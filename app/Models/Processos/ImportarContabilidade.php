<?php

namespace App\Models\Processos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\Models\GerencialEmpresas;
use App\Models\GerencialPeriodo;
use App\Models\GerencialLancamento;

class ImportarContabilidade extends Model
{
    use HasFactory;

    public  $mesAtivo;
    public  $anoAtivo;
    public  $empresasRegional;
    public  $empresasRegionalERP;
    public  $codigoRegional;
    public  $dataLancamentos;

    public  $errors;

    
    /** 
     *	VALIDAÇÃO DE CONTAS CONTÁBEIS SEM ASSOCIAÇÂO DE CONTA GERENCIAL
     *	Esta validação irá verificar se existem lançamentos contábeis registrados em contas que
     *	não possuem associação com as contas gerenciais
     *
     *	Na validação para importação dos lançamentos contábeis, esta consulta NÃO deve retornar resultados,
     *	caso contrário deverão ser listadas as contas contábeis e notificados os usuários que mantêm o gerencial
     *	para qie providenciem o devido cadastro associativo (CONTA GERENCIAL x CONTA CONTÁBIL)
     *
     *  @param  string  Mês para o período do gerencial
     *  @param  string  Ano para o período do gerencial
     * 
     *  @return mixed   array   : Lista de contas contábeis sem associação
     *                  boolean : FALSE [não existem contas contábeis sem associação]
     *                  int     : Total de registros encontrados (0: nenuma conta contábil sem associação de conta gerencial)
     */
    public function checkContaContabil(string $parMes = NULL, string $parAno = NULL) {
        $parMes         = $parMes ?? $this->mesAtivo;
        $parAno         = $parAno ?? $this->anoAtivo;

        $listaEmpresas  = implode(', ', $this->empresasRegionalERP);

        if (empty($parMes) || empty($parAno)) {
            $this->errors[] = ['errorTitle' => '<small>[log]</small> VALIDAÇÃO DE CONTA CONTÁBIL PERÍODO', 'error'   => 'Período Mês/Ano não informado'];
            return FALSE;
        }
        if (empty($listaEmpresas)) {
            $this->errors[] = ['errorTitle' => '<small>[log]</small> VALIDAÇÃO DE CONTA CONTÁBIL EMPRESAS', 'error'   => 'Não foi informada a relação de empresas'];
            return FALSE;
        }

        $dbData = DB::select("SELECT contaContabil	= PlanoConta.PlanoConta_ID COLLATE SQL_Latin1_General_CP1_CI_AS,
                                     descricaoConta	= PlanoConta.PlanoConta_Descricao
                              FROM GrupoRoma_DealernetWF..Lancamento			(nolock)
                              JOIN GrupoRoma_DealernetWF..PlanoConta			(nolock) ON PlanoConta.PlanoConta_Codigo			= Lancamento.Lancamento_PlanoContaCod
                              JOIN GrupoRoma_DealernetWF..CentroResultado		(nolock) ON CentroResultado.CentroResultado_Codigo	= Lancamento.Lancamento_CentroResultadoCod
                              /* LIBERADOS */
                              WHERE Lancamento.Lancamento_Status			= 'LIB'
                              /* PERÍODO A SER IMPORTADO / VERIFICADO */
                              AND   YEAR(Lancamento.Lancamento_Data)		= :ano
                              AND   MONTH(Lancamento.Lancamento_Data)		= :mes
                              /* EMPRESAS DA REGIONAL */
                              AND   Lancamento.Lancamento_EmpresaCod        IN ($listaEmpresas)
                              /* SEGMENTO AUTOMOTIVO  */
                              AND   CentroResultado.Estrutura_Codigo		= '5'
                              /* Lançamentos de RES: Resultado, DSP: Despesa, REC: Receita, ATV: Ativos) */
                              AND   PlanoConta.PlanoConta_TipoContabil	IN ('RES', 'DSP', 'REC', 'ATV')
                              /* Verifica se existe associação da conta contábil com a conta gerencial */
                              AND   PlanoConta.PlanoConta_ID	COLLATE SQL_Latin1_General_CP1_CI_AI			NOT IN (SELECT contaContabil
                                                                                                                        FROM GAMA..G3_gerencialContaContabil		(nolock)
                                                                                                                        WHERE G3_gerencialContaContabil.contaContabilAtiva = 'S')
                              GROUP BY PlanoConta.PlanoConta_ID, PlanoConta.PlanoConta_Descricao
                              ORDER BY PlanoConta.PlanoConta_ID", ['ano' => $parAno, 'mes' => $parMes]);

        if (count($dbData) == 0)  return TRUE;

        $error = NULL;
        foreach ($dbData as $row => $data) {
            $error   .= $data->contaContabil.': '.$data->descricaoConta.'<br>';
        }

        $this->errors[] = ['errorTitle' => 'CONTAS CONTÁBEIS SEM ASSSOCIAÇÃO DE CONTA GERENCIAL', 'error' => $error];

        return FALSE;
    }

    /**
     *	VALIDAÇÃO DE INTEGRAÇÃO CONTÁBIL
     *	Verifica se foram realizadas as integrações contábeis no Workflow para os tipo de integração abaixo,
     *	para todas as empresas que possuem o parâmetro VALIDA INTEGRAÇÂO CONTÁBIL = [S] Sim
     *
     *	Tipos de integrações:
     *	ME	- Movimentação de Estoques
     *	NE	- Notas Fiscais de Entreda
     *	EV	- Notas Fiscais de Entrada de Veículos
     *	NS	- Notas Fiscais de Saída
     *	SV	- Notas Fiscais de Saída de Veículos
     *	SCB	- Controle Bancário
     *	CP	- Contas a Pagar
     *	CR	- Contas a Receber
     *
     *  @param  array   Lista de tipos de integrações contabéis
     *  @param  string  Mês para o período do gerencial
     *  @param  string  Ano para o período do gerencial
     * 
     *  @return mixed   array   : Lista de integrações realizadas |
     *                  boolean : FALSE se não foram realizadas qualquer uma dos tipos de integração
     *                  int     : Total de registros encontrados (0: nenhuma validação pendente)
     */
    public function checkIntegracaoContabil(string $parMes = NULL, string $parAno = NULL, array $tipos = NULL) {
        $parMes         = $parMes ?? $this->mesAtivo;
        $parAno         = $parAno ?? $this->anoAtivo;

//        $empresasIntegracao = GerencialEmpresas::whereIn('codigoEmpresaERP', $this->empresasRegional)
        $empresasIntegracao = GerencialEmpresas::whereIn('codigoEmpresaERP', $this->empresasRegionalERP)
                                                ->where('validaIntegracaoContabil', 'S')
                                                ->where('empresaAtiva', 'S')
                                                ->get();
        $listaEmpresas = '';
        foreach($empresasIntegracao as $data) {
            $listaEmpresas  .= (empty($listaEmpresas) ? '' : ',').$data->codigoEmpresaERP;
        }

        if (empty($parMes) || empty($parAno)) {
            $this->errors[] = ['errorTitle' => '<small>[log]</small> VALIDAÇÃO DE INTEGRAÇÃO CONTÁBIL PERÍODO', 'error'   => 'Período Mês/Ano não informado'];
            return FALSE;
        }
        if (empty($listaEmpresas)) {
            $this->errors[] = ['errorTitle' => '<small>[log]</small> VALIDAÇÃO DE INTEGRAÇÃO CONTÁBIL EMPRESAS', 'error'   => 'Não foi informada a relação de empresas'];
            return FALSE;
        }

        // Informar o(s) estoque(s) é obrigatório
        if (empty($tipos))   {
          $tipos = "ME', 'NE', 'EV', 'NS', 'SV', 'SCB', 'CP', 'CR";
        }
        else    $tipos = implode("','", $tipos);

        $dbData     = DB::select("SELECT codigoEmpresa		    = Empresa.Empresa_Codigo,
                                         nomeEmpresa		    = G3_gerencialEmpresas.nomeAlternativo,
                                         tipoLote			    = CASE WHEN TipoLote.TipoLote_Sigla = 'ME'  THEN '[ME] - Movimentações de Entrada'
                                                                       WHEN TipoLote.TipoLote_Sigla = 'NE'  THEN '[NE] - Notas Fiscais de Entrada'
                                                                       WHEN TipoLote.TipoLote_Sigla = 'EV'  THEN '[EV] - Notas Fiscais de Entrada de Veículos'
                                                                       WHEN TipoLote.TipoLote_Sigla = 'NS'  THEN '[NS] - Notas Fiscais de Saída'
                                                                       WHEN TipoLote.TipoLote_Sigla = 'SV'  THEN '[SV] - Notas Fiscais de Saída de Veículos'
                                                                       WHEN TipoLote.TipoLote_Sigla = 'SCB' THEN '[SCB] - Controle Bancário'
                                                                       WHEN TipoLote.TipoLote_Sigla = 'CP'  THEN '[CP] - Contas a Pagar'
                                                                       WHEN TipoLote.TipoLote_Sigla = 'CR'  THEN '[CR] - Contas a Receber'
                                                                       ELSE TipoLote.TipoLote_Sigla
                                                                  END,
                                         registrosIntegrados	= COUNT(LoteContabil.LoteContabil_Codigo)
                                  FROM GrupoRoma_DealernetWF..LoteContabil	    (nolock)
                                  JOIN GrupoRoma_DealernetWF..Empresa			(nolock) ON Empresa.Empresa_Codigo	                = LoteContabil.LoteContabil_EmpresaCod
                                  JOIN GrupoRoma_DealernetWF..TipoLote		    (nolock) ON TipoLote.TipoLote_Codigo	            = LoteContabil.TipoLote_Codigo
                                  JOIN GAMA..G3_GerencialEmpresas				(nolock) ON G3_gerencialEmpresas.codigoEmpresaERP   = Empresa.Empresa_Codigo
                                  /* VALIDAÇÂO DE LOTE CONTÁBIL PARA A EMPRESA */
                                  WHERE	G3_gerencialEmpresas.validaLoteContabil	= 'S'

                                  /* EMPRESAS DA REGIONAL SELECIONADA */
                                  AND   Empresa.Empresa_Codigo                  IN ($listaEmpresas)

                                  /* PERÍODO A SER VALIDADO */
                                  AND     YEAR(LoteContabil.LoteContabil_DataInicio)	= '$parAno'
                                  AND     MONTH(LoteContabil.LoteContabil_DataInicio)	= '$parMes'

                                  /* TIPOS DE LOTE PARAMETRIZADOS PARA VALIDAÇÃO INTEGRAÇÃO */
                                  AND     TipoLote.TipoLote_Sigla IN ('$tipos') 
                                  GROUP BY Empresa.Empresa_Codigo, G3_gerencialEmpresas.nomeAlternativo, TipoLote.TipoLote_Sigla
                                  ORDER BY NomeEmpresa, TipoLote_Sigla"); //, ['ano' => $parAno, 'mes' => $parMes, 'listaEmpresa' => $listaEmpresas, 'integracoes' => $tipos]);
        
        if (count($dbData) == 0) {
            $this->errors[] = ['errorTitle' => 'INTEGRAÇÕES CONTÁBEIS', 'error' => 'Não foram encontradas integrações contábeis para este período'];
            return FALSE;
        }

        return TRUE;
    }

    /**
     *	VALIDAÇÃO DE VENDEDORES SEM CADASTRO DE CPF
     *	Verifica se todos os vendedores das equipes [4] NOVOS, [5] SEMI NOVOS e [11] VENDEDOR DE VEÍCULOS
     *	possuem o CPF cadastrado no workflow no campo IDENTIFICADOR ALTERNATIVO
     *
     *  @return mixed   array: Lista de vendedores sem cpf
     *                  boolean: FALSE = nenhum vendedor sem cpf
     */
    public function checkVendedores() {
        $dbData = DB::select("SELECT nomeUsuario 	= Usuario.Usuario_Nome,
                                     tipoUsuario	= Equipe.Equipe_Descricao,
                                     cpfSga 		= SGAo_Usuario.cpfUsuario
                            FROM GrupoRoma_DealernetWF..Usuario		(nolock)
                            JOIN GrupoRoma_DealernetWF..Equipe		(nolock)	ON Equipe.Equipe_Codigo = Usuario.Equipe_Codigo
                            LEFT JOIN GAMA..SGAo_usuario			(nolock)	ON SGAo_Usuario.emailUsuario = Usuario.Usuario_Email COLLATE SQL_Latin1_General_CP1_CI_AS
                            /* USUÁRIOS ATIVOS */
                            WHERE Usuario.Usuario_DataDemissao		IS NULL

                            /* IGNORA OS USUÁRIOS COM O IDENTIFICADOR INICADOS POR ACAO [usuários da dealernet] */
                            AND	  Usuario.Usuario_Identificador		NOT LIKE 'ACAO%'

                            /* USUÁRIOS COM O CAMPO DE IDENTIFICADOR ALTERNATIVO (usado para o CPF) VAZIO */
                            AND  (Usuario.Usuario_IdentificadorAlternativo = ' ' OR Usuario.Usuario_IdentificadorAlternativo IS NULL)

                            /* NAS EQUIPES DE VENDAS [4] EQUIPE DE NOVOS, [5] EQUIPE DE SMINOVOS e [11] VENDEDOR DE VEÌCULOS */
                            AND   Equipe.Equipe_Codigo IN (4,5,11)");
        
        if (count($dbData) == 0 ) return TRUE;
        
        $error = NULL;
        foreach ($dbData as $data) {
            $error .= $data->nomeUsuario.' | '.$data->tipoUsuario.' | CPF: '.$data->cpfSga.'<br>';
        }
        $this->errors[] = ['errorTitle'     => 'VENDEDORES SEM REGISTRO DE CPF (identificador alternativo - workflow)', 'error' => $error];

        return FALSE;
    }

    /**
     *	VALIDA NOTAS FISCAIS DE VENDA E DEVOLUÇÃO DE VEÍCULOS
     *	Verifica se existe alguma nota fiscal de venda e devolução de veículos sem a identificação do vendedor na nota fiscal
     *
     *  @param  string      Mês de referência
     *  @param  string      Ano de referência
     * 
     *  @return mixed   array   : Lista de notas fiscais sem identificação do vendedor
     *                  boolean : FALSE não foram passados os parâmetros necessários para execução da consulta
     *                  int     : Total de registros encontrados (0: nenhuma nota fiscal sem identificação do vendedor)
     *
     */
    public function checkNotaVendedor(string $parMes = NULL, string $parAno = NULL) {
        $parMes    = $parMes ?? $this->mesAtivo;
        $parAno    = $parAno ?? $this->anoAtivo;

        if (empty($parMes) || empty($parAno)) {
            $this->errors[] = ['errorTitle' => '<small>[log]</small> VALIDAÇÃO DE NOTAS SEM VENDEDOR PERÍODO', 
                               'error'   => 'Período Mês/Ano não informado'];
            return FALSE;
        }

        $dbData     = DB::select("SELECT origem		    = 'VENDA',
                                         numeroNota     = NotaFiscal.NotaFiscal_Numero,
                                         empresa		= Empresa.Empresa_NomeFantasia,
                                         dataEmissao	= CONVERT(varchar, NotaFiscal.NotaFiscal_DataEmissao, 103)
                                  FROM GrupoRoma_DealernetWF..NotaFiscal       (nolock)
                                  JOIN GrupoRoma_DealernetWF..NotaFiscalItem   (nolock)	 ON NotaFiscal.NotaFiscal_Codigo				= NotaFiscalItem.NotaFiscal_Codigo 
                                  JOIN GrupoRoma_DealernetWF..Proposta         (nolock)	 ON Proposta.Proposta_NotaFiscalCod				= NotaFiscal.NotaFiscal_Codigo 
                                                                                        AND  NotaFiscalItem.NotaFiscalItem_VeiculoCod	= Proposta.Proposta_VeiculoCod
                                  JOIN GrupoRoma_DealernetWF..NaturezaOperacao (nolock)	 ON NaturezaOperacao.NaturezaOperacao_Codigo	= NotaFiscal.NotaFiscal_NaturezaOperacaoCod
                                  JOIN GrupoRoma_DealernetWF..Veiculo          (nolock)	 ON Veiculo.Veiculo_Codigo						= NotaFiscalItem.NotaFiscalItem_VeiculoCod
                                  JOIN GrupoRoma_DealernetWF..Empresa          (nolock)	 ON Empresa.Empresa_Codigo						= NotaFiscal.NotaFiscal_EmpresaCod
                                  /* NOTAS COM SITUAÇÃO DE [EMI] EMITIDAS */
                                  WHERE  NotaFiscal.NotaFiscal_Status						= 'EMI'
                                  /* DATA / PERÍODO DE EMISSÃO IGUAL AO PERÍODO DO GERENCIAL */
                                  AND    YEAR(NotaFiscal.NotaFiscal_DataEmissao)			= '".$parAno."'
                                  AND    MONTH(NotaFiscal.NotaFiscal_DataEmissao)			= '".$parMes."'
                                  /* NATUREZA DE OPERAÇÃO [VEN] VENDA */
                                  AND    NaturezaOperacao.NaturezaOperacao_GrupoMovimento	= 'VEN'
                                  /* MOVIMENTO DE [S] SAÍDA */
                                  AND    NotaFiscal.NotaFiscal_Movimento					= 'S'
                                  /* COM PROPOSTA DE VENDA NÃO [CAN] CANCELADA */
                                  AND    Proposta.Proposta_Status							<> 'CAN'
                                  /* COM NÚMERO DE PEDIDO GERADO PELA PROPOSTA*/
                                  AND    Proposta.Proposta_Pedido							IS NOT NULL
                                  /* NOTAS FISCAIS COM O CAMPO DO VENDEDOR VAZIO */
                                  AND   ((NotaFiscal.NotaFiscal_UsuCodVendedor			IS NULL)	OR 
                                          (NotaFiscal.NotaFiscal_UsuCodVendedor			= '' )		OR
                                          (NotaFiscal.NotaFiscal_UsuCodVendedor			= ' ' ))
                                  /* COM O CÓDIGO DE EMPRESA PERTENCENTE À REGIONAL INFORMADA PARA PROCESSAMENTO */
                                  AND    NotaFiscal.NotaFiscal_EmpresaCod					IN (SELECT Empresa.Empresa_Codigo
                                                                                            FROM GrupoRoma_DealernetWF..Empresa	(nolock)
                                                                                            JOIN GAMA..G3_gerencialEmpresas		(nolock) ON G3_gerencialEmpresas.codigoEmpresaERP = Empresa.Empresa_Codigo
                                                                                            WHERE G3_gerencialEmpresas.codigoRegional IN ('".$this->codigoRegional."') )
                                  UNION ALL
                                  /* NOTAS FISCAIS DE DEVOLUÇÃO DE VEÍCULOS */
                                  SELECT origem 		= 'DEVOLUCAO',
                                         numeroNota 	= NotaFiscal.NotaFiscal_Numero,
                                         empresa 		= Empresa.Empresa_NomeFantasia,
                                         dataEmissao	= CONVERT(varchar, NotaFiscal.NotaFiscal_DataEmissao, 103)
                                  FROM GrupoRoma_DealernetWF..NotaFiscal       (nolock)
                                  JOIN GrupoRoma_DealernetWF..NotaFiscalItem   (nolock)	ON NotaFiscal.NotaFiscal_Codigo				= NotaFiscalItem.NotaFiscal_Codigo 
                                  JOIN GrupoRoma_DealernetWF..Proposta         (nolock)	ON Proposta.Proposta_NotaFiscalCod			= NotaFiscal.NotaFiscal_Codigo 
                                                                                       AND NotaFiscalItem.NotaFiscalItem_VeiculoCod	= Proposta.Proposta_VeiculoCod
                                  JOIN GrupoRoma_DealernetWF..NaturezaOperacao (nolock)	ON NaturezaOperacao.NaturezaOperacao_Codigo	= NotaFiscal.NotaFiscal_NaturezaOperacaoCod
                                  JOIN GrupoRoma_DealernetWF..Veiculo          (nolock)	ON Veiculo.Veiculo_Codigo					= NotaFiscalItem.NotaFiscalItem_VeiculoCod
                                  JOIN GrupoRoma_DealernetWF..Empresa          (nolock)	ON Empresa.Empresa_Codigo					= NotaFiscal.NotaFiscal_EmpresaCod
                                  /* NOTAS FISCAIS DE DEVOLUÇÃO [EMI] EMITIDAS */
                                  WHERE  NotaFiscal.NotaFiscal_Status						= 'EMI'
                                  /* DATA / PERÍODO DE EMISSÃO IGUAL AO PERÍODO DO GERENCIAL */
                                  AND    YEAR(NotaFiscal.NotaFiscal_DataEmissao)			= '".$parAno."'
                                  AND    MONTH(NotaFiscal.NotaFiscal_DataEmissao)			= '".$parMes."'
                                  /* NATUREZA DE OPERAÇÃO DE [DVE] DEVOLUÇÃO DE VENDA */
                                  AND    NaturezaOperacao.NaturezaOperacao_GrupoMovimento	= 'DVE'
                                  /* MOVIMENTO DE [E] ENTRADA */
                                  AND    NotaFiscal.NotaFiscal_Movimento					= 'E'
                                  /* COM PROPOSTA DE VENDA NÃO [CAN] CANCELADA */
                                  AND    Proposta.Proposta_Status							<> 'CAN'
                                  /* COM NÚMERO DE PEDIDO GERADO PELA PROPOSTA */
                                  AND    Proposta.Proposta_Pedido							IS NOT NULL
                                  /* NOTAS FISCAIS COM O CAMPO DE VENDEDOR VAZIO */
                                  AND   ((NotaFiscal.NotaFiscal_UsuCodVendedor			IS NULL) OR 
                                          (NotaFiscal.NotaFiscal_UsuCodVendedor			= '' )	 OR 
                                          (NotaFiscal.NotaFiscal_UsuCodVendedor = ' ' ))
                                  /* COM O CÓDIGO DE EMPRESA PERTENCENTE À REGIONAL INFORMADA PARA PROCESSAMENTO */
                                  AND    NotaFiscal.NotaFiscal_EmpresaCod					IN (SELECT Empresa.Empresa_Codigo
                                                                                            FROM GrupoRoma_DealernetWF..Empresa	(nolock)
                                                                                            JOIN GAMA..G3_gerencialEmpresas		(nolock) ON G3_gerencialEmpresas.codigoEmpresaERP = Empresa.Empresa_Codigo
                                                                                            WHERE G3_gerencialEmpresas.codigoRegional IN ('".$this->codigoRegional."') )");
        if (count($dbData) == 0) return TRUE;
        
        $error = NULL;
        foreach($dbData as $data) {
            $error .= $data->origem.' | NF: '.$data->numeroNota.' | Empresa: '.$data->empresa.' | Emissao: '.$data->dataEmissao.'<br>';
        }

        $this->errors[] = ['errorTitle'   => 'NOTAS FISCAIS SEM IDENTIFICAÇÃO DO VENDEDOR', 'error' => $error];
        return FALSE;
    }

    /**
     *	VALIDA A EXISTÊNCIA DE LANÇAMENTOS CONTÁBEIS ABERTOS
     *	Verfica se existem lançamentos em aberto (situações [ABE] ou [PEI])
     *
     *	@param  string      Mês de referência
     *  @param  string      Ano de referência
     *  @param  int         Código da regional
     * 
     *  @return mixed   array   : Lista de empresas e lotes com lançamentos abertos
     *                  boolean : FALSE não foram passados os parâmetros necessários para execução da consulta
     *                  int     : Total de registros encontrados (0: nenhuma lançamento aberto)
     */
    public function checkLacamentosAbertos(string $parMes = NULL, string $parAno = NULL) {
        $parMes    = $parMes ?? $this->mesAtivo;
        $parAno    = $parAno ?? $this->anoAtivo;

        $empresasIntegracao = GerencialEmpresas::whereIn('codigoEmpresaERP', $this->empresasRegionalERP)
                                                ->where('empresaAtiva', '=', 'S')
                                                ->get();
        $listaEmpresas = '';
        foreach($empresasIntegracao as $data) {
            $listaEmpresas  .= (empty($listaEmpresas) ? '' : ',').$data->codigoEmpresaERP;
        }
        
        if (empty($parMes) || empty($parAno)) {
            $this->errors[] = ['errorTitle' => '<small>[log]</small> VALIDAÇÃO DE LANÇAMENTOS ABERTOS PERÍODO', 'error'   => 'Período Mês/Ano não informado'];
            return FALSE;
        }
        if (empty($listaEmpresas)) {
            $this->errors[] = ['errorTitle' => '<small>[log]</small> VALIDAÇÃO DE LANÇAMENTOS ABERTOS EMPRESAS', 'error'   => 'Não foi informada a relação de empresas'];
            return FALSE;
        }

        $dbData     = DB::select("SELECT nomeEmpresa 	= Empresa.Empresa_NomeFantasia,
                                         numeroLote 	= LoteContabil.LoteContabil_Numero,
                                         mesAnoLancamento = CONVERT(varchar,MONTH(Lancamento.Lancamento_Data))+'/'+CONVERT(varchar, YEAR(Lancamento.Lancamento_Data))
                                  FROM GrupoRoma_DealernetWF..Lancamento        (nolock)
                                  LEFT JOIN GrupoRoma_DealernetWF..LoteContabil	(nolock) ON LoteContabil.LoteContabil_codigo        = Lancamento.LoteContabil_Codigo
                                  JOIN GrupoRoma_DealernetWF..PlanoConta        (nolock) ON PlanoConta.PlanoConta_Codigo            = Lancamento.Lancamento_PlanoContaCod
                                  JOIN GrupoRoma_DealernetWF..CentroResultado   (nolock) ON CentroResultado.CentroResultado_Codigo  = Lancamento.Lancamento_CentroResultadoCod
                                  JOIN GrupoRoma_DealernetWF..Empresa			(nolock) ON Empresa.Empresa_Codigo                  = Lancamento.Lancamento_EmpresaCod
                                  /*JOIN gama..sga_empresas						(nolock) ON sga_empresas.emp_cd                     = Empresa.Empresa_Codigo*/
                                  /* DATA / PERÍODO DO LANÇAMENTO IGUAL AO PERÍODO DO GERENCIAL */
                                  WHERE YEAR(Lancamento.Lancamento_Data)					= '$parAno'
                                  AND   MONTH(Lancamento.Lancamento_Data)					= '$parMes'
                                  /* COM CÓDIGO DE CENTRO DE CUSTO INFORMADO */
                                  AND   Lancamento.Lancamento_CentroResultadoCod			IS NOT NULL
                                  /* COM SITUAÇÃO [ABE] ABERTO ou [PEI] ?? */
                                  AND	  Lancamento.Lancamento_Status						IN ('ABE','PEI')
                                  /* PARA A ESTRUTURA DE PLANO DE CONTAS [5] AUTOMOTIVO */
                                  AND   CentroResultado.Estrutura_Codigo					= '5'
                                  /* PARA OS TIPOS DE CONTA de [RES] RESULTADO, [DSP] DESPESAS, [REC] RECEITA, [ATV] ATIVO */
                                  AND   PlanoConta.PlanoConta_TipoContabil				IN ('RES','DSP','REC','ATV')
                                  /* PARA AS EMPRESAS QUE FAZEM PARTE DA REGIONAL * /
                                  AND   sga_empresas.cod_reg								= '".$this->codigoRegional."' */
                                  AND   Empresa.Empresa_Codigo                          IN ($listaEmpresas)
                                  GROUP BY Empresa.Empresa_NomeFantasia, LoteContabil.LoteContabil_Numero, CONVERT(varchar,MONTH(Lancamento.Lancamento_Data))+'/'+CONVERT(varchar, YEAR(Lancamento.Lancamento_Data))
                                  ORDER BY NomeEmpresa, NumeroLote"); //, ['ano' => $parAno, 'mes' => $parMes, 'regional' => $this->codigoRegional]);
        
        if (count($dbData) == 0) return TRUE;
        
        $error = NULL;
        foreach($dbData as $data) {
            $error .= $data->nomeEmpresa.' | Lote: '.$data->numeroLote.' | Período: '.$data->mesAnoLancamento.'<br>';
        }
        $this->errors[] = ['errorTitle' => 'LANÇAMENTOS CONTÁBEIS ABERTOS', 'error' => $error];
        return FALSE;
    }

    /**
     *	VALOR TOTAL DE NOTAS DE VEÍCULOS EMITIDAS
     *	retorna o valor total das vendas com base nas notas fiscais de venda emitidas
     *
     *  @param  string      Mês de referência
     *  @param  string      Ano de referência
     * 
     *  @return mixed   array   : Origem e valor total de venda
     *                  boolean : FALSE não foram passados os parâmetros necessários para execução da consulta
     *                  int     : Total de registros encontrados (0: não foram encontradas notas fiscais de venda de veículos)
     */
    public function totalVendasNF(string $parMes = NULL, string $parAno = NULL) {
        $parMes    = $parMes ?? $this->mesAtivo;
        $parAno    = $parAno ?? $this->anoAtivo;

        $empresasIntegracao = GerencialEmpresas::whereIn('codigoEmpresaERP', $this->empresasRegionalERP)
                                                ->where('empresaAtiva', '=', 'S')
                                                ->get();
        $listaEmpresas = '';
        foreach($empresasIntegracao as $data) {
            $listaEmpresas  .= (empty($listaEmpresas) ? '' : ',').$data->codigoEmpresaERP;
        }

        if (empty($parMes) || empty($parAno)) {
            $this->errors[] = ['errorTitle' => '<small>[log]</small> VALIDAÇÃO DE VALOR TOTAL NF PERÍODO', 'error'   => 'Período Mês/Ano não informado'];
            return FALSE;
        }
        if (empty($listaEmpresas)) {
            $this->errors[] = ['errorTitle' => '<small>[log]</small> VALIDAÇÃO DE VALOR TOTAL NF EMPRESAS', 'error'   => 'Não foi informada a relação de empresas'];
            return FALSE;
        }

        $dbData = DB::select("SELECT origem				= 'NFV',
                                     valorTotalVenda	= round(SUM(NotaFiscal.NotaFiscal_ValorTotal),2)
                              FROM GrupoRoma_DealernetWF..NotaFiscal			(nolock)
                              JOIN GrupoRoma_DealernetWF..NotaFiscalItem		(nolock)	ON	NotaFiscal.NotaFiscal_Codigo                = NotaFiscalItem.NotaFiscal_Codigo
                              JOIN GrupoRoma_DealernetWF..Veiculo				(nolock)	ON	NotaFiscalItem.NotaFiscalItem_VeiculoCod    = Veiculo.Veiculo_Codigo
                              JOIN GrupoRoma_DealernetWF..Estoque				(nolock)	ON	Estoque.Estoque_Codigo                      = NotaFiscalItem.NotaFiscalItem_EstoqueCod
                              JOIN GrupoRoma_DealernetWF..NaturezaOperacao	    (nolock)	ON	NaturezaOperacao.NaturezaOperacao_Codigo    = NotaFiscal.NotaFiscal_NaturezaOperacaoCod
                              /* DATA / PERÍODO DE EMISSÃO IGUAL AO PERÍODO DO GERENCIAL*/
                              WHERE YEAR(NotaFiscal.NotaFiscal_DataEmissao)				= '$parAno'
                              AND   MONTH(NotaFiscal.NotaFiscal_DataEmissao)			= '$parMes'
                              /* NOTA FISCAL COM SITUAÇÃO [EMI] EMITIDA */
                              AND   NotaFiscal.NotaFiscal_Status						= 'EMI'
                              /* MOVIMENTO DE [S] SAÍDA */
                              AND   NotaFiscal.NotaFiscal_Movimento						= 'S'
                              /* NATUREZA DE OPERAÇÃO [VEN] VENDA */
                              AND   NaturezaOperacao.NaturezaOperacao_GrupoMovimento	= 'VEN'
                              /* EMPRESAS DA REGIONAL SELECIONADA */
                              AND   NotaFiscal.NotaFiscal_EmpresaCod                    IN ($listaEmpresas)
                              /* ESTOQUE DIFERENTE DE [DI] VENDA DIRETA e [VI] VEÍCULOS IMOBILIZADOS */
                              AND   Estoque.Estoque_Sigla								NOT IN ('DI','VI')");
        if (count($dbData) == 0) {
            $this->errors[] = ['errorTitle' => 'NOTAS FISCAIS X LANÇAMENTOS CONTÁBEIS [VALOR DE VENDA]', 
                               'error'      => 'Não foram encontradas Notas Fiscais de Venda de Veículos <strong>EMITIDAS</strong> para a Regional / período informados'];
            return FALSE;
        }

        $returnData = [];
        foreach($dbData as $data) {
            $returnData[] = ['origem'   => $data->origem, 'valorTotalVenda' => $data->valorTotalVenda];
        }

        return $returnData;
    }

    /**
     *	VALOR TOTAL DE VENDAS CONTABILIZADAS
     *	Verifica se todas as notas fiscais de venda de veículos foram devidamente contabilizadas
     *
     *  @param  string      Mês de referência
     *  @param  string      Ano de referência
     * 
     *  @return mixed   array   : Origem e valor total de venda
     *                  boolean : FALSE não foram passados os parâmetros necessários para execução da consulta
     *                  int     : Total de registros encontrados (0: não foram encontradas notas fiscais de venda de veículos)
    */
    public function totalVendasCTB(string $parMes = NULL, string $parAno = NULL) {
        $parMes    = $parMes ?? $this->mesAtivo;
        $parAno    = $parAno ?? $this->anoAtivo;

        $empresasIntegracao = GerencialEmpresas::whereIn('codigoEmpresaERP', $this->empresasRegionalERP)
                                                ->where('empresaAtiva', '=', 'S')
                                                ->get();
        $listaEmpresas = '';
        foreach($empresasIntegracao as $data) {
            $listaEmpresas  .= (empty($listaEmpresas) ? '' : ',').$data->codigoEmpresaERP;
        }

        if (empty($parMes) || empty($parAno)) {
            $this->errors[] = ['errorTitle' => '<small>[log]</small> VALIDAÇÃO DE VALOR NF CONTABILIZADO PERÍODO', 'error'   => 'Período Mês/Ano não informado'];
            return FALSE;
        }
        if (empty($listaEmpresas)) {
            $this->errors[] = ['errorTitle' => '<small>[log]</small> VALIDAÇÃO DE VALOR NF CONTABILIZADO EMPRESAS', 'error'   => 'Não foi informada a relação de empresas'];
            return FALSE;
        }

        $dbData = DB::select("SELECT origem				= 'CTB',
                                     valorTotalVenda	= ROUND(SUM(Lancamento.Lancamento_Valor * CASE WHEN Lancamento.Lancamento_Natureza = 'D' THEN -1 ELSE 1 END),2)
                              FROM GrupoRoma_DealernetWF..Lancamento      (nolock)
                              JOIN GrupoRoma_DealernetWF..PlanoConta      (nolock) ON PlanoConta.PlanoConta_Codigo           = Lancamento.Lancamento_PlanoContaCod
                              JOIN GrupoRoma_DealernetWF..CentroResultado (nolock) ON CentroResultado.CentroResultado_Codigo = Lancamento.Lancamento_CentroResultadoCod
                              /* DATA / PERÍODO DE LANÇAMENTO IGUAL AO PERÍODO DO GERENCIAL */
                              WHERE YEAR(Lancamento.Lancamento_Data)				= '$parAno'
                              AND   MONTH(Lancamento.Lancamento_Data)				= '$parMes'
                              /* COM CENTRO DE CUSTO PREENCHIDO */
                              AND   Lancamento.Lancamento_CentroResultadoCod		IS NOT NULL
                              /* DA ESTRUTURA DO PLANO DE CONTAS [5] AUTOMOTIVO */
                              AND   CentroResultado.Estrutura_Codigo				= '5'
                              /* LANÇAMENTOS PARA AS CONTAS CONTÁBEIS IDENTIFICADAS NO GERENCIAL COMO CONTA DE RECEITA DE VEÍCULOS */
                              AND   PlanoConta.PlanoConta_ID						IN (SELECT  G3_gerencialContaContabil.contaContabil collate SQL_Latin1_General_CP1_CI_AS
                                                                                        FROM	GAMA..G3_gerencialContaContabil (nolock)
                                                                                        WHERE	G3_gerencialContaContabil.receitaVeiculo = 'S')
                              /* NOS CENTROS DE CUSTO [1] VEÍCULOS NOVOS e [3] VEÍCULOS USADOS */
                              AND   CentroResultado.CentroResultado_Codigo		IN (1,3)
                              /* EMPRESAS DA REGIONL SELECIONADA */
                              AND   Lancamento.Lancamento_EmpresaCod            IN ($listaEmpresas)
                              /* COM LANÇAMENTO CONTÁBIL [LIB] LIBERADO */
                              AND   Lancamento.Lancamento_Status					= 'LIB'"); //, ['ano' => $parAno, 'mes' => $parMes, 'empresasRegional' => $listaEmpresas]);

        if (count($dbData) == 0) {
            $this->errors[] = ['errorTitle' => 'NOTAS FISCAIS X LANÇAMENTOS CONTÁBEIS [VALOR DE VENDA]',
                               'error'      => 'Não foram encontradas Notas Fiscais de Venda de Veículos <strong>CONTABILIZADAS</strong> a Regional / período informados'];
            return FALSE;
        }

        $returnData = [];
        foreach($dbData as $data) {
            $returnData[] = ['origem'   => $data->origem, 'valorTotalVenda' => $data->valorTotalVenda];
        }

        return $returnData;
    }

    /**
     *	RECEITA, CUSTO E ICMS DE VENDA DE VEÍCULOS
     *	Retorna todas as vendas com os dados para registro da receita e custo
     *
     *	@param  string      Mês de referência
     *  @param  string      Ano de referência
     *  @param  int         Código da regional
     *  @param  array       Lista de estoques (VN,VU, VD,DI,VI, ...)
     * 
     *  @return mixed   array   : Valores de Receita, Custo e Impostos dos veiculos vendidos
     *                  boolean : FALSE não foram passados os parâmetros necessários para execução da consulta
     *                  int     : Total de registros encontrados (0: não foram encontradas notas fiscais de venda de veículos)
     */
    public function receitaCustoVeiculos(string $parMes = NULL, string $parAno = NULL) {
        $parMes    = $parMes ?? $this->mesAtivo;
        $parAno    = $parAno ?? $this->anoAtivo;

        if (empty($parMes))         return FALSE;
        if (empty($parAno))         return FALSE;
//        if (empty($parRegional))    return FALSE;
//        if (empty($parEstoque))     return FALSE;

        if (empty($parMes) || empty($parAno)) {
            $this->errors[] = ['errorTitle' => '<small>[log]</small> RECEITA / CUSTO DE VEÍCULOS', 'error'   => 'Período Mês/Ano não informado'];
            return FALSE;
        }
        #if (empty($listaEmpresas)) {
        #    $this->errors[] = ['errorTitle' => '<small>[log]</small> RECEITA / CUSTO DE VEÍCULOS EMPRESAS', 'error'   => 'Não foi informada a relação de empresas'];
        #    return FALSE;
        #}

        $dbData = DB::select("SELECT codigoEmpresaOrigem		= NotaFiscal.NotaFiscal_EmpresaCod,
                                        nomeEmpresaOrigem		= Empresa.Empresa_Nome,
                                        codigoEmpresaVenda		= CASE WHEN CONVERT(varchar, NotaFiscal.NotaFiscal_UsuCodVendedor) IN (G3_gerencialRegional.codigoVendasExternasERP) 
                                                                                THEN G3_gerencialRegional.codigoEmpresaVendaExterna
																	   WHEN EmpresaVendaRegional.Empresa_Codigo IS NULL THEN NotaFiscal.NotaFiscal_EmpresaCod
                                                                       ELSE EmpresaVenda.Empresa_Codigo
                                                                    END,
                                        codigoVeiculo			= Veiculo.Veiculo_Codigo,
                                        numeroDocumento			= NotaFiscal.NotaFiscal_Codigo,
                                        valorReceita			= (NotaFiscal.NotaFiscal_ValorTotal - NotaFiscal.NotaFiscal_ValorDesconto),
                                        valorCusto				= (SELECT TOP 1 nfCompra.NotaFiscal_ValorTotal
                                                                        FROM  GrupoRoma_DealernetWF..NotaFiscal     nfCompra        (nolock)
                                                                        JOIN  GrupoRoma_DealernetWF..NotaFiscalItem nfCompraVeiculo (nolock) ON nfCompraVeiculo.NotaFiscal_Codigo = nfCompra.NotaFiscal_Codigo
                                                                        JOIN  GrupoRoma_DealernetWF..NaturezaOperacao               (nolock) ON NaturezaOperacao.NaturezaOperacao_Codigo = nfCompra.NotaFiscal_NaturezaOperacaoCod
                                                                        WHERE nfCompraVeiculo.NotaFiscalItem_VeiculoCod  = NotaFiscalItem.NotaFiscalItem_VeiculoCod
                                                                        AND   nfCompra.NotaFiscal_Status <> 'CAN'
                                                                        AND   nfCompra.NotaFiscal_Movimento = 'E'
                                                                        AND   nfCompra.NotaFiscal_DataEmissao <= NotaFiscal.NotaFiscal_DataEmissao
                                                                        AND   NaturezaOperacao.NaturezaOperacao_GrupoMovimento = 'COM'
                                                                        ORDER BY nfCompra.NotaFiscal_DataEmissao DESC) * -1,
                                        tipoOperacao			= NaturezaOperacao.NaturezaOperacao_GrupoMovimento,
                                        codigoVendedorNF		= Vendedor.Usuario_Codigo,
                                        valorICMS				= (SELECT SUM(NotaFiscalitemTributo_Valor) 
                                                                    FROM  GrupoRoma_DealernetWF..NotaFiscalItemTributo 
                                                                    WHERE NotaFiscalItemTributo.NotaFiscal_Codigo = NotaFiscal.NotaFiscal_Codigo
                                                                    AND   NotaFiscalItemTributo.NotaFiscalItemTributo_TributoCod = 107),
                                        estoque					= Departamento.Departamento_Sigla,
                                        codigoCentroCusto		= G3_gerencialCentroCusto.id,
                                        centroCusto				= G3_gerencialCentroCusto.descricaoCentroCusto
                              FROM GrupoRoma_DealernetWF..NotaFiscal			(nolock)
                              JOIN GrupoRoma_DealernetWF..NotaFiscalItem		(nolock) ON NotaFiscalItem.NotaFiscal_Codigo			= NotaFiscal.NotaFiscal_Codigo
                              JOIN GrupoRoma_DealernetWF..NaturezaOperacao	    (nolock) ON NaturezaOperacao.NaturezaOperacao_Codigo	= NotaFiscal.NotaFiscal_NaturezaOperacaoCod
                              JOIN GrupoRoma_DealernetWF..Veiculo				(nolock) ON Veiculo.Veiculo_Codigo						= NotaFiscalItem.NotaFiscalItem_VeiculoCod
                              JOIN GrupoRoma_DealernetWF..Usuario	AS Vendedor	(nolock) ON Vendedor.Usuario_Codigo						= NotaFiscal.NotaFiscal_UsuCodVendedor
                              JOIN GrupoRoma_DealernetWF..Empresa				(nolock) ON Empresa.Empresa_Codigo						= NotaFiscal.NotaFiscal_EmpresaCod
                              JOIN GrupoRoma_DealernetWF..Pessoa				(nolock) ON Pessoa.Pessoa_Codigo						= NotaFiscal.NotaFiscal_PessoaCod
                              /* IDENTIFICAÇÃO DO CENTRO DE CUSTO CONTÁBIL */
                              JOIN GrupoRoma_DealernetWF..Departamento		    (nolock) ON Departamento.Departamento_Codigo			= NotaFiscal.NotaFiscal_DepartamentoCod
                              JOIN GrupoRoma_DealernetWF..CentroResultado		(nolock) ON CentroResultado.CentroResultado_Sigla		= Departamento.Departamento_Contabil
                              JOIN GAMA..G3_gerencialCentroCusto				(nolock) ON G3_gerencialCentroCusto.codigoCentroCustoERP= CentroResultado.CentroResultado_Codigo
                              /* Identificação da empresa e Regional do Gerencial */
                              JOIN GAMA..G3_gerencialEmpresas					(nolock) ON G3_gerencialEmpresas.codigoEmpresaERP		= NotaFiscal.NotaFiscal_EmpresaCod
                              JOIN GAMA..G3_gerencialRegional					(nolock) ON G3_gerencialRegional.id						= G3_gerencialEmpresas.codigoRegional
                              /******************************************************************************************************************************
                                Identificação da empresa de venda, de acordo com a alocação do vendedor:
                                1: Se Veículo Usados ou Imobilizado = Código da Empresa parametrizada no cadastro da Regional
                                2: Se houver registro, no SGA, da empresa na qual o vendedor estva alocado na data da venda
                                3: Em caso de transferências entre empresas no DP, verifica em qual empresa o vendedor estava alocado na data da Venda
                                4: Em qual empresa o vendedor estava alocado no DP, caso tenha sido desligado da empresa
                                5: Em qual empresa o vendedor está ativo no sistema do DP
                                6: A empresa de venda será a mesma da emissão da Nota Fiscal, se nenhuma das alternativas acima forem satisfeitas
                              ******************************************************************************************************************************/
                              JOIN GrupoRoma_DealernetWF..Empresa AS EmpresaVenda			(nolock) on EmpresaVenda.Empresa_Codigo  
                                    = COALESCE( /* 1. AS VENDAS DE Vu e VI DEVERÃO SER ALOCADAS NA UNIDADE DEFINIDA NO CADASTRO DA REGIONAL */
                                            (CASE WHEN Departamento.Departamento_Sigla IN ('VU','VI') THEN G3_gerencialRegional.codigoEmpresaVeiculosUsados ELSE NULL END),
                                            /* 2. EMPRESA DE VENDA DEFINIDA NO SGA PARA O VENDEDOR */
                                            (SELECT TOP 1 SGA_comercialVendedorEmpresa.emp_cd
                                            FROM GAMA..SGA_comercialVendedorEmpresa			(nolock)
                                            WHERE SGA_comercialVendedorEmpresa.fun_cd		 = NotaFiscal.NotaFiscal_UsuCodVendedor
                                            AND   SGA_comercialVendedorEmpresa.dataInicio	<= NotaFiscal.NotaFiscal_DataEmissao
                                            ORDER BY SGA_comercialVendedorEmpresa.dataInicio DESC),
                                            /* 3. EMPRESA EM QUE O VENDEDOR ESTAVA ALOCADO NA DATA DA VENDA NO SISTEMA DO DP
                                            PARA OS CASOS DE TRANSFERÊNCIA ENTRE EMPRESAS */
                                            (SELECT TOP 1 SGA_empresas.emp_cd
                                            FROM GAMA..r034fun					(nolock)
                                            JOIN GAMA..r038hfi					(nolock) ON r038hfi.numcad = r034fun.numcad AND r038hfi.numemp = r034fun.numemp
                                            JOIN GAMA..SGA_empresas			    (nolock) ON SGA_empresas.col_rm = CASE WHEN r038hfi.numemp <> r038hfi.empatu THEN r038hfi.empatu 
                                                                                                                       ELSE r038hfi.numemp
                                                                                                                  END  
                                                                                        AND SGA_empresas.fil_rm = r038hfi.codfil
                                            WHERE REPLICATE('0', (11-LEN(CONVERT(varchar, r034fun.numcpf))))+CONVERT(varchar, r034fun.numcpf) = Vendedor.Usuario_IdentificadorAlternativo
                                            AND  r038hfi.cadatu != r038hfi.numcad
                                            AND  r038hfi.datalt <= NotaFiscal.NotaFiscal_DataEmissao
                                            AND  (r038hfi.tipadm IN ('3','4')) 
                                            GROUP BY SGA_empresas.emp_cd, r034fun.datafa
                                            ORDER BY r034fun.datafa DESC),
                                            /* 4. EMPRESA EM QUE O COLABORADOR ESTAVA ALOCADO NA DATA DA VENDA NO SISTEMA DO DP
                                            PARA O CASO DO VENDEDOR TER SIDO DESLIGADO DA EMPRESA */
                                            (SELECT TOP 1 sga_empresas.emp_cd
                                            FROM GAMA..r034fun						(nolock)
                                            JOIN GAMA..r038hfi						(nolock) ON r038hfi.numcad				= r034fun.numcad 
                                                                                            AND r038hfi.numemp				= r034fun.numemp 
                                                                                            AND r038hfi.codfil				= r034fun.codfil
                                            JOIN GAMA..sga_empresas				    (nolock) ON SGA_empresas.col_rm			= r038hfi.numemp
                                                                                            AND sga_empresas.fil_rm			= r038hfi.codfil
                                            JOIN GrupoRoma_DealernetWF..Empresa	    (nolock) ON Empresa.Empresa_Codigo		= sga_empresas.emp_cd
                                            WHERE REPLICATE('0', (11-LEN(CONVERT(varchar, r034fun.numcpf))))+CONVERT(varchar, r034fun.numcpf) = Vendedor.Usuario_IdentificadorAlternativo
                                            AND   r034fun.sitafa			         = 7
                                            AND   r034fun.datafa					>= NotaFiscal.NotaFiscal_DataEmissao
                                            GROUP BY SGA_EMPRESAS.EMP_CD,r034fun.datafa
                                            HAVING (MIN(r034fun.datafa))			>= NotaFiscal.NotaFiscal_DataEmissao),
                                            /* 5. EMPRESA EM QUE O VENDEDOR ESTÁ ATIVO E ALOCADO NO SISTEMA DO DP
                                                PARA O CASO DO VENDEDOR ESTAR ATIVO */
                                            (SELECT TOP 1 SGA_empresas.emp_cd
                                            FROM GAMA..r034fun						(nolock)
                                            JOIN GAMA..r038hfi						(nolock) ON r038hfi.numcad				= r034fun.numcad 
                                                                                            AND r038hfi.numemp				= r034fun.numemp
                                            JOIN GAMA..sga_empresas				    (nolock) ON SGA_empresas.col_rm			= r038hfi.numemp
                                                                                            AND sga_empresas.fil_rm			= r038hfi.codfil
                                            JOIN GrupoRoma_DealernetWF..Empresa	    (nolock) ON Empresa.Empresa_Codigo		= sga_empresas.emp_cd
                                            WHERE REPLICATE('0', (11-LEN(CONVERT(varchar, r034fun.numcpf))))+CONVERT(varchar, r034fun.numcpf) = Vendedor.Usuario_IdentificadorAlternativo
                                            AND   r034fun.sitafa != '7' 
                                            ORDER BY r038hfi.datalt DESC),
                                            /* 6. CASO NENHUMA DAS ALTERNATIVAS ACIMA SEJA SATISFEITA IRÁ RETORNAR O CÓDIGO
                                                DA EMPRESA QUE EMITIU A NOTA FISCAL DE VENDA */
                                            NotaFiscal.NotaFiscal_EmpresaCod)
                              /* VERIFICA SE A EMPRESA DE VENDA (do vendedor) É DA MESMA REGIONAL SELECIONADA
                                 SENÃO, MANTÉM A EMPRESA DE EMISSÃO DA NOA FISCAL
                              */
                              OUTER APPLY (	SELECT Empresa.*
                                            FROM GrupoRoma_DealernetWF..Empresa				(nolock)
                                            JOIN GAMA..G3_gerencialEmpresas GEmpresaVenda	(nolock) ON GEmpresaVenda.codigoEmpresaERP	= Empresa.Empresa_Codigo
                                            JOIN GAMA..G3_gerencialRegional GRegionalVenda	(nolock) ON GRegionalVenda.id				= GEmpresaVenda.codigoRegional
                                            WHERE	Empresa.Empresa_Codigo = EmpresaVenda.Empresa_Codigo
                                            AND		GRegionalVenda.id	= G3_gerencialRegional.id
                                        ) AS EmpresaVendaRegional

                              /* NOTAS FISCAIS COM SITUAÇÃO [EMI] EMITIDAS */
                              WHERE  NotaFiscal.NotaFiscal_Status = 'EMI'
                              /* RESTRINGE ÀS VENDAS DA REGIONAL INFORMADA PARA IMPORTAÇÃO DOS LANÇAMENTOS CONTÁBEIS */
                              AND    G3_gerencialRegional.id		= $this->codigoRegional
                              /* DATA / PERÍODO IGUAL AO PERÍODO DO GERENCIAL */
                              AND    YEAR(NotaFiscal.NotaFiscal_DataEmissao)			= '$parAno'
                              AND    MONTH(NotaFiscal.NotaFiscal_DataEmissao)			= '$parMes'
                              /* RESTRINGE À LISTA DE ESTOQUES INFORMADOS * /
                              AND    Departamento.Departamento_Sigla    in (:estoque) */
                              /* SOMENTE AS NOTAS FISCAIS DE [VEN] VENDA */
                              AND    NaturezaOperacao.NaturezaOperacao_GrupoMovimento	= 'VEN'
                              /* SOMENTE AS NOTAS FISCAIS COM MOVIMENTO DE [S] SAÍDA */
                              AND    NotaFiscal.NotaFiscal_Movimento = 'S'
                              /* DESCONSIDERA AS VENDAS REALIZADAS ENTRE AS EMPRESAS DO GRUPO (INTRAGRUPO) */
                              AND    Pessoa.Pessoa_DocIdentificador NOT IN (SELECT ColigadaDados.Pessoa_DocIdentificador
                                                                            FROM	GrupoRoma_DealernetWF..Empresa	AS Coligada			(nolock)
                                                                            JOIN	GrupoRoma_DealernetWF..Pessoa	AS ColigadaDados	(nolock) ON ColigadaDados.Pessoa_Codigo = Coligada.Empresa_PessoaCod
                                                                            WHERE Coligada.Empresa_Ativo = 1)
                              /* UNION ALL - DEVOLUÇÔES */
                              UNION ALL

                              SELECT codigoEmpresaOrigem		= NotaFiscal.NotaFiscal_EmpresaCod,
                                     nomeEmpresaOrigem		    = Empresa.Empresa_Nome,
                                     codigoEmpresaVenda		    = CASE WHEN CONVERT(varchar, NotaFiscal.NotaFiscal_UsuCodVendedor) IN (G3_gerencialRegional.codigoVendasExternasERP) 
                                                                                THEN G3_gerencialRegional.codigoEmpresaVendaExterna
																	   WHEN EmpresaDevolucaoRegional.Empresa_Codigo IS NULL THEN NotaFiscal.NotaFiscal_EmpresaCod
                                                                       ELSE EmpresaDevolucao.Empresa_Codigo
                                                                    END,
                                     codigoVeiculo			    = Veiculo.Veiculo_Codigo,
                                     numeroDocumento			= NotaFiscal.NotaFiscal_Codigo,
                                     valorReceita				= (NotaFiscal.NotaFiscal_ValorTotal - NotaFiscal.NotaFiscal_ValorDesconto) * -1,
                                     valorCusto				    = (SELECT TOP 1 nfCompra.NotaFiscal_ValorTotal
                                                                    FROM  GrupoRoma_DealernetWF..NotaFiscal     nfCompra        (nolock)
                                                                    JOIN  GrupoRoma_DealernetWF..NotaFiscalItem nfCompraVeiculo (nolock) ON nfCompraVeiculo.NotaFiscal_Codigo = nfCompra.NotaFiscal_Codigo
                                                                    JOIN  GrupoRoma_DealernetWF..NaturezaOperacao               (nolock) ON NaturezaOperacao.NaturezaOperacao_Codigo = nfCompra.NotaFiscal_NaturezaOperacaoCod
                                                                    WHERE nfCompraVeiculo.NotaFiscalItem_VeiculoCod  = NotaFiscalItem.NotaFiscalItem_VeiculoCod
                                                                    AND   nfCompra.NotaFiscal_Status <> 'CAN'
                                                                    AND   nfCompra.NotaFiscal_Movimento = 'E'
                                                                    AND   nfCompra.NotaFiscal_DataEmissao <= NotaFiscal.NotaFiscal_DataEmissao
                                                                    AND   NaturezaOperacao.NaturezaOperacao_GrupoMovimento = 'COM'
                                                                    ORDER BY nfCompra.NotaFiscal_DataEmissao DESC) * -1,
                                     tipoOperacao				= NaturezaOperacao.NaturezaOperacao_GrupoMovimento,
                                     codigoVendedorNF			= Vendedor.Usuario_Codigo,
                                     valorICMS				    = (SELECT SUM(NotaFiscalitemTributo_Valor) 
                                                                    FROM  GrupoRoma_DealernetWF..NotaFiscalItemTributo 
                                                                    WHERE NotaFiscalItemTributo.NotaFiscal_Codigo = NotaFiscal.NotaFiscal_Codigo
                                                                    AND   NotaFiscalItemTributo.NotaFiscalItemTributo_TributoCod = 107) * -1,
                                     estoque					= Departamento.Departamento_Sigla,
                                     codigoCentroCusto		    = G3_gerencialCentroCusto.id,
                                     centroCusto				= G3_gerencialCentroCusto.descricaoCentroCusto
                              FROM GrupoRoma_DealernetWF..NotaFiscal				(nolock)
                              JOIN GrupoRoma_DealernetWF..NotaFiscalItem			(nolock) ON NotaFiscalItem.NotaFiscal_Codigo			= NotaFiscal.NotaFiscal_Codigo
                              JOIN GrupoRoma_DealernetWF..NaturezaOperacao		    (nolock) ON NaturezaOperacao.NaturezaOperacao_Codigo	= NotaFiscal.NotaFiscal_NaturezaOperacaoCod
                              JOIN GrupoRoma_DealernetWF..NotaFiscalNFReferencia	(nolock) ON NotaFiscalNFReferencia.NotaFiscal_Codigo	= NotaFiscal.NotaFiscal_Codigo
                              JOIN GrupoRoma_DealernetWF..NotaFiscal AS NFVenda	    (nolock) ON NFVenda.NotaFiscal_Codigo					= NotaFiscalNFReferencia.NotaFiscalNFReferencia_NFCod
                              JOIN GrupoRoma_DealernetWF..Veiculo					(nolock) ON Veiculo.Veiculo_Codigo						= NotaFiscalItem.NotaFiscalItem_VeiculoCod
                              JOIN GrupoRoma_DealernetWF..Usuario	AS Vendedor		(nolock) ON Vendedor.Usuario_Codigo						= NotaFiscal.NotaFiscal_UsuCodVendedor
                              JOIN GrupoRoma_DealernetWF..Empresa					(nolock) ON Empresa.Empresa_Codigo						= NotaFiscal.NotaFiscal_EmpresaCod
                              JOIN GrupoRoma_DealernetWF..Pessoa					(nolock) ON Pessoa.Pessoa_Codigo						= NotaFiscal.NotaFiscal_PessoaCod
                              /* IDENTIFICAÇÃO DO CENTRO DE CUSTO CONTÁBIL */
                              JOIN GrupoRoma_DealernetWF..Departamento			    (nolock) ON Departamento.Departamento_Codigo				= NotaFiscal.NotaFiscal_DepartamentoCod
                              JOIN GrupoRoma_DealernetWF..CentroResultado			(nolock) ON CentroResultado.CentroResultado_Sigla			= Departamento.Departamento_Contabil
                              JOIN GAMA..G3_gerencialCentroCusto					(nolock) ON G3_gerencialCentroCusto.codigoCentroCustoERP	= CentroResultado.CentroResultado_Codigo
                              /* Identificação da empresa e Regional do Gerencial */
                              JOIN GAMA..G3_gerencialEmpresas						(nolock) ON G3_gerencialEmpresas.codigoEmpresaERP		= NotaFiscal.NotaFiscal_EmpresaCod
                              JOIN GAMA..G3_gerencialRegional						(nolock) ON G3_gerencialRegional.id						= G3_gerencialEmpresas.codigoRegional
                              /******************************************************************************************************************************
                                Identificação da empresa de venda, de acordo com a alocação do vendedor:
                                1: Se Veículo Usados ou Imobilizado = Código da Empresa parametrizada no cadastro da Regional
                                2: Se houver registro, no SGA, da empresa na qual o vendedor estva alocado na data da venda
                                3: Em caso de transferências entre empresas no DP, verifica em qual empresa o vendedor estava alocado na data da Venda
                                4: Em qual empresa o vendedor estava alocado no DP, caso tenha sido desligado da empresa
                                5: Em qual empresa o vendedor está ativo no sistema do DP
                                6: A empresa de venda será a mesma da emissão da Nota Fiscal, se nenhuma das alternativas acima forem satisfeitas
                              ******************************************************************************************************************************/
                              JOIN GrupoRoma_DealernetWF..Empresa AS EmpresaDevolucao	(nolock) on EmpresaDevolucao.Empresa_Codigo  
                                    = COALESCE( /* 1. AS VENDAS DE Vu e VI DEVERÃO SER ALOCADAS NA UNIDADE DEFINIDA NO CADASTRO DA REGIONAL */
                                                (CASE WHEN Departamento.Departamento_Sigla IN ('VU','VI') THEN G3_gerencialRegional.codigoEmpresaVeiculosUsados ELSE NULL END),
                                                /* 2. EMPRESA DE VENDA DEFINIDA NO SGA PARA O VENDEDOR */
                                                (SELECT TOP 1 SGA_comercialVendedorEmpresa.emp_cd
                                                FROM GAMA..SGA_comercialVendedorEmpresa			(nolock)
                                                WHERE SGA_comercialVendedorEmpresa.fun_cd			 = NFVenda.NotaFiscal_UsuCodVendedor
                                                AND   SGA_comercialVendedorEmpresa.dataInicio		<= NFVenda.NotaFiscal_DataEmissao
                                                ORDER BY SGA_comercialVendedorEmpresa.dataInicio DESC),
                                                /* 3. EMPRESA EM QUE O VENDEDOR ESTAVA ALOCADO NA DATA DA VENDA NO SISTEMA DO DP
                                                PARA OS CASOS DE TRANSFERÊNCIA ENTRE EMPRESAS */
                                                (SELECT TOP 1 SGA_empresas.emp_cd
                                                FROM GAMA..r034fun					(nolock)
                                                JOIN GAMA..r038hfi					(nolock) ON r038hfi.numcad = r034fun.numcad AND r038hfi.numemp = r034fun.numemp
                                                JOIN GAMA..SGA_empresas			    (nolock) ON SGA_empresas.col_rm = CASE WHEN r038hfi.numemp <> r038hfi.empatu THEN r038hfi.empatu 
                                                                                                                           ELSE r038hfi.numemp
                                                                                                                      END  
                                                                                            AND SGA_empresas.fil_rm = r038hfi.codfil
                                                WHERE REPLICATE('0', (11-LEN(CONVERT(varchar, r034fun.numcpf))))+CONVERT(varchar, r034fun.numcpf) = Vendedor.Usuario_IdentificadorAlternativo
                                                AND  r038hfi.cadatu != r038hfi.numcad
                                                AND  r038hfi.datalt <= NFVenda.NotaFiscal_DataEmissao
                                                AND  (r038hfi.tipadm IN ('3','4')) 
                                                GROUP BY SGA_empresas.emp_cd, r034fun.datafa
                                                ORDER BY r034fun.datafa DESC),
                                                /* 4. EMPRESA EM QUE O COLABORADOR ESTAVA ALOCADO NA DATA DA VENDA NO SISTEMA DO DP
                                                PARA O CASO DO VENDEDOR TER SIDO DESLIGADO DA EMPRESA */
                                                (SELECT TOP 1 sga_empresas.emp_cd
                                                FROM GAMA..r034fun						(nolock)
                                                JOIN GAMA..r038hfi						(nolock) ON r038hfi.numcad				= r034fun.numcad 
                                                                                                AND r038hfi.numemp				= r034fun.numemp 
                                                                                                AND r038hfi.codfil				= r034fun.codfil
                                                JOIN GAMA..sga_empresas				    (nolock) ON SGA_empresas.col_rm			= r038hfi.numemp
                                                                                                AND sga_empresas.fil_rm			= r038hfi.codfil
                                                JOIN GrupoRoma_DealernetWF..Empresa	    (nolock) ON Empresa.Empresa_Codigo		= sga_empresas.emp_cd
                                                WHERE REPLICATE('0', (11-LEN(CONVERT(varchar, r034fun.numcpf))))+CONVERT(varchar, r034fun.numcpf) = Vendedor.Usuario_IdentificadorAlternativo
                                                AND   r034fun.sitafa			         = 7
                                                AND   r034fun.datafa					>= NFVenda.NotaFiscal_DataEmissao
                                                GROUP BY SGA_EMPRESAS.EMP_CD,r034fun.datafa
                                                HAVING (MIN(r034fun.datafa))			>= NFVenda.NotaFiscal_DataEmissao),
                                                /* 5. EMPRESA EM QUE O VENDEDOR ESTÁ ATIVO E ALOCADO NO SISTEMA DO DP
                                                    PARA O CASO DO VENDEDOR ESTAR ATIVO */
                                                (SELECT TOP 1 SGA_empresas.emp_cd
                                                FROM GAMA..r034fun						(nolock)
                                                JOIN GAMA..r038hfi						(nolock) ON r038hfi.numcad				= r034fun.numcad 
                                                                                                AND r038hfi.numemp				= r034fun.numemp
                                                JOIN GAMA..sga_empresas				    (nolock) ON SGA_empresas.col_rm			= r038hfi.numemp
                                                                                                AND sga_empresas.fil_rm			= r038hfi.codfil
                                                JOIN GrupoRoma_DealernetWF..Empresa	    (nolock) ON Empresa.Empresa_Codigo		= sga_empresas.emp_cd
                                                WHERE REPLICATE('0', (11-LEN(CONVERT(varchar, r034fun.numcpf))))+CONVERT(varchar, r034fun.numcpf) = Vendedor.Usuario_IdentificadorAlternativo
                                                AND   r034fun.sitafa != '7' 
                                                ORDER BY r038hfi.datalt DESC),
                                                /* 6. CASO NENHUMA DAS ALTERNATIVAS ACIMA SEJA SATISFEITA IRÁ RETORNAR O CÓDIGO
                                                    DA EMPRESA QUE EMITIU A NOTA FISCAL DE VENDA */
                                                NFVenda.NotaFiscal_EmpresaCod)

                              /* VERIFICA SE A EMPRESA DE VENDA (do vendedor) É DA MESMA REGIONAL SELECIONADA
                                 SENÃO, MANTÉM A EMPRESA DE EMISSÃO DA NOA FISCAL
                              */
                              OUTER APPLY (	SELECT Empresa.*
                                            FROM GrupoRoma_DealernetWF..Empresa EmpresaDevolucao    (nolock)
                                            JOIN GAMA..G3_gerencialEmpresas GEmpresaDevolucao	    (nolock) ON GEmpresaDevolucao.codigoEmpresaERP	= Empresa.Empresa_Codigo
                                            JOIN GAMA..G3_gerencialRegional GRegionalDevolucao	    (nolock) ON GRegionalDevolucao.id				= GEmpresaDevolucao.codigoRegional
                                            WHERE	EmpresaDevolucao.Empresa_Codigo = Empresa.Empresa_Codigo
                                            AND		GRegionalDevolucao.id	        = G3_gerencialRegional.id
                                        ) AS EmpresaDevolucaoRegional

                              /* NOTAS FISCAIS COM SITUAÇÃO [EMI] EMITIDAS */
                              WHERE  NotaFiscal.NotaFiscal_Status = 'EMI'
                              /* RESTRINGE ÀS VENDAS DA REGIONAL INFORMADA PARA IMPORTAÇÃO DOS LANÇAMENTOS CONTÁBEIS */
                              AND    G3_gerencialRegional.id		= $this->codigoRegional
                              /* DATA / PERÍODO IGUAL AO PERÍODO DO GERENCIAL */
                              AND    YEAR(NotaFiscal.NotaFiscal_DataEmissao)			= '$parAno'
                              AND    MONTH(NotaFiscal.NotaFiscal_DataEmissao)			= '$parMes'
                              /* RESTRINGE À LISTA DE ESTOQUES INFORMADOS * /
                              AND    Departamento.Departamento_Sigla    in (:estoque) */
                              /* SOMENTE AS NOTAS FISCAIS DE [VEN] VENDA */
                              AND    NaturezaOperacao.NaturezaOperacao_GrupoMovimento	= 'DVE'
                              /* SOMENTE AS NOTAS FISCAIS COM MOVIMENTO DE [S] SAÍDA */
                              AND    NotaFiscal.NotaFiscal_Movimento = 'E'
                              /* DESCONSIDERA AS VENDAS REALIZADAS ENTRE AS EMPRESAS DO GRUPO (INTRAGRUPO) */
                              AND    Pessoa.Pessoa_DocIdentificador NOT IN (SELECT ColigadaDados.Pessoa_DocIdentificador
                                                                            FROM	GrupoRoma_DealernetWF..Empresa	AS Coligada			(nolock)
                                                                            JOIN	GrupoRoma_DealernetWF..Pessoa	AS ColigadaDados	(nolock) ON ColigadaDados.Pessoa_Codigo = Coligada.Empresa_PessoaCod
                                                                            WHERE Coligada.Empresa_Ativo = 1)"); 
                            //, ['ano' => $parAno, 'mes' => $parMes, 'regional' => $this->codigoRegional]);

        if (count($dbData) == 0) {
            $this->errors[] = ['errorTitle'     => 'RECEITA / CUSTO DE VEÍCULO',
                                'error'         => 'Não foram encontrados valores de receitas / custos de veículos'];
            return FALSE;
        }

        $returnData = [];
        foreach($dbData as $data) {
            $returnData[] = ['codigoEmpresaOrigem'  => $data->codigoEmpresaOrigem,
                            'nomeEmpresaOrigem'     => $data->nomeEmpresaOrigem,
                            'codigoEmpresaVenda'    => $data->codigoEmpresaVenda,
                            'codigoVeiculo'         => $data->codigoVeiculo,
                            'numeroDocumento'       => $data->numeroDocumento,
                            'valorReceita'          => $data->valorReceita,
                            'valorCusto'            => $data->valorCusto,
                            'tipoOperacao'          => $data->tipoOperacao,
                            'codigoVendedor'        => $data->codigoVendedorNF,
                            'valorICMS'             => $data->valorICMS,
                            'estoque'               => trim($data->estoque),
                            'codigoCentroCusto'     => $data->codigoCentroCusto,
                            'centroCusto'           => $data->centroCusto];
        }

        return $returnData;
    }

    /**
     *	BÔNUS EMPRESA
     *	Retorna os valores referentes à bônus empresa para registro no Gerencial
     *  
     *  @param  string      Mês de referência
     *  @param  string      Ano de referência
     *  @param  int         Código da regional
     * 
     *  @return mixed   array   : Dados e valor do Bônus Empresa
     *                  boolean : FALSE não foram passados os parâmetros necessários para execução da consulta
     *                  int     : Total de registros encontrados (0: não foram encontrados valores de Bônus Empresa)
    */
    public function getBonusEmpresa(string $parMes = NULL, string $parAno = NULL) {
        $parMes    = $parMes ?? $this->mesAtivo;
        $parAno    = $parAno ?? $this->anoAtivo;
        
        $empresasIntegracao = GerencialEmpresas::whereIn('codigoEmpresaERP', $this->empresasRegionalERP)
                                                ->where('empresaAtiva', '=', 'S')
                                                ->get();
        $listaEmpresas = '';
        foreach($empresasIntegracao as $data) {
            $listaEmpresas  .= (empty($listaEmpresas) ? '' : ',').$data->codigoEmpresaERP;
        }

        if (empty($parMes) || empty($parAno)) {
            $this->errors[] = ['errorTitle' => '<small>[log]</small> BÔNUS EMPRESA PERÍODO', 'error'   => 'Período Mês/Ano não informado'];
            return FALSE;
        }
        if (empty($listaEmpresas)) {
            $this->errors[] = ['errorTitle' => '<small>[log]</small> BÔNUS EMPRESA EMPRESAS', 'error'   => 'Não foi informada a relação de empresas'];
            return FALSE;
        }

        $dbData     = DB::select("SELECT numeroDocumento        = Titulo.Titulo_Codigo,
                                         codigoVeiculo		    = Titulo.Titulo_VeiculoCod,
                                         codigoVendedor         = Titulo.Titulo_UsuarioCodVendedor,
                                         codigoEmpresaOrigem	= EmpresaOrigem.Empresa_Codigo,
                                         nomeEmpresaOrigem	    = EmpresaOrigem.Empresa_NomeFantasia,
                                         codigoEmpresaVenda     = ISNULL(CASE WHEN G3_gerencialRegional.codigoVendasExternasERP LIKE '%'+CONVERT(VARCHAR, Titulo.Titulo_UsuarioCodVendedor)+'%'
																				   THEN G3_gerencialRegional.codigoEmpresaVendaExterna
																			   ELSE EmpresaVenda.Empresa_Codigo
																		  END, EmpresaOrigem.Empresa_Codigo),
                                         codigoRegional         = G3_gerencialRegional.id,
                                         valorBonus			    = SUM(Titulo.Titulo_Valor),
                                         estoque                = CASE WHEN G3_gerencialRegional.codigoVendasExternasERP LIKE '%'+CONVERT(VARCHAR, Titulo.Titulo_UsuarioCodVendedor)+'%'
                                                                            THEN 'VE'
                                                                       ELSE Departamento.Departamento_Sigla
                                                                  END,
                                         codigoCentroCusto		= G3_gerencialCentroCusto.id,
                                         centroCusto			= G3_gerencialCentroCusto.descricaoCentroCusto,
                                         codigoContaGerencial	= (SELECT G3_gerencialContaGerencial.id
                                                                    FROM GAMA..G3_gerencialContaGerencial		(nolock)
                                                                    WHERE G3_gerencialContaGerencial.valoresVeiculo LIKE '%BEP%'),
                                         contaContabil			= (SELECT G3_gerencialContaContabil.codigoContaContabilERP
                                                                    FROM GAMA..G3_gerencialContaGerencial		(nolock)
                                                                    JOIN GAMA..G3_gerencialContaContabil		(nolock) ON G3_gerencialContaContabil.idContaGerencial = G3_gerencialContaGerencial.id
                                                                    WHERE G3_gerencialContaGerencial.valoresVeiculo LIKE '%BEP%'
                                                                    AND   G3_gerencialContaContabil.idCentroCusto	= G3_gerencialCentroCusto.id)
                                  /* TÍTULOS */
                                  FROM GrupoRoma_DealernetWF..Titulo					(nolock)
                                  /* IDENTIFICAÇÃO DO VENDEDOR */
                                  JOIN GrupoRoma_DealernetWF..Usuario					(nolock) ON Usuario.Usuario_Codigo						= Titulo.Titulo_UsuarioCodVendedor                    
                                  /* IDENTIFICAÇÃO DO DEPARTAMENTO (Estoque) */
                                  JOIN GrupoRoma_DealernetWF..Departamento			    (nolock) ON Departamento.Departamento_Codigo			= Titulo.Titulo_DepartamentoCod
                                  /* EMPRESA DE ORIGEM */
                                  JOIN GrupoRoma_DealernetWF..Empresa EmpresaOrigem	    (nolock) ON EmpresaOrigem.Empresa_Codigo				= Titulo.Titulo_EmpresaCod
                                  /* EMPRESA E REGIONAL DO GERENCIAL */
                                  JOIN GAMA..G3_gerencialEmpresas						(nolock) ON G3_gerencialEmpresas.codigoEmpresaERP		= Titulo.Titulo_EmpresaCod
                                  JOIN GAMA..G3_gerencialRegional						(nolock) ON G3_gerencialRegional.id						= G3_gerencialEmpresas.codigoRegional
                                  /* Identificação do centro de custo contabil*/
                                  JOIN GrupoRoma_DealernetWF..CentroResultado		    (nolock) ON CentroResultado.CentroResultado_Sigla			= Departamento.Departamento_Contabil
                                  JOIN GAMA..G3_gerencialCentroCusto				    (nolock) ON G3_gerencialCentroCusto.codigoCentroCustoERP	= CentroResultado.CentroResultado_Codigo
                                  /******************************************************************************************************************************
                                    IDENTIFICAÇÃO DA EMPRESA DE VENDA

                                    De acordo com a alocação do vendedor:
                                    1: Se Veículo Usados ou Imobilizado = Código da Empresa parametrizada no cadastro da Regional
                                    2: Se houver registro, no SGA, da empresa na qual o vendedor estva alocado na data da venda
                                    3: Em caso de transferências entre empresas no DP, verifica em qual empresa o vendedor estava alocado na data da Venda
                                    4: Em qual empresa o vendedor estava alocado no DP, caso tenha sido desligado da empresa
                                    5: Em qual empresa o vendedor está ativo no sistema do DP
                                    6: A empresa de venda será a mesma da emissão da Nota Fiscal, se nenhuma das alternativas acima forem satisfeitas
                                  ******************************************************************************************************************************/
                                  JOIN GrupoRoma_DealernetWF..Empresa AS EmpresaVenda			(nolock) on EmpresaVenda.Empresa_Codigo  
                                        = COALESCE( /* 1. AS VENDAS DE Vu e VI DEVERÃO SER ALOCADAS NA UNIDADE DEFINIDA NO CADASTRO DA REGIONAL */
                                                    (CASE WHEN Departamento.Departamento_Sigla IN ('VU','VI') THEN G3_gerencialRegional.codigoEmpresaVeiculosUsados ELSE NULL END),
                                                    
                                                    /* 2. EMPRESA DE VENDA DEFINIDA NO SGA PARA O VENDEDOR */
                                                    (SELECT TOP 1 SGA_comercialVendedorEmpresa.emp_cd
                                                    FROM GAMA..SGA_comercialVendedorEmpresa			(nolock)
                                                    WHERE SGA_comercialVendedorEmpresa.fun_cd			 = Titulo.Titulo_UsuarioCodVendedor
                                                    AND   SGA_comercialVendedorEmpresa.dataInicio		<= Titulo.Titulo_DataEmissao
                                                    ORDER BY SGA_comercialVendedorEmpresa.dataInicio DESC),

                                                    /* 3. EMPRESA EM QUE O VENDEDOR ESTAVA ALOCADO NA DATA DE REGISTRO DO TÍTULO NO SISTEMA DO DP
                                                    PARA OS CASOS DE TRANSFERÊNCIA ENTRE EMPRESAS */
                                                    (SELECT TOP 1 SGA_empresas.emp_cd
                                                    FROM GAMA..r034fun					(nolock)
                                                    JOIN GAMA..r038hfi					(nolock) ON r038hfi.numcad = r034fun.numcad AND r038hfi.numemp = r034fun.numemp
                                                    JOIN GAMA..SGA_empresas			(nolock) ON SGA_empresas.col_rm = CASE WHEN r038hfi.numemp <> r038hfi.empatu THEN r038hfi.empatu 
                                                                                                                            ELSE r038hfi.numemp
                                                                                                                        END  
                                                                                                    AND SGA_empresas.fil_rm = r038hfi.codfil
                                                    WHERE REPLICATE('0', (11-LEN(CONVERT(varchar, r034fun.numcpf))))+CONVERT(varchar, r034fun.numcpf) = Usuario.Usuario_IdentificadorAlternativo
                                                    AND  r038hfi.cadatu != r038hfi.numcad
                                                    AND  r038hfi.datalt <= Titulo.Titulo_DataEmissao
                                                    AND  (r038hfi.tipadm IN ('3','4')) 
                                                    GROUP BY SGA_empresas.emp_cd, r034fun.datafa
                                                    ORDER BY r034fun.datafa DESC),

                                                    /* 4. EMPRESA EM QUE O COLABORADOR ESTAVA ALOCADO NA DATA DE REGISTRO DO TÍTULO NO SISTEMA DO DP
                                                    PARA O CASO DO VENDEDOR TER SIDO DESLIGADO DA EMPRESA */
                                                    (SELECT TOP 1 sga_empresas.emp_cd
                                                    FROM GAMA..r034fun						(nolock)
                                                    JOIN GAMA..r038hfi						(nolock) ON r038hfi.numcad				= r034fun.numcad 
                                                                                                    AND r038hfi.numemp				= r034fun.numemp 
                                                                                                    AND r038hfi.codfil				= r034fun.codfil
                                                    JOIN GAMA..sga_empresas				(nolock) ON SGA_empresas.col_rm			= r038hfi.numemp
                                                                                                    AND sga_empresas.fil_rm			= r038hfi.codfil
                                                    JOIN GrupoRoma_DealernetWF..Empresa	(nolock) ON Empresa.Empresa_Codigo		= sga_empresas.emp_cd
                                                    WHERE REPLICATE('0', (11-LEN(CONVERT(varchar, r034fun.numcpf))))+CONVERT(varchar, r034fun.numcpf) = Usuario.Usuario_IdentificadorAlternativo
                                                    AND   r034fun.sitafa			= 7
                                                    AND   r034fun.datafa					>= Titulo.Titulo_DataEmissao
                                                    GROUP BY SGA_EMPRESAS.EMP_CD,r034fun.datafa
                                                    HAVING (MIN(r034fun.datafa))			>= Titulo.Titulo_DataEmissao),

                                                    /* 5. EMPRESA EM QUE O VENDEDOR ESTÁ ATIVO E ALOCADO NO SISTEMA DO DP
                                                        PARA O CASO DO VENDEDOR ESTAR ATIVO */
                                                    (SELECT TOP 1 SGA_empresas.emp_cd
                                                    FROM GAMA..r034fun						(nolock)
                                                    JOIN GAMA..r038hfi						(nolock) ON r038hfi.numcad				= r034fun.numcad 
                                                                                                    AND r038hfi.numemp				= r034fun.numemp
                                                    JOIN GAMA..sga_empresas				(nolock) ON SGA_empresas.col_rm			= r038hfi.numemp
                                                                                                    AND sga_empresas.fil_rm			= r038hfi.codfil
                                                    JOIN GrupoRoma_DealernetWF..Empresa	(nolock) ON Empresa.Empresa_Codigo		= sga_empresas.emp_cd
                                                    WHERE REPLICATE('0', (11-LEN(CONVERT(varchar, r034fun.numcpf))))+CONVERT(varchar, r034fun.numcpf) = Usuario.Usuario_IdentificadorAlternativo
                                                    AND   r034fun.sitafa != '7' 
                                                    ORDER BY r038hfi.datalt DESC),

                                                    /* 6. CASO NENHUMA DAS ALTERNATIVAS ACIMA SEJA SATISFEITA IRÁ RETORNAR O CÓDIGO
                                                        DA EMPRESA PARA QUAL ESTÁ REGISTRADO O TÍTULO */
                                                    Titulo.Titulo_EmpresaCod)
                                  /* TIPO DE TÍTULO BÔNUS EMPRESA [258] V-BONUS ROMA | [344] V-BONUS CAPTACAO) */
                                  WHERE Titulo.Titulo_TipoTituloCod IN (258,344)
                                  /* TÍTULOS NÃO CANCELADOS */
                                  AND   Titulo.Titulo_Status <> 'CAN'
                                  /* DATA / PERÍODO IGUAL AO PERÍODO DO GERENCIAL */
                                  AND   YEAR(Titulo.Titulo_DataPagamento)				= '$parAno'
                                  AND   MONTH(Titulo.Titulo_DataPagamento)			    = '$parMes'
                                  /* AGENTE COBRADOR [35] VEÍCULOS */
                                  AND   Titulo.Titulo_AgenteCobradorCod = 35
                                  /* DEPARTAMENTO / ESTOQUE DIFERENTE DE [VI] VEÍCULOS IMOBILIZADOS */
                                  AND   Departamento.Departamento_Sigla				<> 'VI'
                                  /* O TÍTULO NÃO É DE UMA DEVOLUÇÃO DE VENDA */							 
                                  AND  Titulo_NotaFiscalCod NOT IN (SELECT NotaFiscalNFReferencia_NFCod
                                                                    FROM  GrupoRoma_DealernetWF..NotaFiscal				(nolock)
                                                                    JOIN  GrupoRoma_DealernetWF..NotaFiscalItem			(nolock)  ON NotaFiscalItem.NotaFiscal_Codigo			= NotaFiscal.NotaFiscal_Codigo
                                                                    JOIN  GrupoRoma_DealernetWF..NaturezaOperacao		(nolock)  ON NaturezaOperacao.NaturezaOperacao_Codigo	= NotaFiscal.NotaFiscal_NaturezaOperacaoCod
                                                                    JOIN  GrupoRoma_DealernetWF..NotaFiscalNFReferencia	(nolock)  ON NotaFiscalNFReferencia.NotaFiscal_Codigo	= NotaFiscal.NotaFiscal_Codigo 
                                                                    WHERE  NaturezaOperacao.NaturezaOperacao_GrupoMovimento = 'DVE'
                                                                    AND    NotaFiscal.NotaFiscal_Movimento                  = 'E'
                                                                    AND    NotaFiscalItem.NotaFiscalItem_VeiculoCod         IS NOT NULL
                                                                    AND    YEAR(NotaFiscal.NotaFiscal_DataEmissao)		    = '$parAno'
                                                                    AND	   MONTH(NotaFiscal.NotaFiscal_DataEmissao)		    = '$parMes'
                                                                    AND    NotaFiscal.NotaFiscal_Status <> 'CAN') 
                                  /* EMPRESAS CADASTRAS DA REGIONAL INFORMADA */
                                  /*AND  G3_gerencialRegional.id			= $this->codigoRegional */
                                  AND   G3_gerencialEmpresas.codigoEmpresaERP   IN ($listaEmpresas)
                                  GROUP BY Titulo.Titulo_Codigo, Titulo.Titulo_VeiculoCod, Titulo.Titulo_UsuarioCodVendedor, EmpresaOrigem.Empresa_Codigo, EmpresaOrigem.Empresa_NomeFantasia,
                                        CASE WHEN G3_gerencialRegional.codigoVendasExternasERP LIKE '%'+CONVERT(VARCHAR, Titulo.Titulo_UsuarioCodVendedor)+'%'
                                                THEN G3_gerencialRegional.codigoEmpresaVendaExterna
                                            ELSE EmpresaVenda.Empresa_Codigo
                                        END,
                                        EmpresaVenda.Empresa_Nome, G3_gerencialRegional.id,
                                        CASE WHEN G3_gerencialRegional.codigoVendasExternasERP LIKE '%'+CONVERT(VARCHAR, Titulo.Titulo_UsuarioCodVendedor)+'%'
                                                THEN 'VE'
                                            ELSE Departamento.Departamento_Sigla
                                        END,
                                        G3_gerencialCentroCusto.id, G3_gerencialCentroCusto.descricaoCentroCusto");
                            //,['ano' => $parAno, 'mes' => $parMes, 'regional' => $this->codigoRegional]);

        if (count($dbData) == 0) {
            $this->errors[]     = ['errorTitle' => 'BÔNUS EMPRESA',
                                    'error'     => 'Não foram encontrados valores de Bônus Empresa'];
            return FALSE;
        }

        $returnData = [];
        foreach($dbData as $data) {
            $returnData[] = ['numeroDocumento'      => $data->numeroDocumento,
                            'codigoVeiculo'         => $data->codigoVeiculo,
                            'codigoVendedor'        => $data->codigoVendedor,
                            'codigoEmpresaOrigem'   => $data->codigoEmpresaOrigem,
                            'nomeEmpresaOrigem'     => $data->nomeEmpresaOrigem,
                            'codigoEmpresaVenda'    => $data->codigoEmpresaVenda,
                            'codigoRegional'        => $data->codigoRegional,
                            'valorBonus'            => $data->valorBonus,
                            'estoque'               => trim($data->estoque),
                            'codigoCentroCusto'     => $data->codigoCentroCusto,
                            'centroCusto'           => $data->centroCusto,
                            'codigoContaGerencial'  => $data->codigoContaGerencial,
                            'contaContabil'         => $data->contaContabil];
        }
        return $returnData;
    }

    /**
     *	HOLD BACK
     *	Retorna os valores de HoldBack para Registro no gerencial
     *
     *  @param  string      Mês de referência
     *  @param  string      Ano de referência
     *  @param  int         Código da regional
     * 
     *  @return mixed   array   : Dados e valor de HoldBack
     *                  boolean : FALSE não foram passados os parâmetros necessários para execução da consulta
     *                  int     : Total de registros encontrados (0: não foram encontrados valores de Hold Back)
    */
    public function getHoldBack(string $parMes = NULL, string $parAno = NULL) {
        $parMes    = $parMes ?? $this->mesAtivo;
        $parAno    = $parAno ?? $this->anoAtivo;
        
        $empresasIntegracao = GerencialEmpresas::whereIn('codigoEmpresaERP', $this->empresasRegionalERP)
                                                ->where('empresaAtiva', '=', 'S')
                                                ->get();
        $listaEmpresas = '';
        foreach($empresasIntegracao as $data) {
            $listaEmpresas  .= (empty($listaEmpresas) ? '' : ',').$data->codigoEmpresaERP;
        }

        if (empty($parMes) || empty($parAno)) {
            $this->errors[] = ['errorTitle' => '<small>[log]</small> VALORES DE HOLD BACK PERÍODO', 'error'   => 'Período Mês/Ano não informado'];
            return FALSE;
        }
        if (empty($listaEmpresas)) {
            $this->errors[] = ['errorTitle' => '<small>[log]</small> VALORES DE HOLD BACK EMPRESAS', 'error'   => 'Não foi informada a relação de empresas'];
            return FALSE;
        }

        $dbData     = DB::select("SELECT numeroDocumento        = Titulo.Titulo_Codigo,
                                         codigoVeiculo		    = Titulo.Titulo_VeiculoCod,
                                         /*codigoVendedor         = Usuario.Usuario_Codigo,*/
                                         codigoEmpresaOrigem	= EmpresaOrigem.Empresa_Codigo,
                                         nomeEmpresaOrigem	    = EmpresaOrigem.Empresa_NomeFantasia,
                                         codigoEmpresaVenda		= ISNULL((SELECT Proposta.Proposta_EmpresaCod
                                                                            FROM GrupoRoma_DealernetWF..Proposta	(nolock)
                                                                            WHERE Proposta.Proposta_EmpresaCod		= Titulo.Titulo_EmpresaCod
                                                                            AND	Proposta.Proposta_VeiculoCod		= Titulo.Titulo_VeiculoCod
                                                                            AND Proposta.Proposta_Status				<> 'CAN'
                                                                            AND Proposta.Proposta_Pedido				IS NOT NULL
                                                                            AND Proposta.Proposta_NotaFiscalCod		    NOT IN 
                                                                                            (SELECT NotaFiscalNFReferencia_NFCod
                                                                                                FROM  GrupoRoma_DealernetWF..NotaFiscal				(nolock)
                                                                                                JOIN  GrupoRoma_DealernetWF..NotaFiscalItem			(nolock) ON NotaFiscalItem.NotaFiscal_Codigo		 = NotaFiscal.NotaFiscal_Codigo
                                                                                                JOIN  GrupoRoma_DealernetWF..NaturezaOperacao		(nolock) ON NaturezaOperacao.NaturezaOperacao_Codigo = NotaFiscal.NotaFiscal_NaturezaOperacaoCod
                                                                                                JOIN  GrupoRoma_DealernetWF..NotaFiscalNFReferencia	(nolock) ON NotaFiscalNFReferencia.NotaFiscal_Codigo = NotaFiscal.NotaFiscal_Codigo 
                                                                                                WHERE NaturezaOperacao.NaturezaOperacao_GrupoMovimento = 'DVE'
                                                                                                AND    NotaFiscal.NotaFiscal_Movimento				    = 'E'
                                                                                                AND    NotaFiscalItem.NotaFiscalItem_VeiculoCod	    IS NOT NULL
                                                                                                AND    NotaFiscal.NotaFiscal_Status			    	<> 'CAN') ), EmpresaOrigem.Empresa_COdigo),
                                         codigoRegional         = G3_gerencialRegional.id,
                                         valorBonus			    = SUM(Titulo.Titulo_Valor),
                                         estoque                = CASE WHEN G3_gerencialRegional.codigoVendasExternasERP LIKE '%'+CONVERT(VARCHAR, Titulo.Titulo_UsuarioCodVendedor)+'%'
                                                                        THEN 'VE'
                                                                    ELSE Departamento.Departamento_Sigla
                                                                  END,
                                         codigoCentroCusto		= G3_gerencialCentroCusto.id,
                                         centroCusto			= G3_gerencialCentroCusto.descricaoCentroCusto,
                                        
                                         codigoContaGerencial	= (SELECT G3_gerencialContaGerencial.id
                                                                    FROM GAMA..G3_gerencialContaGerencial		(nolock)
                                                                    WHERE G3_gerencialContaGerencial.valoresVeiculo LIKE '%HBK%')

                                  /* TÍTULOS */
                                  FROM GrupoRoma_DealernetWF..Titulo					(nolock)
                                  /* IDENTIFICAÇÃO DO DEPARTAMENTO (Estoque) */
                                  JOIN GrupoRoma_DealernetWF..Departamento			    (nolock) ON Departamento.Departamento_Codigo				= Titulo.Titulo_DepartamentoCod
                                  /* EMPRESA DE ORIGEM */
                                  JOIN GrupoRoma_DealernetWF..Empresa EmpresaOrigem	    (nolock) ON EmpresaOrigem.Empresa_Codigo					= Titulo.Titulo_EmpresaCod
                                  /* EMPRESA E REGIONAL DO GERENCIAL */
                                  JOIN GAMA..G3_gerencialEmpresas						(nolock) ON G3_gerencialEmpresas.codigoEmpresaERP			= Titulo.Titulo_EmpresaCod
                                  JOIN GAMA..G3_gerencialRegional						(nolock) ON G3_gerencialRegional.id							= G3_gerencialEmpresas.codigoRegional
                                  /* Identificação do centro de custo contabil*/
                                  JOIN GrupoRoma_DealernetWF..CentroResultado			(nolock) ON CentroResultado.CentroResultado_Sigla			= Departamento.Departamento_Contabil
                                  JOIN GAMA..G3_gerencialCentroCusto					(nolock) ON G3_gerencialCentroCusto.codigoCentroCustoERP	= CentroResultado.CentroResultado_Codigo
                                  /* TIPO DE TÍTULO BÔNUS EMPRESA [258] V-BONUS ROMA | [344] V-BONUS CAPTACAO) */
                                  WHERE Titulo.Titulo_TipoTituloCod IN (217)
                                  /* TÍTULOS NÃO CANCELADOS */
                                  AND   Titulo.Titulo_Status IN ('AUT', 'PAG')
                                  /* DATA / PERÍODO IGUAL AO PERÍODO DO GERENCIAL */
                                  AND   YEAR(Titulo.Titulo_DataEmissao)			    = '$parAno'
                                  AND   MONTH(Titulo.Titulo_DataEmissao)			= '$parMes'
                                  /* DEPARTAMENTO / ESTOQUE DIFERENTE DE [VI] VEÍCULOS IMOBILIZADOS */
                                  AND   Departamento.Departamento_Sigla				<> 'VI'
                                  /* EMPRESAS CADASTRADAS DA REGIONAL INFORMADA */
                                  /*AND  G3_gerencialRegional.id			            = $this->codigoRegional*/
                                  AND   G3_gerencialEmpresas.codigoEmpresaERP           IN ($listaEmpresas)
                                  GROUP BY Titulo.Titulo_Codigo, 
                                           Titulo.Titulo_VeiculoCod,
                                           Titulo.Titulo_EmpresaCod,
                                           /*Usuario.Usuario_Codigo, */
                                           EmpresaOrigem.Empresa_Codigo, 
                                           EmpresaOrigem.Empresa_NomeFantasia,
                                           G3_gerencialRegional.id,
                                           CASE WHEN G3_gerencialRegional.codigoVendasExternasERP LIKE '%'+CONVERT(VARCHAR, Titulo.Titulo_UsuarioCodVendedor)+'%'
                                                THEN 'VE'
                                                ELSE Departamento.Departamento_Sigla
                                           END,
                                           G3_gerencialCentroCusto.id, 
                                           G3_gerencialCentroCusto.descricaoCentroCusto");
                        //,['ano' => $parAno, 'mes' => $parMes, 'regional' => $parRegional]);

        if (count($dbData) == 0) {
            $this->errors[] = ['errorTitle' => 'HOLD BACK',
                                'error'     => 'Não foram encontrados valores de Hold Back'];
            return FALSE;
        }

        $returnData = [];
        foreach($dbData as $data) {
            $returnData[] = ['numeroDocumento'      => $data->numeroDocumento,
                            'codigoVeiculo'         => $data->codigoVeiculo,
                            //'codigoVendedor'        => $data->codigoVendedor,
                            'codigoEmpresaOrigem'   => $data->codigoEmpresaOrigem,
                            'nomeEmpresaOrigem'     => $data->nomeEmpresaOrigem,
                            'codigoEmpresaVenda'    => $data->codigoEmpresaVenda,
                            'codigoRegional'        => $data->codigoRegional,
                            'valorHoldBack'         => $data->valorBonus,
                            'estoque'               => trim($data->estoque),
                            'codigoCentroCusto'     => $data->codigoCentroCusto,
                            'centroCusto'           => $data->centroCusto,
                            'codigoContaGerencial'  => $data->codigoContaGerencial];
        }
        return $returnData;
    }

    /**
     *	BÔNUS FÁBRICA
     *	Retorna o valor de Bônus Fábrica apra os veículos vendidos no período
     *
     *  @param  string      Mês de referência
     *  @param  string      Ano de referência
     *  @param  int         Código da regional
     * 
     *  @return mixed   array   : Dados e valor de Bônus Fábrica
     *                  boolean : FALSE não foram passados os parâmetros necessários para execução da consulta
     *                  int     : Total de registros encontrados (0: não foram encontrados valores de Bônus Fábrica)
    */
    public function getBonusFabrica(string $parMes = NULL, string $parAno = NULL) {
        $parMes    = $parMes ?? $this->mesAtivo;
        $parAno    = $parAno ?? $this->anoAtivo;

        $empresasIntegracao = GerencialEmpresas::whereIn('codigoEmpresaERP', $this->empresasRegionalERP)
                                                ->where('empresaAtiva', '=', 'S')
                                                ->get();
        $listaEmpresas = '';
        foreach($empresasIntegracao as $data) {
            $listaEmpresas  .= (empty($listaEmpresas) ? '' : ',').$data->codigoEmpresaERP;
        }

        if (empty($parMes) || empty($parAno)) {
            $this->errors[] = ['errorTitle' => '<small>[log]</small> BÔNUS FÁBRICA PERÍODO', 'error'   => 'Período Mês/Ano não informado'];
            return FALSE;
        }
        if (empty($listaEmpresas)) {
            $this->errors[] = ['errorTitle' => '<small>[log]</small> BÔNUS FÁBRICA EMPRESAS', 'error'   => 'Não foi informada a relação de empresas'];
            return FALSE;
        }
     
        $dbData     = DB::select("SELECT numeroDocumento        = Titulo.Titulo_Codigo,
                                         codigoVeiculo		    = Titulo.Titulo_VeiculoCod,
                                         codigoVendedor         = Usuario.Usuario_Codigo,
                                         codigoRegional         = G3_gerencialRegional.id,
                                         codigoEmpresaOrigem	= Titulo.Titulo_EmpresaCod,
                                         estoque				= CASE WHEN CONVERT(varchar, NotaFiscal.NotaFiscal_UsuCodVendedor) IN (G3_gerencialRegional.codigoVendasExternasERP) 
                                                                            THEN 'VE'
                                                                       ELSE Departamento.Departamento_Sigla
                                                                  END,
                                         nomeEmpresaOrigem	    = EmpresaOrigem.Empresa_NomeFantasia,
                                         codigoEmpresaVenda     = CASE WHEN CONVERT(varchar, NotaFiscal.NotaFiscal_UsuCodVendedor) IN (G3_gerencialRegional.codigoVendasExternasERP) 
                                                                            THEN G3_gerencialRegional.codigoEmpresaVendaExterna
                                                                       ELSE EmpresaVenda.Empresa_Codigo
                                                                  END,
                                         valorBonus			    = isnull((SELECT TOP 1 (TituloMov.TituloMov_Valor *-1)
                                                                          FROM GrupoRoma_DealernetWF..TituloMov       (nolock)
                                                                          WHERE TituloMov.Titulo_Codigo =  Titulo.Titulo_Codigo
                                                                          AND   TituloMov.TituloMov_TipoCDCod = 95
                                                                          AND   TituloMov.TituloMov_Status <> 'CAN'),Titulo.Titulo_Valor)
                                  FROM GrupoRoma_DealernetWF..Titulo	(nolock)
                                  /* NOTA FISCAL VINCULADA AO TÍTULO */
                                  JOIN GrupoRoma_DealernetWF..NotaFiscalItem				(nolock) ON NotaFiscalItem.NotaFiscalItem_VeiculoCod	= Titulo.Titulo_VeiculoCod
                                  JOIN GrupoRoma_DealernetWF..NotaFiscal					(nolock) ON NotaFiscal.NotaFiscal_Codigo				= NotaFiscalItem.NotaFiscal_Codigo
                                  JOIN GrupoRoma_DealernetWF..NaturezaOperacao			(nolock) ON NaturezaOperacao.NaturezaOperacao_Codigo	= NotaFiscal.NotaFiscal_NaturezaOperacaoCod
                                  /* USUÁRIO / VENDEDOR VINCULADO À NOTA FISCAL DE VENDA */ 
                                  JOIN GrupoRoma_DealernetWF..Usuario						(nolock) ON Usuario.Usuario_Codigo					= NotaFiscal.NotaFiscal_UsuCodVendedor
                                  /* DEPARTAMENTO / ESTOQUE */
                                  JOIN GrupoRoma_DealernetWF..Departamento				(nolock) on Departamento.Departamento_Codigo		= Titulo.Titulo_DepartamentoCod
                                  /* IDENTIFICA A EMPRESA DE ORIGEM DO BÔNUS FÁBRICA */
                                  JOIN GrupoRoma_DealernetWF..Empresa	EmpresaOrigem		(nolock) ON EmpresaOrigem.Empresa_Codigo			= Titulo.Titulo_EmpresaCod
                                  /* EMPRESA E REGIONAL DO GERENCIAL */
                                  JOIN GAMA..G3_gerencialEmpresas								(nolock) ON G3_gerencialEmpresas.codigoEmpresaERP	= EmpresaOrigem.Empresa_Codigo 
                                  JOIN GAMA..G3_gerencialRegional								(nolock) ON G3_gerencialRegional.id					= G3_gerencialEmpresas.codigoRegional
                                  /******************************************************************************************************************************
                                    Identificação da empresa de venda, de acordo com a alocação do vendedor:
                                    1: Se Veículo Usados ou Imobilizado = Código da Empresa parametrizada no cadastro da Regional
                                    2: Se houver registro, no SGA, da empresa na qual o vendedor estva alocado na data da venda
                                    3: Em caso de transferências entre empresas no DP, verifica em qual empresa o vendedor estava alocado na data da Venda
                                    4: Em qual empresa o vendedor estava alocado no DP, caso tenha sido desligado da empresa
                                    5: Em qual empresa o vendedor está ativo no sistema do DP
                                    6: A empresa de venda será a mesma da emissão da Nota Fiscal, se nenhuma das alternativas acima forem satisfeitas
                                  ******************************************************************************************************************************/
                                  JOIN GrupoRoma_DealernetWF..Empresa AS EmpresaVenda			(nolock) on EmpresaVenda.Empresa_Codigo  
                                        = COALESCE( /* 1. AS VENDAS DE Vu e VI DEVERÃO SER ALOCADAS NA UNIDADE DEFINIDA NO CADASTRO DA REGIONAL */
                                                    (CASE WHEN Departamento.Departamento_Sigla IN ('VU','VI') THEN G3_gerencialRegional.codigoEmpresaVeiculosUsados ELSE NULL END),
                                                    
                                                    /* 2. EMPRESA DE VENDA DEFINIDA NO SGA PARA O VENDEDOR */
                                                    (SELECT TOP 1 SGA_comercialVendedorEmpresa.emp_cd
                                                    FROM GAMA..SGA_comercialVendedorEmpresa			(nolock)
                                                    WHERE SGA_comercialVendedorEmpresa.fun_cd			 = Titulo.Titulo_UsuarioCodVendedor
                                                    AND   SGA_comercialVendedorEmpresa.dataInicio		<= Titulo.Titulo_DataEmissao
                                                    ORDER BY SGA_comercialVendedorEmpresa.dataInicio DESC),

                                                    /* 3. EMPRESA EM QUE O VENDEDOR ESTAVA ALOCADO NA DATA DA VENDA NO SISTEMA DO DP
                                                    PARA OS CASOS DE TRANSFERÊNCIA ENTRE EMPRESAS */
                                                    (SELECT TOP 1 SGA_empresas.emp_cd
                                                    FROM GAMA..r034fun					(nolock)
                                                    JOIN GAMA..r038hfi					(nolock) ON r038hfi.numcad = r034fun.numcad AND r038hfi.numemp = r034fun.numemp
                                                    JOIN GAMA..SGA_empresas			(nolock) ON SGA_empresas.col_rm = CASE WHEN r038hfi.numemp <> r038hfi.empatu THEN r038hfi.empatu 
                                                                                                                            ELSE r038hfi.numemp
                                                                                                                        END  
                                                                                                    AND SGA_empresas.fil_rm = r038hfi.codfil
                                                    WHERE REPLICATE('0', (11-LEN(CONVERT(varchar, r034fun.numcpf))))+CONVERT(varchar, r034fun.numcpf) = Usuario.Usuario_IdentificadorAlternativo
                                                    AND  r038hfi.cadatu != r038hfi.numcad
                                                    AND  r038hfi.datalt <= Titulo.Titulo_DataEmissao
                                                    AND  (r038hfi.tipadm IN ('3','4')) 
                                                    GROUP BY SGA_empresas.emp_cd, r034fun.datafa
                                                    ORDER BY r034fun.datafa DESC),

                                                    /* 4. EMPRESA EM QUE O COLABORADOR ESTAVA ALOCADO NA DATA DA VENDA NO SISTEMA DO DP
                                                    PARA O CASO DO VENDEDOR TER SIDO DESLIGADO DA EMPRESA */
                                                    (SELECT TOP 1 sga_empresas.emp_cd
                                                    FROM GAMA..r034fun						(nolock)
                                                    JOIN GAMA..r038hfi						(nolock) ON r038hfi.numcad				= r034fun.numcad 
                                                                                                    AND r038hfi.numemp				= r034fun.numemp 
                                                                                                    AND r038hfi.codfil				= r034fun.codfil
                                                    JOIN GAMA..sga_empresas				(nolock) ON SGA_empresas.col_rm			= r038hfi.numemp
                                                                                                    AND sga_empresas.fil_rm			= r038hfi.codfil
                                                    JOIN GrupoRoma_DealernetWF..Empresa	(nolock) ON Empresa.Empresa_Codigo		= sga_empresas.emp_cd
                                                    WHERE REPLICATE('0', (11-LEN(CONVERT(varchar, r034fun.numcpf))))+CONVERT(varchar, r034fun.numcpf) = Usuario.Usuario_IdentificadorAlternativo
                                                    AND   r034fun.sitafa			= 7
                                                    AND   r034fun.datafa					>= Titulo.Titulo_DataEmissao
                                                    GROUP BY SGA_EMPRESAS.EMP_CD,r034fun.datafa
                                                    HAVING (MIN(r034fun.datafa))			>= Titulo.Titulo_DataEmissao),

                                                    /* 5. EMPRESA EM QUE O VENDEDOR ESTÁ ATIVO E ALOCADO NO SISTEMA DO DP
                                                        PARA O CASO DO VENDEDOR ESTAR ATIVO */
                                                    (SELECT TOP 1 SGA_empresas.emp_cd
                                                    FROM GAMA..r034fun						(nolock)
                                                    JOIN GAMA..r038hfi						(nolock) ON r038hfi.numcad				= r034fun.numcad 
                                                                                                    AND r038hfi.numemp				= r034fun.numemp
                                                    JOIN GAMA..sga_empresas				(nolock) ON SGA_empresas.col_rm			= r038hfi.numemp
                                                                                                    AND sga_empresas.fil_rm			= r038hfi.codfil
                                                    JOIN GrupoRoma_DealernetWF..Empresa	(nolock) ON Empresa.Empresa_Codigo		= sga_empresas.emp_cd
                                                    WHERE REPLICATE('0', (11-LEN(CONVERT(varchar, r034fun.numcpf))))+CONVERT(varchar, r034fun.numcpf) = Usuario.Usuario_IdentificadorAlternativo
                                                    AND   r034fun.sitafa != '7' 
                                                    ORDER BY r038hfi.datalt DESC),

                                                    /* 6. CASO NENHUMA DAS ALTERNATIVAS ACIMA SEJA SATISFEITA IRÁ RETORNAR O CÓDIGO
                                                        DA EMPRESA QUE EMITIU A NOTA FISCAL DE VENDA */
                                                    Titulo.Titulo_EmpresaCod)
                                  /* TIPOS DE CRÉDITO / DÉBITO [65] RECEBIMENTO PARCIAL | [95] DEVOLUÇÃO BÔNUS FÁBRICA */
                                  JOIN GrupoRoma_DealernetWF..TituloMov		        (nolock) ON TituloMov.Titulo_Codigo = Titulo.Titulo_Codigo	       
                                                                                            AND TituloMov.TituloMov_EmpresaCod_Movimento    = Titulo.Titulo_EmpresaCod 
                                                                                            AND TituloMov.TituloMov_TipoCDCod		        IN ('64','95')
                                                                                            AND TituloMov.TituloMov_Status			        <> 'CAN'
                                  WHERE Titulo.Titulo_MovimentoFinanceiro = 'R'
                                  AND   G3_gerencialRegional.tipoTituloBonusFabrica	like '%'+CONVERT(varchar,Titulo.Titulo_TipoTituloCod)+'%'
                                  /*AND   CONVERT(varchar, Titulo.Titulo_TipoTituloCod)		IN  (SELECT SGA_parametrosComissao.tipoTituloBonusFabrica
                                                                                                 FROM GAMA..SGA_parametrosComissao	(nolock)
                                                                                                 WHERE SGA_parametrosComissao.ano		= ':ano'
                                                                                                 AND   SGA_parametrosComissao.mes		= ':mes'
                                                                                                 AND   SGA_parametrosComissao.COD_REG	= G3_gerencialRegional.id) */
                                  /* IGNORA AS [VI] VENDAS DE IMOBILIZADO, [VD ou DI] VENDAS DIRETAS */
                                  AND   Departamento.Departamento_Sigla NOT IN ('VI')
                                  /* DATA / PERÍODO IGUAL AO PERÍODO DO GERENCIAL */
                                  AND   YEAR(Titulo.Titulo_DataEmissao)			    = '$parAno'
                                  AND   MONTH(Titulo.Titulo_DataEmissao)			= '$parMes'
                                  /* TÍTULOS NÃO CANCELADOS */
                                  AND Titulo.Titulo_Status						    <> 'CAN'
                                  /* EMPRESAS CADASTRADAS DA REGIONAL INFORMADA */
                                  /*AND G3_gerencialRegional.id						= :regional*/
                                  AND  G3_gerencialEmpresas.codigoEmpresaERP        IN ($listaEmpresas)
                                  AND NotaFiscal.NotaFiscal_Status				    = 'EMI'
                                  AND NaturezaOperacao_GrupoMovimento				= 'VEN'
                                  AND NotaFiscal.NotaFiscal_Codigo				NOT IN (SELECT NotaFiscalNFReferencia_NFCod 
                                                                                        FROM   GrupoRoma_DealernetWF..NotaFiscalNFReferencia (nolock)
                                                                                        WHERE  NotaFiscalNFReferencia_Tipo =  'DEV')");
                        //,['ano' => $parAno, 'mes' => $parMes, 'regional' => $parRegional]);

        if (count($dbData) == 0) {
            $this->errors[] = ['errorTitle' => 'BÔNUS FÁBRICA',
                                'error'     => 'Não foram encontrados valores de Bônus Fábrica'];
            return FALSE;
        }

        $returnData = [];
        foreach($dbData as $data) {
            $returnData[] = ['numeroDocumento'      => $data->numeroDocumento,
                            'codigoVeiculo'         => $data->codigoVeiculo,
                            'codigoVendedor'        => $data->codigoVendedor,
                            'empresaOrigem'         => $data->codigoEmpresaOrigem,
                            'nomeEmpresaOrigem'     => $data->nomeEmpresaOrigem,
                            'empresaVenda'          => $data->codigoEmpresaVenda,
                            'codigoRegional'        => $data->codigoRegional,
                            'valorBonusFabrica'     => $data->valorBonus,
                            'estoque'               => trim($data->estoque)];
        }
        return $returnData;

    }   //-- receitaCustoVeiculos --//


    /**
     *  getLancamentosContabeis
     *  Carrega todos os saldos dos lançamentos contábeis, conforme as condições de filtro informadas
     * 
     *  @param  Illuminate\Http\Request
     *              Mes do lançamento *
     *              Ano do lançamento *
     *              Empresas da REGIONAL
     *              
     */
    public function getLancamentosContabeis(Request $request) {

        $conditions = NULL;

        if (isset($request->mesReferencia) && !empty($request->mesReferencia)) {
             $conditions .= "AND MONTH(Lancamento.Lancamento_Data)    = '".$request->mesReferencia."'\n";
        }
        if (isset($request->anoReferencia) && !empty($request->anoReferencia)) {
            $conditions .= "AND YEAR(Lancamento.Lancamento_Data)    = '".$request->anoReferencia."'\n";
        }
        if (isset($request->codigoRegional) && !empty($request->codigoRegional)) {
            $conditions .= "AND G3_gerencialRegional.id    = '".$request->codigoRegional."'\n";
        }

        $this->dataLancamentos = DB::select("SELECT mesLancamento			= MONTH(Lancamento.Lancamento_Data),
                                                    anoLancamento			= YEAR(Lancamento.Lancamento_data),
                                                    codigoContaContabil		= Lancamento.Lancamento_PlanoContaCod,
                                                    codigoSubContaContabil	= SubConta.SubConta_Codigo,
                                                    subContaContabil		= SubConta.SubConta_Descricao,
                                                    codigoRegional			= G3_gerencialRegional.id,
                                                    idEmpresa			    = G3_gerencialEmpresas.id,
                                                    centroCusto		        = ISNULL(G3_gerencialContaContabil.idCentroCusto, G3_gerencialCentroCusto.id),
                                                    --centroCusto		        = Lancamento.Lancamento_CentroResultadoCod,
                                                    idContaGerencial	    = G3_gerencialContaContabil.idContaGerencial,
                                                    creditoDebito		    = CASE WHEN Lancamento.Lancamento_Natureza = 'C' THEN 'CRD' ELSE 'DEB' END,
                                                    valorLancamento			= SUM(Lancamento.Lancamento_Valor) * 
                                                                            CASE WHEN Lancamento.Lancamento_Natureza = 'C' THEN 1
                                                                                ELSE -1
                                                                            END,
                                                    idTipoLancamento        = 1,
                                                    historicoLancamento     = (SELECT historicoTipoLancamento
                                                                               FROM GAMA..G3_gerencialTipoLancamento (nolock)
                                                                               WHERE G3_gerencialTipoLancamento.id = 1)
                                            FROM GrupoRoma_DealernetWF..Lancamento			(nolock)
                                            JOIN GrupoRoma_DealernetWF..PlanoConta			(nolock) ON PlanoConta.PlanoConta_Codigo						= Lancamento.Lancamento_PlanoContaCod
                                            JOIN GrupoRoma_DealernetWF..CentroResultado	(nolock) ON CentroResultado.CentroResultado_Codigo				= Lancamento.Lancamento_CentroResultadoCod
                                            LEFT JOIN GrupoRoma_DealernetWF..SubConta		(nolock) ON SubConta.SubConta_Codigo							= Lancamento.Lancamento_SubContaCod
                                            JOIN GAMA..G3_gerencialContaContabil			(nolock) ON G3_gerencialContaContabil.codigoContaContabilERP	= Lancamento.Lancamento_PlanoContaCod
                                            JOIN GAMA..G3_gerencialContaGerencial			(nolock) ON G3_gerencialContaGerencial.id						= G3_gerencialContaContabil.idContaGerencial
                                            JOIN GAMA..G3_gerencialCentroCusto				(nolock) ON G3_gerencialCentroCusto.codigoCentroCustoERP		= CentroResultado.CentroResultado_Codigo
                                            JOIN GAMA..G3_gerencialEmpresas				(nolock) ON G3_gerencialEmpresas.codigoEmpresaERP				= Lancamento.Lancamento_EmpresaCod
                                            JOIN GAMA..G3_gerencialRegional				(nolock) ON G3_gerencialRegional.id								= G3_gerencialEmpresas.codigoRegional

                                            WHERE Lancamento.Lancamento_Status = 'LIB'
                                            $conditions

                                            GROUP BY MONTH(Lancamento.Lancamento_Data), YEAR(Lancamento.Lancamento_data), Lancamento.Lancamento_PlanoContaCod,
                                                     SubConta.SubConta_Codigo, SubConta.SubConta_Descricao,
                                                     G3_gerencialRegional.id, G3_gerencialEmpresas.id,
                                                     ISNULL(G3_gerencialContaContabil.idCentroCusto, G3_gerencialCentroCusto.id), --Lancamento.Lancamento_CentroResultadoCod,
                                                     G3_gerencialContaContabil.idContaGerencial, Lancamento.Lancamento_Natureza");

        if (count($this->dataLancamentos) > 0)  return TRUE;
        else                                    return FALSE;
    }   //-- getLancamentosContabeis

    /**
     *  getLancamentosContabeis
     *  Carrega todos os saldos dos lançamentos contábeis, conforme as condições de filtro informadas
     * 
     *  @param  array      critérios ["fieldName" => fieldName, "fieldCriteria" => [=,<>,>=,<=,...], "values" => values, "andOr": 'AND [default]']
     * 
     *  @example    getSaldoContabil(["fieldName" => fieldName, "fieldCriteria" => [=,<>,>=,<=,...], "values" => values, "andOr": 'AND [default]'])
     *  @return boolean     FALSE: no data | TRUE: this->dataLancamentos
     *              
     */
    public function getSaldoContabil(array $criterios) {

        $conditions     = NULL;

        if (isset($this->mesAtivo) && !empty($this->mesAtivo)) {
             $conditions .= "AND MONTH(Lancamento.Lancamento_Data)    = '".$this->mesAtivo."'\n";
        }
        if (isset($this->anoAtivo) && !empty($this->anoAtivo)) {
            $conditions .= "AND YEAR(Lancamento.Lancamento_Data)    = '".$this->anoAtivo."'\n";
        }

        $sqlCriterios = NULL;
        foreach ($criterios as $index => $sqlWhere) {
            $sqlCriterios .= $sqlWhere['andOr'] ?? ' AND ';
            $sqlCriterios .= $sqlWhere['fieldName'];
            $sqlCriterios .= $sqlWhere['fieldCriteria'] ?? ' = ';
            $sqlCriterios .= (is_array($sqlWhere['values']) ? "('".implode("','", $sqlWhere['values'])."')" : $sqlWhere['values']);
            $sqlCriterios .= "\n";
        }

        $this->dataLancamentos = DB::select("SELECT mesLancamento			= MONTH(Lancamento.Lancamento_Data),
                                                    anoLancamento			= YEAR(Lancamento.Lancamento_data),
                                                    codigoContaContabil		= Lancamento.Lancamento_PlanoContaCod,
                                                    codigoRegional			= G3_gerencialRegional.id,
                                                    idEmpresa			    = G3_gerencialEmpresas.id,
                                                    --centroCusto		        = G3_gerencialCentroCusto.id,
                                                    --idContaGerencial	    = G3_gerencialContaContabil.idContaGerencial,
                                                    creditoDebito		    = CASE WHEN Lancamento.Lancamento_Natureza = 'C' THEN 'CRD' ELSE 'DEB' END,
                                                    valorLancamento			= SUM(Lancamento.Lancamento_Valor) * 
                                                                            CASE WHEN Lancamento.Lancamento_Natureza = 'C' THEN 1
                                                                                ELSE -1
                                                                            END,
                                                    idTipoLancamento        = 11,
                                                    historicoLancamento     = (SELECT historicoTipoLancamento+
                                                                                    CASE WHEN G3_gerencialTipoLancamento.historicoIncremental = 'S' THEN ' - OUTRAS CONTAS CONTABEIS' ELSE '' END
                                                                                FROM GAMA..G3_gerencialTipoLancamento (nolock)
                                                                                WHERE G3_gerencialTipoLancamento.id = 11)
                                            /* Lançamento Contabil - ERP / DMS */
                                            FROM GrupoRoma_DealernetWF..Lancamento			(nolock)
                                            /* Plano de Contas Contabil - ERP / DMS */
                                            JOIN GrupoRoma_DealernetWF..PlanoConta			(nolock) ON PlanoConta.PlanoConta_Codigo						= Lancamento.Lancamento_PlanoContaCod
                                            /* Sub Conta do Plano de Contas Contabil - ERP / DMS */
                                            LEFT JOIN GrupoRoma_DealernetWF..SubConta		(nolock) ON SubConta.SubConta_Codigo							= Lancamento.Lancamento_SubContaCod
                                            /* Empresas do Gerencial - SGA / Gerencial 3 */
                                            JOIN GAMA..G3_gerencialEmpresas				    (nolock) ON G3_gerencialEmpresas.codigoEmpresaERP				= Lancamento.Lancamento_EmpresaCod
                                            /* Regionais do Gerencial - SGA / Gerencial 3 */
                                            JOIN GAMA..G3_gerencialRegional				    (nolock) ON G3_gerencialRegional.id								= G3_gerencialEmpresas.codigoRegional

                                            WHERE Lancamento.Lancamento_Status = 'LIB'
                                            $conditions
                                            $sqlCriterios

                                            GROUP BY MONTH(Lancamento.Lancamento_Data), 
                                                     YEAR(Lancamento.Lancamento_data), 
                                                     Lancamento.Lancamento_PlanoContaCod, 
                                                     G3_gerencialRegional.id, 
                                                     G3_gerencialEmpresas.id, 
                                                     Lancamento.Lancamento_Natureza");

        if (count($this->dataLancamentos) > 0)  return TRUE;
        else                                    return FALSE;
    }   //-- getSaldoContabil


} //-- FIM Class:ImprtarContabilidade - Model

