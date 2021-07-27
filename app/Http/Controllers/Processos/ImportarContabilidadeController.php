<?php

namespace App\Http\Controllers\Processos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Processos\GerencialExcessoesController;

use App\Models\Processos\ImportarContabilidade;
use App\Models\GerencialCentroCusto;
use App\Models\GerencialContaContabil;
use App\Models\GerencialLancamento;
use App\Models\GerencialContaGerencial;
use App\Models\GerencialEmpresas;
use App\Models\GerencialTipoLancamento;
use App\Models\GerencialEstorno;

use App\Models\GerencialPeriodo;

class ImportarContabilidadeController extends Controller
{
    protected   $titulo             = "IMPORTAÇÃO DE LANÇAMENTOS CONTÁBEIS";
    protected   $importa;
    protected   $validateErrors;
    protected   $contaGerencial;
    protected   $barraProgresso;
    protected   $lancamentoGerencial;
    protected   $tipoLancamento;
    protected   $historico;
    protected   $centroCusto;
    protected   $excecoes;
    protected   $basesCalculo;
    protected   $periodo;
    protected   $estorno;

        public function __construct() 
    {
        $this->importa              = new ImportarContabilidade;
        $this->contaGerencial       = new GerencialContaGerencial;
        $this->lancamentoGerencial  = new GerencialLancamento;
        $this->tipoLancamento       = new GerencialTipoLancamento;
        $this->centroCusto          = new GerencialCentroCusto;
        $this->excecoes             = new GerencialExcessoesController;
        $this->empresa              = new GerencialEmpresas;
        $this->periodo              = new GerencialPeriodo;
        $this->estorno              = new GerencialEstorno;        
        
        // Carrega as definições do Tipo de Lançamento [A - AUTOMÁTICO] Código 1
        $this->historico = $this->tipoLancamento->getHistoricoLancamento(1);

    }

    /**
     * Exibe formulário para informar o período do gerencial para processamento
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Verifica se existem períodos Abertos para lançamento [AB | LC]
        $periodo = $this->periodo->checkPeriodo();

        // Caso não existem períodos abertos exibe mensagem de erro
        if ($periodo === FALSE)  return ("<span id='showMsg' data-title='PERÍODO GERENCIAL'
                                                    data-message='Não foi encontrado nenhum período (mês/ano) ativo e aberto [AB | LC]'></span>");
        
        // Se houverem mais de um período aberto retorna mensagem de erro solicitando a correção
        if (count($periodo) > 1) {
            $periodosAbertos = "";
            foreach ($periodo as $row => $data) {
                $periodosAbertos .= (empty($periodosAbertos) ? '' : ', ').$data['MESANO'];
            }
            return ("<span id='showMsg' data-title='PERÍODO GERENCIAL'
                                        data-message='Os períodos ".$periodosAbertos." estão abertos. Favor corrigir e tentar novamente.'></span>");
        }

        // Encontrou apenas um período aberto e segue o fluxo normal
        return view('layouts.layoutProcessamento', ['tituloPagina' => $this->titulo, 
                                                    'action' => 'importacaoContabil', 
                                                    'mes' => ($periodo[0]['mes'] ?? null), 
                                                    'ano' => ($periodo[0]['ano'] ?? null)]);
    }

    /**
     *  processaImportacaoContabil
     *  Executa as rotinas de importação dos lançamentos contábeis e dos valores referentes 
     *  à receita de veículos
     * 
     *  @param  Illuminate\Http\Request
     * 
     */
    public function processaImportacaoContabil(Request $request) {

        // Inicializa a transação com o banco de dados
        DB::beginTransaction();

        // IDENTIFICA AS EMPRESAS DA REGIONAL SELECIONADA
        $regionalEmpresas = GerencialEmpresas::where('codigoRegional', $request->codigoRegional)->get();

        // Atribui os códigos das empresas da regional à propriedade empresasRegional da Model ImportarContabilidade
        foreach ($regionalEmpresas as $data) {
            $this->importa->empresasRegional[] = $data->codigoEmpresaERP;
        }

        if (!isset($this->importa->empresasRegional) || empty($this->importa->empresasRegional)) {
            return ("<span id='showMsg' data-title='IMPORTAÇÃO DE LANÇAMENTOS CONTÁBEIS'
                            data-message='Regional não informada ou não foram encontradas empresas cadastradas para esta regional!'></span>");
        }

        // Atribui o código da Regional à propriedade codigoRegional da Model ImportarContabilidade
        $this->importa->codigoRegional = $request->codigoRegional;

        //--/ 1. Período Gerencial
        $periodo = $this->periodo->checkPeriodo($request->mesReferencia, $request->anoReferencia);
        $this->importa->mesAtivo    = $request->mesReferencia;
        $this->importa->anoAtivo    = $request->anoReferencia;

        // Caso não existem períodos abertos exibe mensagem de erro
        if ($periodo === false)  {
            $this->importa->errors[] = ['errorTitle'    => 'PERÍODO GERENCIAL',
                                        'error'         => 'Não foi encontrado nenhum período (mês/ano) ativo e aberto [AB | LC]'];
            return view('processamento.validacao', ['errors' => $this->importa->errors]);
        }

        // Se houver mais de um período aberto retorna mensagem de erro solicitando a correção
        if (count($periodo) > 1) {
            $periodosAbertos = "";
            foreach ($periodo as $row => $data) {
                $periodosAbertos .= (empty($periodosAbertos) ? '' : ', ').$data['MESANO'];
            }

            $this->importa->errors[] = ['errorTitle'    => 'PERÍODO GERENCIAL',
                                        'error'         => 'Os períodos '.$periodosAbertos.' estão abertos. Favor corrigir e tentar novamente.'];
            return view('processamento.validacao', ['errors' => $this->importa->errors]);
        }

        $mensagem = '';
        // Importa os lançamentos contábeis se a opção "LANÇAMENTOS FOR SELECIONADA"
        if (isset($request->importarLancamentos) && $request->importarLancamentos == 1) {
            //--/ 2. Set Período
            $this->periodo->setPeriodo($request->mesReferencia, $request->anoReferencia);

            //--/ 3. Processa as validações para importação dos lançamentos contábeis
            if (!$this->validaImportacao($request)) {
                DB::rollBack();
                return view('processamento.validacao', ['errors' => $this->importa->errors]);
            }

            //--/ 4. Processa a importação dos lançamentos contábeis
            if (!$this->lancamentosContabeis($request))  {
                DB::rollBack();
                return view('processamento.validacao', ['errors' => $this->lancamentoGerencial->errors]);
            }

            //--/ 5. Processa os valores de Receita, Custo, Impostos, Bônus Fábrica, Bônus Empresa e HoldBack
            if (!$this->valoresVeiculos($request))  {
                DB::rollBack();
                return view('processamento.validacao', ['errors' => $this->lancamentoGerencial->errors]);
            }

            $mensagem   .= "<li>Lançamentos contábeis importados com sucesso!</li>";
        } 

        // Processa Exceções de Outras Contas Contábeis se a opção "[EXCEÇÕES] - OUTRAS CONTAS" estiver selecionada
        if (isset($request->outrasContas) && $request->outrasContas == 1) {
            if (!$this->excecoes->importaOutrasContas($this->importa->codigoRegional, $request)) {
                DB::rollBack();
                return view('processamento.validacao', ['errors' => $this->excecoes->errors]);
            }
            else {
                $mensagem .= "<li>[EXCEÇÕES - OUTRAS CONTAS] - Outras Contas importado com sucesso";
            }
        }

        // Processa Exceções de Outras Contas Contábeis se a opção "[EXCEÇÕES] - OUTRAS CONTAS" estiver selecionada
        if (isset($request->amortizacao) && $request->amortizacao == 1) {
            if (!$this->excecoes->processaAmortizacao($request)) {
                DB::rollBack();
                return view('processamento.validacao', ['errors' => $this->excecoes->errors]);
            }
            else {
                $mensagem .= "<li>[EXCEÇÕES - AMORTIZAÇÕES] - Amortizações processadas com sucesso";
            }
        }

        // Processa os ESTORNOS registrados
        if (isset($request->estorno) && $request->estorno == 1) {
            if (!$this->processaEstornos($request)) {
                DB::rollBack();
                return view('processamento.validacao', ['errors' => $this->estorno->errors]);
            }
            else {
                $mensagem .= "<li>[ESTORNOS] - Estornos processadas com sucesso";
            }
        }

        // Efetiva o registro dos dados no banco de dados se não ocorrer nenhum erro
        DB::commit();

        return ("<span id='showMsg' data-title='IMPORTAÇÃO DE LANÇAMENTOS CONTÁBEIS'
                            data-message='<ul>".$mensagem."</ul>'></span>");

    }

    /**
     *  VALIDAÇÃO DE DADOS PARA A IMPORTAÇÃO DOS LANÇAMENTOS CONTÁBEIS
     * 
     *  Realiza a validação dos itens abaixo:
     * 
     *  1. Checagem do período do gerencial que está aberto para lançamentos [inicialmente situação = 'LC'] - checkPeriodo()
     *  2. Define as variáveis de Mês e Ano para o período do gerencial                                     - setPeriodo()
     *  3. Verifica se existem contas contábeis sem associação com conta gerencial                          - checkContaContabil()
     *  4. Verifica se foram realizadas as integrações contábeis                                            - checkIntegracaoContabil()
     *  5. Verifica se existem vendedores sem CPF cadastrados no ERP Workflow                               - checkVendedores()
     *  6. Verifica se existem Notas Fiscais sem identificação de vendedor                                  - checkNotaVendedor()
     *  7. Verifica se existem lançamentos abertos na contabilidade                                         - checkLancamentoAbertos()
     *  8. Verifica se existe diferença no valor das vendas Notas Fiscais x Contabilidade:
     *      8a. Carrega o valor total de vendas das notas fiscais emitidas                                  - totalVendasNF()
     *      8b. Carrega o valor total de vendas das notas fiscais contabilizadas                            - totalVendasCTB()
     * 
     *  @param  \Illuminate\Http\Request  $request
     */
    public function validaImportacao(Request $request) {
        
        //-- Inicializa a validação
        $validate   = TRUE;


        //--/ 3. Contas Contábeis sem associação de conta Gerencial
        if(!$this->importa->checkContaContabil($this->periodo->mesAtivo, $this->periodo->anoAtivo)) $validate = FALSE;

        //--/ 4. Integrações Contábeis
        if (!$this->importa->checkIntegracaoContabil($this->periodo->mesAtivo, $this->periodo->anoAtivo))   $validate = FALSE;

        //--/ 5. Vendedores sem CPF
        if(!$this->importa->checkVendedores($this->periodo->mesAtivo, $this->periodo->anoAtivo))          $validate = FALSE;

        //--/ 6. Notas Fiscais sem identificação do vendedor
        if(!$this->importa->checkNotaVendedor($this->periodo->mesAtivo, $this->periodo->anoAtivo))        $validate = FALSE;

        //--/ 7. Lançamentos contábeis abertos
#        if(!$this->importa->checkLacamentosAbertos())   $validate = FALSE;

        //--/ 8. Diferença no valor de vendas (notas x lançamentos)
        $valorNotasFiscais = $this->importa->totalVendasNF($this->periodo->mesAtivo, $this->periodo->anoAtivo);
        $vendaTotalNF      = 0;
        $vendaTotalCTB     = 0;
        if (!$valorNotasFiscais)    $validate = FALSE;
        else                        $vendaTotalNF = $valorNotasFiscais[0]['valorTotalVenda'];

        $valorContabilizado = $this->importa->totalVendasCTB($this->periodo->mesAtivo, $this->periodo->anoAtivo);
        if (!$valorNotasFiscais)    $validate = FALSE;
        else                        $vendaTotalCTB = $valorContabilizado[0]['valorTotalVenda'];

        // Valida a diferença dos valores
        if (number_format($vendaTotalNF,2) <> number_format($vendaTotalCTB,2)) {
            $this->importa->errors[] = ['errorTitle' => 'NOTAS FISCAIS X LANÇAMENTOS CONTÁBEIS [VALOR DE VENDA]',
                                        'error'      => 'O valor total de <strong>NFs EMITIDAS é DIFERENTE DO VALOR CONTABILIZADO</strong><br>
                                                            NFs Emitidas: '.number_format($vendaTotalNF,2,',','.').'<br>Contabilizado: '.number_format($vendaTotalCTB,2,',','.')];
                $validate = FALSE;
        }

        if (!$validate) return FALSE;
        else            return TRUE;
    }   //-- validaImportacao() --//

    /**
     *  lancamentoContabeis
     *  Apura os saldos dos lançamentos contábeis no período informado, de acordo com as definições
     *  de conta gerencial x conta contábil.
     *  
     *  São apurados os saldos por empresa, centro de custo, conta gerencial e conta contábil
     * 
     *  @param  Illuminate\Http\Request
     * 
     */
    public function lancamentosContabeis(Request $request) {
        
        // Carrega os lancamentos do período e regional informados
        $this->importa->getLancamentosContabeis($request);

        // Verifica se existem lançamentos gerenciais para as condições informadas
        // caso exista, remove todos os lançamentos para evitar duplicidade
        $this->lancamentoGerencial->deleteLancamentosGerenciais([['fieldName' => 'idTipoLancamento', 'values' => 1],
                                                                 ['fieldName' => 'idEmpresa', 'fieldComparison' => 'IN', 'values' => $this->importa->empresasRegional]]);

        // Grava os lançamentos gerenciais
        return $this->lancamentoGerencial->gravaLancamento($this->importa->dataLancamentos);

    }   //-- lancamentoContabeis --//

    /**
     *  valoresVeiculos
     *  Apurar os valores referente à receita de veículos
     * 
     *  1. Apura os valores de Receita, Custo e Impostos das vendas de veículos - receitaCustoVeiculos()
     *  2. Apura os valores de Bônus Empresa nas vendas de veículos             - getBonusEmpresa()
     *  3. Apura os valores de Hold Back                                        - getHoldBack()
     *  4. Apura os valores de Bônus Fábrica                                    - getBonusFabrica()
     * 
     *  @param  \Illuminate\Http\Request  $request
     * 
     *  @return string  (onError)
     */
    public function valoresVeiculos(Request $request) {

        $lancamentosVeiculos = [];
        
        //--/ 9. Apura a Receita, Custo e ICMS de veículos vendidos 
        $receitaCusto = $this->importa->receitaCustoVeiculos();
        if ($receitaCusto) {
            // Identifica as contas de receita, custo, impostos e bônus
            $contasVeiculos     = $this->contaGerencial->getContasVeiculos();
            if ($contasVeiculos) {
                // Processa as vendas encontradas para realozação dos valores
                foreach ($receitaCusto as $row => $dataVeiculos) {
                    // Carrega os dados da empresa de destino para identificação no histórico do lançamento
                    $empresaDestino = GerencialEmpresas::where('codigoEmpresaERP', $dataVeiculos['codigoEmpresaVenda'])->get();
                    $empresaOrigem  = GerencialEmpresas::where('codigoEmpresaERP', $dataVeiculos['codigoEmpresaOrigem'])->get();

                    foreach ($empresaDestino as $dadosEmpresa)  { $empresaDestino   = $dadosEmpresa->id; }
                    foreach ($empresaOrigem as $dadosEmpresa)   { $empresaOrigem    = $dadosEmpresa->id; }

                    // Verifica se existem contas gerenciais parametrizadas como conta de receita / custo de veiculos
                    if (!isset($contasVeiculos[$dataVeiculos['codigoCentroCusto']])) {
                        $dataCusto  = GerencialCentroCusto::find($dataVeiculos['codigoCentroCusto']);
                        $this->lancamentoGerencial->errors[]    = [ 'errorTitle' => 'CONTAS DE RECEITAS E CUSTO DE VEICULOS',
                                                                    'error'      => 'Não foi encontrada nenhuma conta de Receita e Custo para o centro de custo '.
                                                                                    $dataCusto->siglaCentroCusto.' - '.
                                                                                    $dataCusto->descricaoCentroCusto];
                        return FALSE;
                    }

                    // Processa as contas de veículos para identificar as contas nas quais existem valores
                    // a serem realocados
                    foreach($contasVeiculos[$dataVeiculos['codigoCentroCusto']] as $row => $infoConta) {
                        switch($infoConta['tipoContaVeiculo']) {
                            // RECEITA / DEVOLUÇÃO
                            case 'RCD':
                                $valorLancamento = $dataVeiculos['valorReceita'];
                                $creditoDebito   = ($valorLancamento < 0 ? 'D' : 'C');
                                $historicoIC     = '[RECEITA/DEVOLUÇÃO] | NF: '.$dataVeiculos['numeroDocumento'].' | Veículo: '.
                                                    $dataVeiculos['codigoVeiculo'].' | Op: '.$dataVeiculos['tipoOperacao'];
                                break;
                            // CUSTO
                            case 'CST':
                                $valorLancamento = $dataVeiculos['valorCusto'];
                                $creditoDebito   = ($valorLancamento < 0 ? 'D' : 'C');
                                $historicoIC     = '[CUSTO] | NF: '.$dataVeiculos['numeroDocumento'].' | Veículo: '.
                                                    $dataVeiculos['codigoVeiculo'].' | Op: '.$dataVeiculos['tipoOperacao'];
                                break;
                            // ICMS
                            case 'ICM':
                                $valorLancamento = $dataVeiculos['valorICMS'];
                                $creditoDebito   = ($valorLancamento < 0 ? 'D' : 'C');
                                $historicoIC     = '[ICMS] | NF: '.$dataVeiculos['numeroDocumento'].' | Veículo: '.
                                                    $dataVeiculos['codigoVeiculo'].' | Op: '.$dataVeiculos['tipoOperacao'];
                                break;
                            // PIS
                            // Valor calculado conforme memória de cálculo:
                            //  BaseCalculo = ValorVenda - ValorCusto
                            //  Valor PIS   = BaseCalculo * 0.0065 (0,65%)
                            case 'PIS':
                                $valorLancamento = ($dataVeiculos['valorReceita'] - $dataVeiculos['valorCusto']) * 0.0065;
                                $creditoDebito   = ($valorLancamento < 0 ? 'D' : 'C');
                                $historicoIC     = '[PIS] | NF: '.$dataVeiculos['numeroDocumento'].' | Veículo: '.
                                                    $dataVeiculos['codigoVeiculo'].' | Op: '.$dataVeiculos['tipoOperacao'];
                                break;
                            // COFINS
                            // Valor calculado conforme memória de cálculo:
                            //  BaseCalculo = ValorVenda - ValorCusto
                            //  Valor COFINS   = BaseCalculo * 0.03 (3%)
                            case 'PIS':
                                $valorLancamento = ($dataVeiculos['valorReceita'] - $dataVeiculos['valorCusto']) * 0.03;
                                $creditoDebito   = ($valorLancamento < 0 ? 'D' : 'C');
                                $historicoIC     = '[COFINS] | NF: '.$dataVeiculos['numeroDocumento'].' | Veículo: '.
                                                    $dataVeiculos['codigoVeiculo'].' | Op: '.$dataVeiculos['tipoOperacao'];
                                break;
                            default:
                                $valorLancamento = 0;
                                break;
                        }

                        // Define o histórico que será utilizado no registro do lançamento
                        if ($this->historico && !empty($this->historico['historicoPadrao'])) {
                            if ($this->historico['incremental'] == 'S') {
                                $historicoOrigem    = $this->historico['historicoPadrao'].$historicoIC.' | Destino: '.$empresaDestino[0]->nomeAlternativo;
                                $historicoDestino   = $this->historico['historicoPadrao'].$historicoIC.' | Origem: '.$dataVeiculos['nomeEmpresaOrigem'];
                            }
                            else {
                                $historicoOrigem    = $historicoDestino = $this->historico['historicoPadrao'];
                            }
                        }
                        else {
                            $historicoOrigem    = $historicoIC.' | Destino: '.$empresaDestino[0]->nomeAlternativo;
                            $historicoDestino   = $historicoIC.' | Origem: '.$dataVeiculos['nomeEmpresaOrigem'];
                        }

                        // Se o valor do lançamento for diferente de 0 (zero),
                        // prepara os dados para registro do lançamento na tabela de dados
                        if ($valorLancamento <> 0) {
                            // Prepara os dados para inclusão do lançamento gerencial NA ORIGEM
                            // idTipoLancamento = 1 = [AUTOMÁTICO]

                            // Registra a contrapartida apenas se as empresas de ORIGEM e DESTINO forem DIFERENTES
                            if ($dataVeiculos['codigoEmpresaOrigem'] != $dataVeiculos['codigoEmpresaVenda']) {
                            $lancamentosVeiculos[]        = ['anoLancamento'         => $this->periodo->anoAtivo,
                                                            'mesLancamento'         => $this->periodo->mesAtivo,
                                                            'codigoContaContabil'   => $infoConta['codigoContaContabil'], //$infoConta['contaContabil'],
                                                            'idEmpresa'             => $empresaOrigem,
                                                            'centroCusto'           => $dataVeiculos['codigoCentroCusto'],
                                                            'idContaGerencial'      => $infoConta['codigoContaGerencial'],
                                                            'creditoDebito'         => ($creditoDebito == 'D' ? 'CRD' : 'DEB'),
                                                            'valorLancamento'       => ($valorLancamento * -1),
                                                            'idTipoLancamento'      => 1,       // [A] AUTOMÁTICO
                                                            'historicoLancamento'   => '[A - AUTOMÁTICO | IC | REC e CST] '.$historicoOrigem,
                                                            'numeroDocumento'       => $dataVeiculos['numeroDocumento']];
                            }
                            
                            // Prepara os dados para inclusão do lançamento gerencial DESTINO
                            // idTipoLancamento = 1 = [A - AUTOMÁTICO]
                            $lancamentosVeiculos[]        = ['anoLancamento'          => $this->periodo->anoAtivo,
                                                            'mesLancamento'         => $this->periodo->mesAtivo,
                                                            'codigoContaContabil'   => $infoConta['codigoContaContabil'], //$infoConta['contaContabil'],
                                                            'idEmpresa'             => $empresaDestino,
                                                            'centroCusto'           => $dataVeiculos['codigoCentroCusto'],
                                                            'idContaGerencial'      => $infoConta['codigoContaGerencial'],
                                                            'creditoDebito'         => ($creditoDebito == 'D' ? 'DEB' : 'CRD'),
                                                            'valorLancamento'       => $valorLancamento,
                                                            'idTipoLancamento'      => 1,       // [A] AUTOMÁTICO
                                                            'historicoLancamento'   => '[A - AUTOMÁTICO | IC | REC e CST] '.$historicoDestino,
                                                            'numeroDocumento'       => $dataVeiculos['numeroDocumento']];
                        }
                    }
                }
            }   //--// Contas Veículos
            else {
                $this->lancamentoGerencial->errors[] = ['errorTitle' => 'CONTAS GERENCIAIS DE VEÍCULOS',
                                                        'error'      => 'Não foram encontras Contas Gerenciais para Receitas e Custos de Veículos'];
                return FALSE;
            }
        }   //--// Receita, Custo, Impostos



        //--/ 10. Apura os Valores de Bônus Empresa
        $bonusEmpresa = $this->importa->getBonusEmpresa();
        if ($bonusEmpresa) {
            foreach ($bonusEmpresa as $row => $data) {
                // Carrega os dados da empresa de destino para identificação no histórico do lançamento
                $empresaDestino = GerencialEmpresas::where('codigoEmpresaERP', $data['codigoEmpresaVenda'])->get();
                $empresaOrigem  = GerencialEmpresas::where('codigoEmpresaERP', $data['codigoEmpresaOrigem'])->get();

                foreach ($empresaDestino as $dadosEmpresa)  { $empresaDestino   = $dadosEmpresa->id; }
                foreach ($empresaOrigem as $dadosEmpresa)   { $empresaOrigem    = $dadosEmpresa->id; }

                // Prepara o histórico do lançamento de acordo com as configurações do tipo de lançamento
                // 1 - [A - AUTOMÁTICO ...]
                if ($this->historico && !empty($this->historico['historicoPadrao'])) {
                    if ($this->historico['incremental'] == 'S') {
                        $historicoOrigem    = $this->historico['historicoPadrao'].' | Destino: '.$empresaDestino[0]->nomeAlternativo.' | VEÍCULO: '.$data['codigoVeiculo'];
                        $historicoDestino   = $this->historico['historicoPadrao'].' | Origem: '.$data['nomeEmpresaOrigem'].' | VEÍCULO: '.$data['codigoVeiculo'];
                    }
                    else {
                        $historicoOrigem = $historicoDestino = $this->historico['historicoPadrao'];
                    }
                }

                if($data['valorBonus'] > 0) {

                    // Registra a contrapartida apenas se as empresas de ORIGEM e DESTINO forem DIFERENTES
                    if ($data['codigoEmpresaOrigem'] != $data['codigoEmpresaVenda'])  {
                        // Deduz o valor na origem (Lançamento a Crédito)
                        $lancamentosVeiculos[]        = ['anoLancamento'         => $this->periodo->anoAtivo,
                                                        'mesLancamento'         => $this->periodo->mesAtivo,
                                                        'codigoContaContabil'   => $data['contaContabil'],
                                                        'idEmpresa'             => $empresaOrigem,
                                                        'centroCusto'           => $data['codigoCentroCusto'],
                                                        'idContaGerencial'      => $data['codigoContaGerencial'],
                                                        'creditoDebito'         => 'CRD',
                                                        'valorLancamento'       => $data['valorBonus'],
                                                        'idTipoLancamento'      => 1,       // [A] AUTOMÁTICO
                                                        'historicoLancamento'   => '[A - AUTOMÁTICO | BÔNUS EMPRESA - CONTRAPARTIDA] '.$historicoOrigem,
                                                        'numeroDocumento'       => $data['numeroDocumento']];
                    }

                    // Registra o valor do bõnus no destino (Lançamento a Débito)
                    $lancamentosVeiculos[]        = ['anoLancamento'         => $this->periodo->anoAtivo,
                                                     'mesLancamento'         => $this->periodo->mesAtivo,
                                                     'codigoContaContabil'   => $data['contaContabil'],
                                                     'idEmpresa'             => $empresaDestino,
                                                     'centroCusto'           => $data['codigoCentroCusto'],
                                                     'idContaGerencial'      => $data['codigoContaGerencial'],
                                                     'creditoDebito'         => 'DEB',
                                                     'valorLancamento'       => ($data['valorBonus'] * -1),
                                                     'idTipoLancamento'      => 1,       // [A] AUTOMÁTICO
                                                     'historicoLancamento'   => '[A - AUTOMÁTICO | BÔNUS EMPRESA - REGISTRO NO DESTINO] '.$historicoDestino,
                                                     'numeroDocumento'       => $data['numeroDocumento']];
                }
            }
        }   //---// BÔNUS EMPRESA

        //--/ 11. Apura os Valores de Hold Back
        $holdBack = $this->importa->getHoldBack();
        if ($holdBack) {
            foreach ($holdBack as $row => $data) {
                // Carrega os dados da empresa de destino para identificação no histórico do lançamento
                $empresaDestino = GerencialEmpresas::where('codigoEmpresaERP', $data['codigoEmpresaVenda'])->get();
                $empresaOrigem  = GerencialEmpresas::where('codigoEmpresaERP', $data['codigoEmpresaOrigem'])->get();

                foreach ($empresaDestino as $dadosEmpresa)  { $empresaDestino   = $dadosEmpresa->id; $nomeEmpresaDestino = $dadosEmpresa->nomeAlternativo; }
                foreach ($empresaOrigem  as $dadosEmpresa)  { $empresaOrigem    = $dadosEmpresa->id; }

                // Prepara o histórico do lançamento de acordo com as configurações do tipo de lançamento
                // 1 - [A - AUTOMÁTICO ...]
                if ($this->historico && !empty($this->historico['historicoPadrao'])) {
                    if ($this->historico['incremental'] == 'S') {
                        $historicoOrigem    = $this->historico['historicoPadrao'].' | Destino: '.$nomeEmpresaDestino.' | VEÍCULO: '.$data['codigoVeiculo'];
                        $historicoDestino   = $this->historico['historicoPadrao'].' | Origem: '.$data['nomeEmpresaOrigem'].' | VEÍCULO: '.$data['codigoVeiculo'];
                    }
                    else {
                        $historicoOrigem = $historicoDestino = $this->historico['historicoPadrao'];
                    }
                }

                if($data['valorHoldBack'] > 0) {
                    // Regista a contrapartida na origem apenas se as empresas de ORIGEM e DESTINO foram DIFERENTES
                    if ($data['codigoEmpresaOrigem'] != $data['codigoEmpresaVenda']) {
                        // Deduz o valor na origem (Lançamento a Crédito)
                        $lancamentosVeiculos[]        = ['anoLancamento'         => $this->periodo->anoAtivo,
                                                        'mesLancamento'         => $this->periodo->mesAtivo,
                                                        'codigoContaContabil'   => NULL,
                                                        'idEmpresa'             => $data['codigoEmpresaOrigem'], //$empresaOrigem,
                                                        'centroCusto'           => $data['codigoCentroCusto'],
                                                        'idContaGerencial'      => $data['codigoContaGerencial'],
                                                        'creditoDebito'         => 'DEB',
                                                        'valorLancamento'       => ($data['valorHoldBack'] * -1),
                                                        'idTipoLancamento'      => 1,       // [A] AUTOMÁTICO
                                                        'historicoLancamento'   => '[A - AUTOMÁTICO | IC | HOLD BACK] '.$historicoOrigem,
                                                        'numeroDocumento'       => $data['numeroDocumento']];
                    }

                    // Registra o valor do bõnus no destino (Lançamento a Débito)
                    $lancamentosVeiculos[]        = ['anoLancamento'         => $this->periodo->anoAtivo,
                                                     'mesLancamento'         => $this->periodo->mesAtivo,
                                                     'codigoContaContabil'   => NULL,
                                                     'idEmpresa'             => $data['codigoEmpresaVenda'], //$empresaDestino,
                                                     'centroCusto'           => $data['codigoCentroCusto'],
                                                     'idContaGerencial'      => $data['codigoContaGerencial'],
                                                     'creditoDebito'         => 'CRD',
                                                     'valorLancamento'       => $data['valorHoldBack'],
                                                     'idTipoLancamento'      => 1,       // [A] AUTOMÁTICO
                                                     'historicoLancamento'   => '[A - AUTOMÁTICO | HOLD BACK] '.$historicoOrigem,
                                                     'numeroDocumento'       => $data['numeroDocumento']];
                }
            }
        }   //--// Hold Back

        //--/ 12. Apura os Valores de Bônus Fábrica
        $bonusFabrica = $this->importa->getBonusFabrica();
        if ($bonusFabrica) {

            foreach ($bonusFabrica as $row => $data) {
                $empresaVenda       = GerencialEmpresas::where('codigoEmpresaERP', $data['empresaVenda'])->get();
                $empresaOrigem      = GerencialEmpresas::where('codigoEmpresaERP', $data['empresaOrigem'])->get();

                foreach ($empresaVenda  as $dadosEmpresa)  { $nomeEmpresaVenda  = $dadosEmpresa->nomeAlternativo; $codigoEmpresaVenda = $dadosEmpresa->id; }
                foreach ($empresaOrigem as $dadosEmpresa)  { $empresaOrigem = $dadosEmpresa->id; }

                $contaGerencial     = $this->contaGerencial->contaGerencialVeiculos('BFB');
                $contaContabil      = GerencialContaContabil::where('gerencialContaContabil.idContaGerencial', $contaGerencial)
                                                            ->where('receitaVeiculo', 'S')
                                                            ->get();

                foreach ($contaContabil as $dadosConta)  { $contaContabil   = $dadosConta->id; }

                $historicoOrigem    = ' VEIC: '.$data['codigoVeiculo']." DEST: ".$nomeEmpresaVenda;
                $historicoDestino   = ' VEIC: '.$data['codigoVeiculo']." ORIG: ".$data['nomeEmpresaOrigem'];

                // Registra a contrapartida na origem apenas se as empresas de ORIGEM e DESTINO forem DIFERENTES
                if ($data['empresaOrigem'] != $data['empresaVenda']) {
                    // Deduz o valor na origem (Lançamento a Débito)
                    $lancamentosVeiculos[]        = ['anoLancamento'         => $this->periodo->anoAtivo,
                                                    'mesLancamento'         => $this->periodo->mesAtivo,
                                                    'codigoContaContabil'   => $contaContabil,
                                                    'idEmpresa'             => $empresaOrigem,
                                                    'centroCusto'           => $this->centroCusto->getCodigoCentroCusto($data['estoque'], 'ERP'),
                                                    'idContaGerencial'      => $contaGerencial,
                                                    'creditoDebito'         => 'DEB',
                                                    'valorLancamento'       => ($data['valorBonusFabrica'] * -1),
                                                    'idTipoLancamento'      => 1,       // [A] AUTOMÁTICO
                                                    'historicoLancamento'   => '[A - AUTOMÁTICO | BÔNUS FÁBRICA] '.$historicoOrigem,
                                                    'numeroDocumento'       => $data['numeroDocumento']];
                }

                // Registra o valor no destino (Lançamento a crédito)
                $lancamentosVeiculos[]        = ['anoLancamento'         => $this->periodo->anoAtivo,
                                                 'mesLancamento'         => $this->periodo->mesAtivo,
                                                 'codigoContaContabil'   => $contaContabil,
                                                 'idEmpresa'             => $codigoEmpresaVenda,
                                                 'centroCusto'           => $this->centroCusto->getCodigoCentroCusto($data['estoque'], 'ERP'),
                                                 'idContaGerencial'      => $this->contaGerencial->contaGerencialVeiculos('BFB'),
                                                 'creditoDebito'         => 'DEB',
                                                 'valorLancamento'       => ($data['valorBonusFabrica'] * -1),
                                                 'idTipoLancamento'      => 1,       // [A] AUTOMÁTICO
                                                 'historicoLancamento'   => '[A - AUTOMÁTICO | BÔNUS FÁBRICA] '.$historicoDestino,
                                                 'numeroDocumento'       => $data['numeroDocumento']];
            }
        }   //--// Bônus Fábrica

        // Registra os lançamentos gerenciais
        if (!empty($lancamentosVeiculos))   return $this->lancamentoGerencial->gravaLancamento($lancamentosVeiculos);
        return TRUE;
    }

    /**
     *  Processa os estornos cadastrados
     * 
     *  
     */
    public function processaEstornos(Request $request) {
        $lancamentoEstorno  = [];

        $dbData     = GerencialEstorno::where('estornoAtivo', 'S')->get();

        if(!$dbData)  return TRUE;
        else {
            // Excluir os lançamentos gerenciais de estorno no período ativo
            $this->lancamentoGerencial->deleteLancamentosGerenciais([['fieldName' => 'idTipoLancamento', 'values' => 14]]);

            // Identifica os parâmetros do tipo de lançamento
            $tipoLancamento = $this->tipoLancamento->getTipoLancamento(14);

            foreach ($dbData as $row => $data) {
                // Identifica os lançamentos por empresa e a condição para o estorno (conta gerencial, conta contábil ou centro de custo)
                $filterLancamento = [['column' => 'G3_gerencialLancamentos.mesLancamento', 'operator'   => '=', 'value' => $request->mesReferencia],
                                     ['column' => 'G3_gerencialLancamentos.anoLancamento', 'operator'   => '=', 'value' => $request->anoReferencia]];

                // Verifica se foi informada a conta gerencial
                if (!empty($data->idContaGerencial)) {
                    $filterLancamento[] = ['column' => 'G3_gerencialLancamentos.idContaGerencial', 'operator'   => '=', 'value' => $data->idContaGerencial];
                }

                // Verifica se foi informada a conta contabil
                if (!empty($data->codigoContaContabil)) {
                    $filterLancamento[] = ['column' => 'G3_gerencialLancamentos.codigoContaContabil', 'operator'   => '=', 'value' => $data->codigoContaContabil];
                }

                // Verifica se foi informado o centro de custo
                if (!empty($data->idCentroCusto)) {
                    $filterLancamento[] = ['column' => 'G3_gerencialLancamentos.centroCusto', 'operator'   => '=', 'value' => $data->idCentroCusto];
                }

                // Identifica os lançamentos de acordo com os critérios informados
                $dbLancamentos  = $this->lancamentoGerencial->getLancamentos(json_encode($filterLancamento));

                // Prepara os dados para registro dos lançamentos de estorno
                if($dbLancamentos) {

                    foreach ($dbLancamentos as $row => $dataLancamento) {
                        $lancamentoEstorno[]    = [ 'anoLancamento'         => $request->anoReferencia,
                                                    'mesLancamento'         => $request->mesReferencia,
                                                    'codigoContaContabil'   => $dataLancamento->contaContabil,
                                                    'idEmpresa'             => $dataLancamento->codigoEmpresa,
                                                    'centroCusto'           => $dataLancamento->codigoCentroCusto,
                                                    'idContaGerencial'      => $dataLancamento->codigoContaGerencial,
                                                    'creditoDebito'         => ($dataLancamento->valorLancamento < 0 ? 'CRD' : 'DEB'),
                                                    'valorLancamento'       => ($dataLancamento->valorLancamento * -1),
                                                    'idTipoLancamento'      => 14,       // [N] ESTORNOS
                                                    'historicoLancamento'   => $tipoLancamento->historicoTipoLancamento];
                    }
                }
            }

            // Grava os lançamentos de estorno
            return $this->lancamentoGerencial->gravaLancamento($lancamentoEstorno);
        }
    }

}
