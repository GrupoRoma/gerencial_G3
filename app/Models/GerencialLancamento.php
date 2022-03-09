<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class GerencialLancamento extends Model
{
    use HasFactory;

    protected   $table        = 'gerencialLancamentos';
    protected   $guarded      = ['id', 'idUsuario', 'idTipoLancamento', 'idLancamentoOrigem'];
    protected   $getColumns   = [];
    protected   $historicoGravaLancamento;
    protected   $reportCentrosCusto;

    public   $comparativoCentroCusto= FALSE;
    
    public $viewTitle       = 'Lançamentos Gerenciais';
    public $viewSubTitle;
   
    public $columnList      = [ 'anoLancamento', 'mesLancamento', 'codigoContaContabil', 'idEmpresa', 'centroCusto', 'idContaGerencial',
                                'creditoDebito', 'valorLancamento', 'historicoLancamento'];
    public $columnsGrid     = [ 'anoLancamento', 'mesLancamento', 'codigoContaContabil', 'idEmpresa', 'centroCusto', 'idContaGerencial',
                                'creditoDebito', 'valorLancamento', 'historicoLancamento', 'numeroLote'];
    public $columnAlias     = ['anoLancamento'          => 'Ano de Referência', 'mesLancamento'         => 'Mês de Referência',
                                'codigoContaContabil'   => 'Conta Contábil',    'idEmpresa'             => 'Empresa',
                                'centroCusto'           => 'Centro de Custo',   'idContaGerencial'      => 'Conta Gerencial',
                                'creditoDebito'         => 'Crédito / Débito',  'valorLancamento'       => 'Valor',
                                'historicoLancamento'   => 'Histórico',         'numeroLote'            => 'Número do Lote'];
    public $columnValue     = ['mesLancamento'        => ['1'  => 'Janeiro', '2'  => 'Fevereiro', '3'  => 'Março', 
                                                          '4'  => 'Abril', '5'  => 'Maio', '6'  => 'Junho',
                                                          '7'  => 'Julho', '8'  => 'Agosto', '9'  => 'Setembro',
                                                          '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'],
                               'creditoDebito'        => ['CRD' => 'Crédito', 'DEB' => 'Débito']];
    public $customType      = ['creditoDebito'        => ['type'      => 'radio',
                                                          'values'    => ['CRD' => 'Crédito', 'DEB' => 'Débito']]
                              ];
    public $rules           = ['anoLancamento'          => 'required', 'mesLancamento'         => 'required|min:1|max:12', 
                                'codigoContaContabil'   => 'nullable', 'idEmpresa'             => 'required', 
                                'centroCusto'           => 'required', 'idContaGerencial'      => 'required',
                                'creditoDebito'         => 'required', 'valorLancamento'       => 'required',
                                'historicoLancamento'   => 'nullable', 'numeroLote'            => 'nullable'];
    public $rulesMessage    = [ 'anoLancamento'         => 'ANO DE REFERÊNCIA: Obrigatório',
                                'mesLancamento'         => 'MÊS DE REFERÊNCIA: Obrigatório',
                                'idEmpresa'             => 'EMPRESA: Obrigatório',
                                'centroCusto'           => 'CENTRO DE CUSTO: Obrigatório',
                                'idContaGerencial'      => 'CONTA GERENCIAL: Obrigatório',
                                'creditoDebito'         => 'CRÉDITO / DÉBTIO: Obrigatório',
                                'valorLancamento'       => 'VALOR: Obrigatório'
                              ];   
    public $errors          = [];
    
    /**
     * Retona a Conta Gerencial associada
     */
    public function gerencialContaGerencial() {
        return $this->hasOne('App\Models\GerencialContaGerencial');
    }

    /**
     * Retona a Empresa associada
     */
    public function gerencialEmpresas() {
        return $this->hasOne('App\Models\GerencialEmpresas');
    }

    /**
     * Retona o Centro de Custo associado
     */
    public function gerencialCentroCusto() {
        return $this->hasOne('App\Models\GerencialCentroCusto');
    }

    /**
     * Retona o Usuário associado
     */
    public function users() {
        return $this->hasOne('App\Models\User');
    }

    /**
     * Retona o Tipo de Lançamento associado
     */
    public function gerencialTipoLancamento() {
        return $this->hasOne('App\Models\GerencialTipoLancamento');
    }

    /**
     * Retorna os dados para visualização
     */
    public function vd_gerencialTipoLancamento($id) {
        $viewData = GerencialTipoLancamento::where('id', $id)->get();

        foreach ($viewData as $row => $data) {
            return $data->descricaoTipoLancamento;
        }
    }

    public function vd_gerencialContaGerencial($id) {
        $viewData = GerencialContaGerencial::where('id', $id)->get();

        foreach ($viewData as $row => $data) {
            return $data->descricaoContaGerencial;
        }
    }

    public function vd_gerencialEmpresas($id) {
        $viewData = GerencialEmpresas::where('id', $id)->get();

        foreach ($viewData as $row => $data) {
            return $data->nomeAlternativo;
        }
    }

    public function vd_gerencialCentroCusto($id) {
        $viewData = GerencialCentroCusto::where('id', $id)->get();

        foreach ($viewData as $row => $data) {
            return $data->descricaoCentroCusto;
        }
    }

    public function fk_gerencialEmpresas($columnValueName = 'id') {
        $fkData = GerencialEmpresas::orderBy('nomeAlternativo')->get();

        $formValues = [];
        foreach($fkData as $row => $data) {
            $formValues[] = [$data->{$columnValueName}, $data->nomeAlternativo];
        }

        return ['options' => $formValues, 'type' => '']; 
    }

    public function fk_gerencialCentroCusto($columnValueName = 'id') {
        $fkData = GerencialCentroCusto::orderBy('siglaCentroCusto')->get();

        $formValues = [];
        foreach($fkData as $row => $data) {
            $formValues[] = [$data->{$columnValueName}, $data->descricaoCentroCusto.' ('.$data->siglaCentroCusto.')'];
        }

        return ['options' => $formValues, 'type' => '']; 
    }

    public function fk_gerencialContaGerencial($columnValueName = 'id') {
        $fkData = GerencialContaGerencial::orderBy('codigoContaGerencial')->get();

        $formValues = [];
        foreach($fkData as $row => $data) {
            $formValues[] = [$data->{$columnValueName}, $data->codigoContaGerencial.'.'.$data->descricaoContaGerencial];
        }

        return ['options' => $formValues, 'type' => '']; 
    }

    public function fk_gerencialTipoLancamento($columnValueName = 'id') {
        $fkData = GerencialTipoLancamento::orderBy('descricaoTipoLancamento')->get();

        $formValues = [];
        foreach($fkData as $row => $data) {
            $formValues[] = [$data->{$columnValueName}, $data->descricaoTipoLancamento];
        }

        return ['options' => $formValues, 'type' => '']; 
    }

    public function custom_codigoContaContabil($values = NULL, $multi = FALSE) {
        $customData = DB::select("SELECT    codigoContaContabilERP  = PlanoConta.PlanoConta_Codigo, 
                                            codigoContaContabil     = G3_gerencialContaContabil.contaContabil,
                                            descricaoConta          = PlanoConta.PlanoConta_Descricao 
                                  FROM  G3_gerencialContaContabil
                                  JOIN  GrupoRoma_DealernetWF..PlanoConta (nolock) ON PlanoConta.PlanoConta_ID collate SQL_Latin1_General_CP1_CI_AS = G3_GerencialContaContabil.contaContabil
                                  WHERE PlanoConta.Estrutura_Codigo = 5 
                                  AND   PlanoConta.PlanoConta_TipoContabil in ('RES', 'DSP', 'REC', 'ATV') 
                                  ORDER BY contaContabil");

        $htmlForm = "<select class='form-control' name='codigoContaContabil".($multi ? '[]\' multiple' : '\'')." id='codigoContaContabil'>";
        if (!$multi) $htmlForm .= "<option>--- selecione uma Conta Contábil ---</option>";

        $values = explode(',', $values);
        foreach ($customData as $row => $data) {
            $htmlForm .= "<option value='".$data->codigoContaContabilERP."' ".(in_array($data->codigoContaContabilERP, $values) ? 'selected' : '').">".
                            $data->codigoContaContabil.' '.$data->descricaoConta.
                         "</option>";
        }
        $htmlForm .= "</select>";

        return $htmlForm.($multi ? "<small class='form-text text-muted'><b>CTRL+Click</b> para selecionar mais de uma opção</small>" : "");
    }   //-- custom_codigoContaContabil --//

    /**
     *  Retorna a lista de empresas
     * 
     */
    public function custom_idEmpresa($values = NULL, $multi = FALSE) {
        $customData = GerencialEmpresas::where('empresaAtiva', 'S')
                                       ->orderBy('nomeAlternativo')
                                       ->get();

        $htmlForm = "<select class='form-control' name='idEmpresa".($multi ? '[]\' multiple' : '\'')." id='idEmpresa'>";
        if (!$multi) $htmlForm .= "<option></option>";

        $values = explode(',', $values);
        foreach ($customData as $row => $data) {
            $htmlForm .= "<option value='".$data->id."' ".(in_array($data->id, $values) ? 'selected' : '').">".$data->nomeAlternativo."</option>";
        }
        $htmlForm .= "</select>";

        return $htmlForm.($multi ? "<small class='form-text text-muted'><b>CTRL+Click</b> para selecionar mais de uma opção</small>" : "");
    }   //-- custom_idEmpresa --//

    public function custom_mesLancamento($values = NULL, $multi = FALSE) {
        $htmlForm = "<select class='form-control' name='mesLancamento' id='mesLancamento'>";
        if (!$multi) $htmlForm .= "<option></option>";
        
        $htmlForm .= "<option value='1' ".($values == '1' ? 'selected' : '').">Janeiro</option>
                      <option value='2' ".($values == '2' ? 'selected' : '').">Fevereiro</option>
                      <option value='3' ".($values == '3' ? 'selected' : '').">Março</option>
                      <option value='4' ".($values == '4' ? 'selected' : '').">Abril</option>
                      <option value='5' ".($values == '5' ? 'selected' : '').">Maio</option>
                      <option value='6' ".($values == '6' ? 'selected' : '').">Junho</option>
                      <option value='7' ".($values == '7' ? 'selected' : '').">Julho</option>
                      <option value='8' ".($values == '8' ? 'selected' : '').">Agosto</option>
                      <option value='9' ".($values == '9' ? 'selected' : '').">Setembro</option>
                      <option value='10' ".($values == '10' ? 'selected' : '').">Outubro</option>
                      <option value='11' ".($values == '11' ? 'selected' : '').">Novembro</option>
                      <option value='12' ".($values == '12' ? 'selected' : '').">Dezembro</option>";
        $htmlForm .= "</select>";

        return $htmlForm.($multi ? "<small class='form-text text-muted'><b>CTRL+Click</b> para selecionar mais de uma opção</small>" : "");
    }   //-- custom_mesLancamento --//

    /**
     *  Inclui uma lista de colunas a serem adicionadas à consulta de lancçamentos gerenciais
     * 
     *  @param  array       Lista de campos     [label1 => campo1, label2 => campo2, label3 => campo3, ...]
     * 
     */
    public function addGetColumns($columns) {
        $this->getColumns = $columns;
    }

    /**
     *  getLancamentos
     *  Retorna todos os lançamentos registrados conforme os critérios informados
     * 
     *  @param  object  lista de critérios para seleção dos lançamentos (jSON)
     * 
     *  @return object  {["column": "nome_da_coluna", "operator": "simbolo_operacao-ex: =, <>, ...", "value": "valor_filtro"], [...]}
     * 
     */
    public function getLancamentos($params = NULL) {
        $filter = '';

        if (!empty($params)) {
            $params = json_decode($params);

            foreach ($params as $key => $condition) {
                $filter .= "\nAND   ".$condition->column." ";
                $filter .= (isset($condition->operator) ? $condition->operator : '=');
                
                if (is_array($condition->value) || is_object($condition->value)) {
                    $filter .= ' (';
                    foreach ($condition->value as $inValue) {
                        $filter .= $inValue.',';
                    }
                    $filter = substr($filter,0 , -1).') ';
                }
                else $filter .= "'".$condition->value."'";
            }
        }

        $addColumns     = NULL;
        $groupColumns   = NULL;
        if (isset($this->getColumns) && !empty($this->getColumns)) {
            foreach ($this->getColumns as $labelName => $columnName) {
                $addColumns     .= $labelName.' = '.$columnName.',';
                $groupColumns   .= $columnName.',';
            }
        }

        $lancamentos = DB::select("SELECT   anoLancamento			= G3_gerencialLancamentos.anoLancamento,
                                            mesLancamento			= G3_gerencialLancamentos.mesLancamento,
                                            mesAnoLancamento		= CONVERT(VARCHAR, G3_gerencialLancamentos.mesLancamento)+'/'+CONVERT(VARCHAR,G3_gerencialLancamentos.anoLancamento),
                                            numeroContaGerencial	= G3_gerencialContaGerencial.codigoContaGerencial,
                                            contaGerencial			= G3_gerencialContaGerencial.descricaoContaGerencial,
                                            --contaContabil			= G3_gerencialLancamentos.codigoContaContabil,
                                            nomeEmpresa				= G3_gerencialEmpresas.nomeAlternativo,
                                            nomeRegional			= G3_gerencialRegional.descricaoRegional,
                                            codigoGrupoConta		= G3_gerencialGrupoConta.codigoGrupoConta,
                                            grupoConta				= G3_gerencialGrupoConta.descricaoGrupoConta,
                                            subGrupoConta			= G3_gerencialSubGrupoConta.descricaoSubGrupoConta,
                                            codigoCentroCusto       = G3_gerencialLancamentos.centroCusto,
                                            siglaCentroCusto		= G3_gerencialCentroCusto.siglaCentroCusto,
                                            centroCusto				= G3_gerencialCentroCusto.descricaoCentroCusto,
                                            valorLancamento			= SUM(G3_gerencialLancamentos.valorLancamento),

                                            $addColumns

                                            codigoContaGerencial	= G3_gerencialContaGerencial.id,
                                            codigoContaContabilERP	= G3_gerencialContaContabil.codigoContaContabilERP,
                                            codigoEmpresa			= G3_gerencialLancamentos.idEmpresa,
                                            codigoEmpresaERP        = G3_gerencialEmpresas.codigoEmpresaERP,
                                            codigoRegional          = G3_gerencialEmpresas.codigoRegional,
                                            importaSaldoInvertido   = G3_gerencialContaContabil.saldoInvertido
                                    FROM GAMA..G3_gerencialLancamentos							(nolock)
                                    JOIN GAMA..G3_gerencialEmpresas								(nolock) ON G3_gerencialEmpresas.id					= G3_gerencialLancamentos.idEmpresa
                                    JOIN GAMA..G3_gerencialRegional								(nolock) ON G3_gerencialRegional.id					= G3_gerencialEmpresas.codigoRegional
                                    JOIN GAMA..G3_gerencialCentroCusto							(nolock) ON G3_gerencialCentroCusto.id				= G3_gerencialLancamentos.centroCusto
                                    JOIN GAMA..G3_gerencialContaGerencial						(nolock) ON G3_gerencialContaGerencial.id			= G3_gerencialLancamentos.idContaGerencial
                                    JOIN GAMA..G3_gerencialGrupoConta							(nolock) ON G3_gerencialGrupoConta.id				= G3_gerencialContaGerencial.idGrupoConta
                                    JOIN GAMA..G3_gerencialSubGrupoConta						(nolock) ON G3_gerencialSubGrupoConta.id			= G3_gerencialGrupoConta.idSubGrupoConta
                                    JOIN GAMA..G3_gerencialTipoLancamento						(nolock) ON G3_gerencialTipoLancamento.id			= G3_gerencialLancamentos.idTipoLancamento
                                    JOIN GAMA..G3_gerencialUsuarios								(nolock) ON G3_gerencialUsuarios.id					= G3_gerencialLancamentos.idUsuario
                                    LEFT JOIN GAMA..users									    (nolock) ON users.id								= G3_gerencialLancamentos.idUsuario
                                    --JOIN GAMA..G3_gerencialContaContabil						(nolock) ON G3_gerencialContaContabil.contaContabil	= G3_gerencialLancamentos.codigoContaContabil
                                    LEFT JOIN GAMA..G3_gerencialContaContabil					(nolock) ON CONVERT(nvarchar, G3_gerencialContaContabil.codigoContaContabilERP)	= G3_gerencialLancamentos.codigoContaContabil
                                                                                                        AND G3_gerencialContaContabil.idContaGerencial = G3_gerencialLancamentos.idContaGerencial
                                    LEFT JOIN GAMA..G3_gerencialLancamentos lancamentoOrigem	(nolock) ON lancamentoOrigem.id						= G3_gerencialLancamentos.idLancamentoOrigem
                                    
                                    WHERE	1 = 1
                                    AND     G3_gerencialEmpresas.empresaAtiva           = 'S'
                                    AND     G3_gerencialCentroCusto.centroCustoAtivo    = 'S'
                                    
                                    $filter
                                    
                                    GROUP BY G3_gerencialLancamentos.anoLancamento, G3_gerencialLancamentos.mesLancamento, G3_gerencialContaGerencial.codigoContaGerencial, G3_gerencialContaGerencial.descricaoContaGerencial,
                                             /*G3_gerencialLancamentos.codigoContaContabil,*/ G3_gerencialEmpresas.nomeAlternativo, G3_gerencialRegional.descricaoRegional, G3_gerencialGrupoConta.ordemExibicao, 
                                             G3_gerencialGrupoConta.codigoGrupoConta, G3_gerencialGrupoConta.descricaoGrupoConta, G3_gerencialSubGrupoConta.descricaoSubGrupoConta, G3_gerencialLancamentos.centroCusto,
                                             G3_gerencialCentroCusto.siglaCentroCusto, G3_gerencialCentroCusto.descricaoCentroCusto, 
                                             
                                             $groupColumns
                                             
                                             G3_gerencialContaGerencial.id, G3_gerencialContaContabil.codigoContaContabilERP,
                                             G3_gerencialLancamentos.idEmpresa, G3_gerencialEmpresas.codigoEmpresaERP, G3_gerencialEmpresas.codigoRegional, G3_gerencialContaContabil.saldoInvertido
                                    
                                    ORDER BY nomeRegional, nomeEmpresa, G3_gerencialGrupoConta.ordemExibicao, anoLancamento, mesLancamento, codigoGrupoConta, subGrupoConta, codigoContaGerencial, centroCusto, codigoContaContabilERP");
        return $lancamentos;

    }   //-- getLancamentos --//

/**
     *  getLancamentosRegional
     *  Retorna todos os lançamentos registrados conforme os critérios informados,
     *  agrupando dados e valores por regional
     * 
     *  @param  object  lista de critérios para seleção dos lançamentos (jSON)
     * 
     *  @return object  {["column": "nome_da_coluna", "operator": "simbolo_operacao-ex: =, <>, ...", "value": "valor_filtro"], [...]}
     * 
     */
    public function getLancamentosRegional($params = NULL) {
        $filter = '';

        if (!empty($params)) {
            $params = json_decode($params);

            foreach ($params as $key => $condition) {
                $filter .= "\nAND   ".$condition->column." ";
                $filter .= (isset($condition->operator) ? $condition->operator : '=');
                
                if (is_array($condition->value) || is_object($condition->value)) {
                    $filter .= ' (';
                    foreach ($condition->value as $inValue) {
                        $filter .= $inValue.',';
                    }
                    $filter = substr($filter,0 , -1).') ';
                }
                else $filter .= "'".$condition->value."'";
            }
        }

        $addColumns     = NULL;
        $groupColumns   = NULL;
        if (isset($this->getColumns) && !empty($this->getColumns)) {
            foreach ($this->getColumns as $labelName => $columnName) {
                $addColumns     .= $labelName.' = '.$columnName.',';
                $groupColumns   .= $columnName.',';
            }
        }

        $lancamentos = DB::select("SELECT   anoLancamento			= G3_gerencialLancamentos.anoLancamento,
                                            mesLancamento			= G3_gerencialLancamentos.mesLancamento,
                                            mesAnoLancamento		= CONVERT(VARCHAR, G3_gerencialLancamentos.mesLancamento)+'/'+CONVERT(VARCHAR,G3_gerencialLancamentos.anoLancamento),
                                            numeroContaGerencial	= G3_gerencialContaGerencial.codigoContaGerencial,
                                            contaGerencial			= G3_gerencialContaGerencial.descricaoContaGerencial,
                                            nomeEmpresa				= G3_gerencialRegional.descricaoRegional,
                                            nomeRegional			= G3_gerencialRegional.descricaoRegional,
                                            codigoGrupoConta		= G3_gerencialGrupoConta.codigoGrupoConta,
                                            grupoConta				= G3_gerencialGrupoConta.descricaoGrupoConta,
                                            subGrupoConta			= G3_gerencialSubGrupoConta.descricaoSubGrupoConta,
                                            codigoCentroCusto       = G3_gerencialLancamentos.centroCusto,
                                            siglaCentroCusto		= G3_gerencialCentroCusto.siglaCentroCusto,
                                            centroCusto				= G3_gerencialCentroCusto.descricaoCentroCusto,
                                            valorLancamento			= SUM(G3_gerencialLancamentos.valorLancamento),

                                            $addColumns

                                            codigoContaGerencial	= G3_gerencialContaGerencial.id,
                                            codigoRegional          = G3_gerencialEmpresas.codigoRegional
                                    FROM GAMA..G3_gerencialLancamentos							(nolock)
                                    JOIN GAMA..G3_gerencialEmpresas								(nolock) ON G3_gerencialEmpresas.id					= G3_gerencialLancamentos.idEmpresa
                                    JOIN GAMA..G3_gerencialRegional								(nolock) ON G3_gerencialRegional.id					= G3_gerencialEmpresas.codigoRegional
                                    JOIN GAMA..G3_gerencialCentroCusto							(nolock) ON G3_gerencialCentroCusto.id				= G3_gerencialLancamentos.centroCusto
                                    JOIN GAMA..G3_gerencialContaGerencial						(nolock) ON G3_gerencialContaGerencial.id			= G3_gerencialLancamentos.idContaGerencial
                                    JOIN GAMA..G3_gerencialGrupoConta							(nolock) ON G3_gerencialGrupoConta.id				= G3_gerencialContaGerencial.idGrupoConta
                                    JOIN GAMA..G3_gerencialSubGrupoConta						(nolock) ON G3_gerencialSubGrupoConta.id			= G3_gerencialGrupoConta.idSubGrupoConta
                                    JOIN GAMA..G3_gerencialTipoLancamento						(nolock) ON G3_gerencialTipoLancamento.id			= G3_gerencialLancamentos.idTipoLancamento
                                    JOIN GAMA..G3_gerencialUsuarios								(nolock) ON G3_gerencialUsuarios.id					= G3_gerencialLancamentos.idUsuario
                                    LEFT JOIN GAMA..users						    			(nolock) ON users.id								= G3_gerencialLancamentos.idUsuario
                                    LEFT JOIN GAMA..G3_gerencialContaContabil					(nolock) ON CONVERT(nvarchar, G3_gerencialContaContabil.codigoContaContabilERP)	= G3_gerencialLancamentos.codigoContaContabil
                                                                                                        AND G3_gerencialContaContabil.idContaGerencial = G3_gerencialLancamentos.idContaGerencial
                                    LEFT JOIN GAMA..G3_gerencialLancamentos lancamentoOrigem	(nolock) ON lancamentoOrigem.id						= G3_gerencialLancamentos.idLancamentoOrigem
                                    
                                    WHERE	1 = 1
                                    AND     G3_gerencialEmpresas.empresaAtiva           = 'S'
                                    AND     G3_gerencialCentroCusto.centroCustoAtivo    = 'S'
                                    
                                    $filter
                                    
                                    GROUP BY G3_gerencialLancamentos.anoLancamento, G3_gerencialLancamentos.mesLancamento, G3_gerencialContaGerencial.codigoContaGerencial, G3_gerencialContaGerencial.descricaoContaGerencial,
                                             G3_gerencialRegional.descricaoRegional, G3_gerencialGrupoConta.ordemExibicao, G3_gerencialGrupoConta.codigoGrupoConta, G3_gerencialGrupoConta.descricaoGrupoConta,
                                             G3_gerencialSubGrupoConta.descricaoSubGrupoConta, G3_gerencialLancamentos.centroCusto, G3_gerencialCentroCusto.siglaCentroCusto, G3_gerencialCentroCusto.descricaoCentroCusto, 

                                             $groupColumns
                                             
                                             G3_gerencialContaGerencial.id, G3_gerencialEmpresas.codigoRegional
                                    
                                    ORDER BY nomeRegional, G3_gerencialGrupoConta.ordemExibicao, anoLancamento, mesLancamento, codigoGrupoConta, subGrupoConta, codigoContaGerencial, centroCusto");
        return $lancamentos;

    }   //-- getLancamentosRegional --//

    /**
     *  Retorna todos os lançamentos registrados na(s) empresa(s) e centro(s) de custo de destino
     *  para cálculo do rateio - PARÂMETRO DE RATEIO
     * 
     *  @param  string  mes de referência para os lançamentos gerenciais
     *  @param  integer ano de referência para os lancamentos gerenciais     * 
     *  @param  string  lista de códigos para as empresas de destino (1,2,3,4,5, ...)
     *  @param  string  lista de códigos de centro de custo de destino  (1,2,3,4,5, ...)
     * 
     *  @return object  Data base result data
     * 
     */
    public function getLancamentosRateio(String $mesLancamento, Int $anoLancamento, String $empresasDestino, String $centroCustosDestino) {
        
        if (empty($mesLancamento)       && $mesLancamento       == '')  return [];
        if (empty($anoLancamento)       && $anoLancamento       == '')  return [];
        if (empty($empresasDestino)     && $empresasDestino     == '')  return [];
        if (empty($centroCustosDestino) && $centroCustosDestino == '')  return [];

        $lancamentos = DB::select("SELECT   anoLancamento			= G3_gerencialLancamentos.anoLancamento,
                                            mesLancamento			= G3_gerencialLancamentos.mesLancamento,
                                            codigoEmpresa			= G3_gerencialLancamentos.idEmpresa,
                                            codigoCentroCusto       = G3_gerencialLancamentos.centroCusto,
                                            valorLancamento			= SUM(G3_gerencialLancamentos.valorLancamento)
                                    FROM GAMA..G3_gerencialLancamentos							(nolock)
                                    JOIN GAMA..G3_gerencialEmpresas								(nolock) ON G3_gerencialEmpresas.id					= G3_gerencialLancamentos.idEmpresa
                                    JOIN GAMA..G3_gerencialCentroCusto							(nolock) ON G3_gerencialCentroCusto.id				= G3_gerencialLancamentos.centroCusto
                                    
                                    WHERE	G3_gerencialLancamentos.mesLancamento       = $mesLancamento
                                    AND     G3_gerencialLancamentos.anoLancamento       = $anoLancamento
                                    AND     G3_gerencialEmpresas.empresaAtiva           = 'S'
                                    AND     G3_gerencialCentroCusto.centroCustoAtivo    = 'S'
                                    AND     G3_gerencialLancamentos.idEmpresa           IN ($empresasDestino) 
                                    AND     G3_gerencialLancamentos.centroCusto         IN ($centroCustosDestino)
                                    
                                    GROUP BY G3_gerencialLancamentos.anoLancamento, 
                                             G3_gerencialLancamentos.mesLancamento, 
                                             G3_gerencialLancamentos.centroCusto, 
                                             G3_gerencialLancamentos.idEmpresa");
        return $lancamentos;

    }   //-- getLancamentosRateio --//


     /**
     *  lancamentoExists
     *  Verifica se existem lançamentos registrados conforme os critérios informados
     * 
     *  @param  array  [["column": "nome_da_coluna", "operator": "simbolo_operacao-ex: =, <>, ...", "value": "valor_filtro"], [...]]
     * 
     *  @return boolean     TRUE: Existem lançamentos | FALSE: Não existem lançamentos
     * 
     */
    public function lancamentoExists($params = NULL) {
        $filter = '';

        if (count($params) > 0 ) {
            foreach ($params as $key => $condition) {
                $filter .= "\nAND   ".$condition['column']." ";
                $filter .= (isset($condition['operator']) ? $condition['operator'] : '=');
                
                if (is_array($condition['value']) || is_object($condition['value'])) {
                    $filter .= ' (';
                    foreach ($condition['value'] as $inValue) {
                        $filter .= $inValue.',';
                    }
                    $filter = substr($filter,0 , -1).') ';
                }
                else $filter .= "'".$condition['value']."'";
            }

            $lancamentos = DB::select("SELECT   *
                                        FROM GAMA..G3_gerencialLancamentos  (nolock)
                                        WHERE	1 = 1                                    
                                        $filter");

            if (count($lancamentos) == 0)   return FALSE;
            else                            return TRUE;
        }
        else return TRUE;

    }   //-- lancamentoExists --//    

    /**
     *  gravaLancamento
     *  @todo   Registra os dados do lançamento na tabela gerencialLancamento
     * 
     *  @param  mixed       (array | object) dados do lancamento [0][$dados[]], [1][$dados[]], [n][$dados[]]
     *  
     *  @example    gravaLancamento(['mesLancamento', 
     *                               'anoLancamento', 
     *                               'idEmpresa', 
     *                               'centroCusto', 
     *                               'idContaGerencial',
     *                               'creditoDebito', 
     *                               'valorLancamento',
     *                               'historicoLancamento', 
     *                               'idTipoLancamento',
     *                               'codigoContaContabil'])
     * 
     *  @return boolean 
     * 
     */
    public function gravaLancamento($dadosLancamento) {
        $this->errors   = [];

        if (!is_object($dadosLancamento))   settype($dadosLancamento, "array");

        if (empty($dadosLancamento)) {
            $this->errors[]     = ['errorTitle' => 'GRAVAÇÃO DOS LANÇAMENTOS -- CONTÁBEIS',
                                    'error'     => 'Não foram informados os dados para registro do lançamento gerencial.'];
            return FALSE;
        }

        $dataSave   = NULL;
        $saved      = 0;
        $rows       = 0;

        foreach ($dadosLancamento as $row => $dadosRegistro) {
            settype($dadosRegistro, 'object');

            if (!isset($dadosRegistro->mesLancamento)  || empty($dadosRegistro->mesLancamento))
                $this->errors[] = ['errorTitle' => "<small>[log]</small> GRAVA LANÇAMENTO [Período]", 'error' => 'Mês não informado'];
            if (!isset($dadosRegistro->anoLancamento)  || empty($dadosRegistro->anoLancamento))
                $this->errors[] = ['errorTitle' => "<small>[log]</small> GRAVA LANÇAMENTO [Período]", 'error' => 'Ano não informado'];
            if (!isset($dadosRegistro->idEmpresa)  || empty($dadosRegistro->idEmpresa))
                $this->errors[] = ['errorTitle' => "<small>[log]</small> GRAVA LANÇAMENTO [Empresa]", 'error' => 'ID da empresa não informado'];
            if (!isset($dadosRegistro->centroCusto)  || empty($dadosRegistro->centroCusto))
                $this->errors[] = ['errorTitle' => "<small>[log]</small> GRAVA LANÇAMENTO [Centro de Custo]", 'error' => 'Centro de Custo não informado'];
            if (!isset($dadosRegistro->idContaGerencial)  || empty($dadosRegistro->idContaGerencial))
                $this->errors[] = ['errorTitle' => "<small>[log]</small> GRAVA LANÇAMENTO [Conta Gerencial]", 'error' => 'Conta Gerencial não informada'];
            if (!isset($dadosRegistro->creditoDebito)  || empty($dadosRegistro->creditoDebito))
                $this->errors[] = ['errorTitle' => "<small>[log]</small> GRAVA LANÇAMENTO [Crédito/Débito]", 'error' => 'Tipo de Lançamento CRÉDITO ou DÉBITO não informado'];
            if (!isset($dadosRegistro->historicoLancamento)  || empty($dadosRegistro->historicoLancamento))
                $this->errors[] = ['errorTitle' => "<small>[log]</small> GRAVA LANÇAMENTO [Histórico]", 'error' => 'Histórico não informado'];
            if (!isset($dadosRegistro->idTipoLancamento)  || empty($dadosRegistro->idTipoLancamento))
                $this->errors[] = ['errorTitle' => "<small>[log]</small> GRAVA LANÇAMENTO [Tipo de Lançamento]", 'error' => 'Tipo de lançamento não informado'];

            // Erros encontrados
            if (count($this->errors) > 0) {
                return FALSE;
            }   

            // Verifica os outros campos da tabela
            $otherFields    = NULL;
            $otherValues    = NULL;
            if (isset($dadosRegistro->idLancamentoOrigem)) {
                $otherFields .= ', idLancamentoOrigem';
                $otherValues .= ", '".$dadosRegistro->idLancamentoOrigem."'";
            }

            if (isset($dadosRegistro->numeroLote)) {
                $otherFields .= ', numeroLote';
                $otherValues .= ", '".$dadosRegistro->numeroLote."'";
            }

            if (isset($dadosRegistro->numeroDocumento)) {
                $otherFields .= ', numeroDocumento';
                $otherValues .= ", '".$dadosRegistro->numeroDocumento."'";
            }

            $this->historicoGravaLancamento = $dadosRegistro->historicoLancamento;

            $codigoEmpresa      = $this->checkTransferenciaEmpresa($dadosRegistro->idEmpresa, $dadosRegistro->centroCusto);
            $codigoCentroCusto  = $this->checkTransferenciaCentroCusto($dadosRegistro->centroCusto, $dadosRegistro->idEmpresa);

            // VERIFICA SE O SALDO DEVE SER REGISTRADO COM VALOR INVERTIDO
            // Se for especificada uma conta contabil e a conta contabil não estiver nula
            if (isset($dadosRegistro->codigoContaContabil) 
                && !empty($dadosRegistro->codigoContaContabil)
                && $this->saldoInvertido($dadosRegistro))  {
                $valorLancamento    = $dadosRegistro->valorLancamento * -1;
                if ($valorLancamento > 0 )  $creditoDebito  = 'CRD';
                else                        $creditoDebito  = 'DEB';
            }
            else  {
                $valorLancamento    = $dadosRegistro->valorLancamento;
                $creditoDebito      = $dadosRegistro->creditoDebito;
            }

            $dataSave     = "(".$dadosRegistro->mesLancamento.",
                                ".$dadosRegistro->anoLancamento.",
                                ".$codigoEmpresa.",
                                ".$codigoCentroCusto.",
                                ".$dadosRegistro->idContaGerencial.",
                                '".$creditoDebito."',
                                ".$valorLancamento.",
                                '".$this->historicoGravaLancamento."',
                                ".$dadosRegistro->idTipoLancamento.",
                                ".(isset($dadosRegistro->codigoContaContabil) ? $dadosRegistro->codigoContaContabil : "NULL").",
                                ".session('userID').",  
                                '".date('Y-m-d H:i:s')."',
                                '".date('Y-m-d H:i:s')."'".$otherValues.")";
            $saved ++;
            $rows ++;

            DB::insert("INSERT INTO GAMA..G3_gerencialLancamentos (mesLancamento, 
                                                                anoLancamento,
                                                                idEmpresa,
                                                                centroCusto,
                                                                idContaGerencial,
                                                                creditoDebito,
                                                                valorLancamento,
                                                                historicoLancamento,
                                                                idTipoLancamento,
                                                                codigoContaContabil,
                                                                idUsuario,
                                                                created_at,
                                                                updated_at".$otherFields.")
                                        VALUES ".$dataSave);
            $row = 0;
        }

        return TRUE;

    }   //-- gravaLancamento --//

  /**
     *  deleteLancamentosGerenciais
     *  Verifica se existem lancamentos gerenciais para as condições informadas
     *  e deleta para evitar a duplicidade de dados
     * 
     *  @param array    criterios   [["fieldName" => fieldName, "fieldComparison" => [=,<>,>=,<=,...], "values" => values, "andOr": 'AND [default]']]
     * 
     *  @example    deleteLancamentosContabeis([["fieldName" => fieldName, "fieldComparison" => [=,<>,>=,<=,...], "values" => values, "andOr": 'AND [default]']])
     * 
     */
    public function deleteLancamentosGerenciais(array $criterios) {

        $deleteData = DB::table('gerencialLancamentos')
                        ->where(function($query) {
                            /* valida  */
                            if (isset($this->mesAtivo) && !empty($this->mesAtivo)) {
                                $query->where('gerencialLancamentos.mesLancamento', $this->mesAtivo);
                                return FALSE;
                            }
                            if (isset($this->anoAtivo) && !empty($this->anoAtivo)) {
                                $query->where('gerencialLancamentos.anoLancamento', $this->anoAtivo);
                                return FALSE;
                            }                    
                        })
                        ->where(function($query) use ($criterios) {
                            foreach ($criterios as $index => $sqlWhere) {
                                $condition = $sqlWhere['fieldName']." ".($sqlWhere['fieldComparison'] ?? '=')." ".
                                                (is_array($sqlWhere['values']) ? "('".implode("','", $sqlWhere['values'])."')" : $sqlWhere['values']);

                                if (isset($sqlWhere['andOr']) && strtoupper($sqlWhere['andOr']) == 'OR') {
                                    $query->orWhereRaw($condition);
                                }
                                else {
                                    $query->whereRaw($condition);
                                }
                            }

                        })
                        ->delete();
        return $deleteData;
    }   //-- deleteLancamentosGerenciais

    /**
     *  Verifica se existe parâmetro para transferência dos valores de uma empresa para outra
     * 
     *  @param  int     Codigo da empresa de Origem
     *  @param  int     Código do centro de Custo (Nulo)
     * 
     *  @return int     Código da empresa para registro do lançamento gerencial
     */
    public function checkTransferenciaEmpresa($codigoEmpresa, $codigoCentroCusto = NULL) {
        // TRANSFERÊNCIA DE EMPRESAS - GERAL
        //  Retorna a empresa para a qual devem ser transferidos todos os lançamentos
        $dbEmpresa      = GerencialParametroEmpresa::where('idEmpresaOrigem', $codigoEmpresa)
                                                    ->whereNull('idCentroCusto')
                                                    ->where('parametroAtivo', 'S')->get();

        // TRANSFERÊNCIA DE CENTRO DE CUSTO ENTRE EMPRESAS
        //  Retorna a empresa para a qual devem ser transferidos todos os lançamentos
        //  do Centro de custo informado
        $dbEmpresaCentroCusto   = GerencialParametroEmpresa::where('idEmpresaOrigem', $codigoEmpresa)
                                                            ->where('idCentroCusto', $codigoCentroCusto)
                                                            ->where('parametroAtivo', 'S')->get();

        $empresaOrigem  = GerencialEmpresas::find($codigoEmpresa);

        if (count($dbEmpresaCentroCusto) > 0) {
            $empresaDestino  = GerencialEmpresas::find($dbEmpresaCentroCusto[0]->idEmpresaDestino);
            $centroCusto     = GerencialCentroCusto::find($codigoCentroCusto);

            $this->historicoGravaLancamento .= ' [TRANSFERÊNCIA ENTRE EMPRESAS DE '.
                                                 $empresaOrigem->nomeAlternativo.
                                                 ' PARA '.$empresaDestino->nomeAlternativo.' | SOMENTE CENTRO DE CUSTO '.$centroCusto->descricaoCentroCusto.'] ';
            return $dbEmpresaCentroCusto[0]->idEmpresaDestino;
        }
        elseif (count($dbEmpresa) > 0) {
            $empresaDestino  = GerencialEmpresas::find($dbEmpresa[0]->idEmpresaDestino);

            $this->historicoGravaLancamento .= ' [TRANSFERÊNCIA ENTRE EMPRESAS DE '.
                                                 $empresaOrigem->nomeAlternativo.
                                                 ' PARA '.$empresaDestino->nomeAlternativo.'] ';
            return $dbEmpresa[0]->idEmpresaDestino;
        }
        else                    return $codigoEmpresa;
    }

    /**
     *  Verifica se existe parâmetro para transferência dos valores de um centro de custo para outro
     * 
     *  @param  int     Codigo do centro de custo de Origem
     *  @param  int     Código da empresa
     * 
     *  @return int     Código do centro de custo para registro do lançamento gerencial
     */
    public function checkTransferenciaCentroCusto($codigoCentroCusto, $codigoEmpresa = NULL) {
        // Centro Custo GERAL
        //  Retorna o novo centro de custo para transferir os lançamentos
        //  na sua totalidade, INDEPENDENTE DA EMPRESA
        //
        //  CENTRO DE CUSTO DE ORIGEM = $codigoCentroCusto (informado)
        //  EMPRESA                   = NULL (empresa não informada)
        $dbCentroCusto          = GerencialParametroCentroCusto::where('idCentroCustoOrigem', $codigoCentroCusto)
                                                                ->whereNull('idEmpresa')
                                                                ->where('parametroAtivo', 'S')->get();

                                                            // Centro Custo GERAL
        //  Retorna o novo centro de custo para transferir os lançamentos
        //  DA EMPRESA informada
        //
        //  CENTRO DE CUSTO DE ORIGEM = $codigoCentroCusto (informado)
        //  EMPRESA                   = $codigoEmpresa (empresa informada)
        $dbCentroCustoEmpresa   = GerencialParametroCentroCusto::where('idCentroCustoOrigem', $codigoCentroCusto)
                                                                ->where('idEmpresa', $codigoEmpresa)
                                                                ->where('parametroAtivo', 'S')->get();
                                                            
        $centroCustoOrigem  = GerencialCentroCusto::find($codigoCentroCusto);
        $empresaOrigem      = GerencialEmpresas::find($codigoEmpresa);
        
        // SOMENTE CENTRO DE CUSTO
        if (count($dbCentroCusto) > 0) {
            $centroCustoDestino = GerencialCentroCusto::find($dbCentroCusto[0]->idCentroCustoDestino);
            $this->historicoGravaLancamento .= ' [TRANSFERÊNCIA ENTRE CENTROS DE CUSTO DE '.
                                                 $centroCustoOrigem->descricaoCentroCusto.
                                                 ' PARA '.$centroCustoDestino->descricaoCentroCusto.']';
            return $dbCentroCusto[0]->idCentroCustoDestino;
        }
        // EMPRESA E CENTRO DE CUSTO
        elseif (count($dbCentroCustoEmpresa) > 0) {
            $centroCustoDestino = GerencialCentroCusto::find($dbCentroCustoEmpresa[0]->idCentroCustoDestino);
            $this->historicoGravaLancamento .= ' [TRANSFERÊNCIA ENTRE CENTROS DE CUSTO NA EMPRESA '.$empresaOrigem->nomeAlternativo.' DE '.
                                                 $centroCustoOrigem->descricaoCentroCusto.
                                                 ' PARA '.$centroCustoDestino->descricaoCentroCusto.']';
            return $dbCentroCustoEmpresa[0]->idCentroCustoDestino;
        }
        // Retorna o centro de custo original se nenhuma configuração de transferência foi encontrada
        else                    return $codigoCentroCusto;
    }

    /**
     *  saldoInvertido
     *  Verifica se a relação conta gerencial x conta contábil deve ter
     *  o saldo registrado com valor invertido
     * 
     *  @param  Object      dadosLancamento 
     * 
     *  @return Boolean     TRUE: Inverter valor do saldo   | FALSE: não inverter valor do saldo
     */
    public function saldoInvertido(Object $dadosLancamento) 
    {
        $dbData = GerencialContaContabil::where('idContaGerencial', $dadosLancamento->idContaGerencial)
                                        ->where('codigoContaContabilERP', $dadosLancamento->codigoContaContabil)
                                        ->get();
        
        if (count($dbData) > 0 && $dbData[0]->saldoInvertido == 'S')    return TRUE;
        else                                                            return FALSE;
    }

    /**
     *  resultadoLiquido
     *  Calcula o resultado líquido global, por empresa e por centro de custo
     *      
     *      RESULTADO LÍQUIDO (RL)  = SOMA DE TODOS OS VALORES DOS LANÇAMENTOS (SVAL)
     * 
     *  @param  array   periodo [mes = mes, ano = ano]
     * 
     *  @return object  resultadoLiquido
     */
    public function resultadoLiquido($periodo) {
        $dbWhere = [['column' => 'G3_gerencialLancamentos.mesLancamento', 'operator'   => '=', 'value' => $periodo['mes']],
                    ['column' => 'G3_gerencialLancamentos.anoLancamento', 'operator'   => '=', 'value' => $periodo['ano']]];

        $dbData = $this->getLancamentos(json_encode($dbWhere));

        $resultadoLiquido   =   NULL;
        foreach ($dbData as $row => $data) {
            if (!isset($resultadoLiquido['TOTAL']))                                                  $resultadoLiquido['TOTAL']     = 0;
            if (!isset($resultadoLiquido[$data->codigoEmpresaERP]['RL']))                            $resultadoLiquido[$data->codigoEmpresaERP]['RL'] = 0;
            if (!isset($resultadoLiquido[$data->codigoEmpresaERP][$data->siglaCentroCusto]['RL']))   $resultadoLiquido[$data->codigoEmpresaERP][$data->siglaCentroCusto]['RL'] = 0;

            $resultadoLiquido[$data->codigoEmpresaERP]['RL']                             += $data->valorLancamento;
            $resultadoLiquido[$data->codigoEmpresaERP][$data->siglaCentroCusto]['RL']    += $data->valorLancamento;

            $resultadoLiquido['TOTAL']  += $data->valorLancamento;
        }

        return $resultadoLiquido;
    }
    

    /**
     *  getLancamentos
     *  Retorna todos os lançamentos registrados conforme os critérios informados
     * 
     *  @param  object  lista de critérios para seleção dos lançamentos (jSON)
     * 
     *  @return object  {["column": "nome_da_coluna", "operator": "simbolo_operacao-ex: =, <>, ...", "value": "valor_filtro"], [...]}
     * 
     */
    public function getMargemBruta($params = NULL) {
        $filter = '';

        if (!empty($params)) {
            $params = json_decode($params);

            foreach ($params as $key => $condition) {
                $filter .= "\nAND   ".$condition->column." ";
                $filter .= (isset($condition->operator) ? $condition->operator : '=');
                
                if (is_array($condition->value) || is_object($condition->value)) {
                    $filter .= ' (';
                    foreach ($condition->value as $inValue) {
                        $filter .= $inValue.',';
                    }
                    $filter = substr($filter,0 , -1).') ';
                }
                else $filter .= "'".$condition->value."'";
            }
        }

        $dbData     = DB::select("  SELECT  nomeEmpresa		        = G3_gerencialEmpresas.nomeAlternativo,
                                            siglaCentroCusto        = G3_gerencialCentroCusto.siglaCentroCusto,
                                            valorMargemBruta        = SUM(CASE WHEN G3_gerencialSubGrupoConta.baseMargemBruta = 'S'			THEN G3_gerencialLancamentos.valorLancamento ELSE 0 END),
                                            valorReceita            = SUM(CASE WHEN G3_gerencialGrupoConta.receitaCustoMercadoria= 'REC'	THEN G3_gerencialLancamentos.valorLancamento ELSE 0 END),
                                            percentualMargemBruta   = CASE WHEN SUM(CASE WHEN G3_gerencialGrupoConta.receitaCustoMercadoria= 'REC'	THEN G3_gerencialLancamentos.valorLancamento ELSE 0 END) > 0
                                                                                THEN SUM(CASE WHEN G3_gerencialSubGrupoConta.baseMargemBruta = 'S'			THEN G3_gerencialLancamentos.valorLancamento ELSE 0 END) / SUM(CASE WHEN G3_gerencialGrupoConta.receitaCustoMercadoria= 'REC'	THEN G3_gerencialLancamentos.valorLancamento ELSE 1 END)
                                                                        ELSE 0
                                                                        END
                                    FROM GAMA..G3_gerencialLancamentos			(nolock)
                                    JOIN GAMA..G3_gerencialContaGerencial		(nolock) ON G3_gerencialContaGerencial.id		= G3_gerencialLancamentos.idContaGerencial
                                    JOIN GAMA..G3_gerencialGrupoConta			(nolock) ON G3_gerencialGrupoConta.id			= G3_gerencialContaGerencial.idGrupoConta
                                    JOIN GAMA..G3_gerencialSubGrupoConta		(nolock) ON G3_gerencialSubGrupoConta.id		= G3_gerencialGrupoConta.idSubGrupoConta
                                    JOIN GAMA..G3_gerencialCentroCusto			(nolock) ON G3_gerencialCentroCusto.id			= G3_gerencialLancamentos.centroCusto
                                    JOIN GAMA..G3_gerencialEmpresas			    (nolock) ON G3_gerencialEmpresas.id				= G3_gerencialLancamentos.idEmpresa
                                    JOIN GAMA..G3_gerencialRegional             (nolock) ON G3_GerencialRegional.id             = G3_gerencialEmpresas.codigoRegional

                                    WHERE	1 = 1

                                    $filter
                                    
                                    GROUP BY G3_gerencialEmpresas.nomeAlternativo, G3_gerencialCentroCusto.siglaCentroCusto");
        return $dbData;
    }   //-- getMargemBruta --//

    /**
     *  setComparativoCCusto
     *  Define que os dados são para o comparativo de centro de custo
     * 
     *  @param  array     CentrosCusto
     * 
     */
    public function setComparativoCCusto(array $centrosCusto = NULL) {
        $this->comparativoCentroCusto   = TRUE;
        $this->reportCentrosCusto       = (!empty($centrosCusto) ? implode(',', $centrosCusto) : '');
    }

    /**
     *  getComparativoMensal
     *  Retorna os lançamentos para o relatório gerencial no comparativo mensal
     * 
     *  @param  Mixed     periodoMes
     *  @param  Mixed     periodoAno
     *  @param  Array     codigoEmpresa (Object / Array)
     * 
     *  @return object      dbDataObject
     * 
     */
    public function getComparativoMensal($periodoMes, $periodoAno, Array $codigoEmpresa) {
        $colEmpresaCCUsto     = "G3_gerencialEmpresas.nomeAlternativo, ";
        $colRegionalCCUsto    = "G3_gerencialRegional.descricaoRegional, ";
        $colGroupCCusto       = "";
        $whereCCusto          = "";
        $orderCCusto          = "";
        
        if ($this->comparativoCentroCusto) {
            $colEmpresaCCUsto     = "G3_gerencialEmpresas.nomeAlternativo + ' - ' + G3_gerencialCentroCusto.descricaoCentroCusto, ";
            $colRegionalCCUsto    = "G3_gerencialRegional.descricaoRegional + ' - ' + G3_gerencialCentroCusto.descricaoCentroCusto, ";
            $colGroupCCusto       = "G3_gerencialCentroCusto.descricaoCentroCusto, G3_gerencialCentroCusto.ordemExibicao, ";
            $whereCCusto          = (!empty($this->reportCentrosCusto) ? "G3_gerencialLancamentos.centroCusto IN (".$this->reportCentrosCusto.")" : '');
            $orderCCusto          = "G3_gerencialCentroCusto.ordemExibicao, ";
        }

        $dbData = DB::select("SELECT 	nomeEmpresa				= ".$colEmpresaCCUsto."
                                        nomeRegional            = ".$colRegionalCCUsto."
                                        mesAno					= CASE WHEN G3_gerencialLancamentos.mesLancamento	= 1 THEN 'JAN/'
                                                                       WHEN G3_gerencialLancamentos.mesLancamento	= 2 THEN 'FEV/'
                                                                       WHEN G3_gerencialLancamentos.mesLancamento	= 3 THEN 'MAR/'
                                                                       WHEN G3_gerencialLancamentos.mesLancamento	= 4 THEN 'ABR/'
                                                                       WHEN G3_gerencialLancamentos.mesLancamento	= 5 THEN 'MAI/'
                                                                       WHEN G3_gerencialLancamentos.mesLancamento	= 6 THEN 'JUN/'
                                                                       WHEN G3_gerencialLancamentos.mesLancamento	= 7 THEN 'JUL/'
                                                                       WHEN G3_gerencialLancamentos.mesLancamento	= 8 THEN 'AGO/'
                                                                       WHEN G3_gerencialLancamentos.mesLancamento	= 9 THEN 'SET/'
                                                                       WHEN G3_gerencialLancamentos.mesLancamento	= 10 THEN 'OUT/'
                                                                       WHEN G3_gerencialLancamentos.mesLancamento	= 11 THEN 'NOV/'
                                                                       WHEN G3_gerencialLancamentos.mesLancamento	= 12 THEN 'DEZ/'
                                                                  END + CONVERT(VARCHAR, anoLancamento),
                                        subGrupoConta			= G3_gerencialSubGrupoConta.descricaoSubGrupoConta,
		                                grupoConta				= G3_gerencialGrupoConta.descricaoGrupoConta,
                                        numeroContaGerencial	= G3_gerencialContaGerencial.codigoContaGerencial,
		                                contaGerencial			= G3_gerencialContaGerencial.descricaoContaGerencial,
                                        saldoLancamento			= SUM(G3_gerencialLancamentos.valorLancamento),
                                        totalReceita			= SUM(CASE WHEN G3_gerencialGrupoConta.receitaCustoMercadoria = 'REC' THEN G3_gerencialLancamentos.valorLancamento ELSE 0 END),

                                        /* hiden values */
                                        mes						= G3_gerencialLancamentos.mesLancamento,
                                        ano						= G3_gerencialLancamentos.anoLancamento,
                                        codigoContaGerencial	= G3_gerencialContaGerencial.codigoContaGerencial
                                FROM	GAMA..G3_gerencialLancamentos			(nolock)
                                JOIN	GAMA..G3_gerencialEmpresas				(nolock) ON G3_gerencialEmpresas.id								= G3_gerencialLancamentos.idEmpresa
                                JOIN	GAMA..G3_gerencialRegional				(nolock) ON G3_gerencialRegional.id								= G3_gerencialEmpresas.codigoRegional
                                JOIN	GAMA..G3_gerencialCentroCusto			(nolock) ON G3_gerencialCentroCusto.id							= G3_gerencialLancamentos.centroCusto
                                JOIN	GAMA..G3_gerencialContaGerencial		(nolock) ON G3_gerencialContaGerencial.id						= G3_gerencialLancamentos.idContaGerencial
                                JOIN	GAMA..G3_gerencialGrupoConta			(nolock) ON G3_gerencialGrupoConta.id							= G3_gerencialContaGerencial.idGrupoConta
                                JOIN	GAMA..G3_gerencialSubGrupoConta			(nolock) ON G3_gerencialSubGrupoConta.id						= G3_gerencialGrupoConta.idSubGrupoConta

                                WHERE	G3_gerencialLancamentos.mesLancamento	<= ?
                                AND		G3_gerencialLancamentos.anoLancamento	= ?
                                AND     G3_gerencialEmpresas.id                IN (".implode(',',$codigoEmpresa).")
                                ".$whereCCusto."

                                GROUP BY	G3_gerencialEmpresas.nomeAlternativo, ".$colGroupCCusto."
                                            G3_gerencialRegional.descricaoRegional,
                                            G3_gerencialLancamentos.mesLancamento, 
                                            G3_gerencialLancamentos.anoLancamento, 
                                            G3_gerencialContaGerencial.codigoContaGerencial, 
                                            G3_gerencialSubGrupoConta.descricaoSubGrupoConta,
                                            G3_gerencialGrupoConta.descricaoGrupoConta,
                                            G3_gerencialContaGerencial.descricaoContaGerencial

                                ORDER BY	".$orderCCusto." nomeEmpresa, ano, mes, numeroContaGerencial, grupoConta, subGrupoConta", [$periodoMes, $periodoAno]);
        return $dbData;
    }

    /**
     *  getLoteLancamento
     *  retorna o número do próximo lote de lançamentos gerenciais
     * 
     *  @return Int
     * 
     */
    public function getLoteLancamento() {
        return GerencialLancamento::max('numeroLote');
    }

    /**
     *  razaoContabil
     *  Retorna os lançamentos de uma conta contábil específica
     * 
     *  @param  object {mesLancamento, anoLancamento, codigoEmpresa, codigoCentroCusto, codigoConta}
     * 
     *  @return dbObject
     */
    public function razaoContabil($criterios) {

        return  DB::select("SELECT	nomeEmpresa             = Empresa.Empresa_Nome,
                                    idContaContabil         = PlanoConta.PlanoConta_ID,
                                    descricaoContaContabil  = PlanoConta.PlanoConta_Descricao,
                                    siglaCentroCusto        = G3_gerencialCentroCusto.siglaCentroCusto,
                                    centroCusto             = CentroResultado.CentroResultado_Descricao,
                                    codigoLancamento        = Lancamento.Lancamento_Codigo,
                                    dataLancamento          = CONVERT(varchar, Lancamento.Lancamento_Data, 103),
                                    documentoLancamento     = Lancamento.Lancamento_Documento,
                                    naturezaLancamento      = Lancamento.Lancamento_Natureza,
                                    observacaoLancamento    = Lancamento.Lancamento_Observacao,
                                    valorLancamento         = Lancamento.Lancamento_Valor * CASE WHEN Lancamento.Lancamento_Natureza = 'D' THEN -1 ELSE 1 END 
                            FROM    GrupoRoma_DealernetWF..Lancamento			(nolock)
                            JOIN	GrupoRoma_DealernetWF..Empresa			(nolock) ON Empresa.Empresa_Codigo							= Lancamento.Lancamento_EmpresaCod
                            JOIN	GrupoRoma_DealernetWF..PlanoConta		(nolock) ON PlanoConta.PlanoConta_Codigo					= Lancamento.Lancamento_PlanoContaCod
                            JOIN	GAMA..G3_gerencialEmpresas				(nolock) ON G3_gerencialEmpresas.codigoEmpresaERP			= Empresa.Empresa_Codigo
                            JOIN	GAMA..G3_gerencialCentroCusto			(nolock) ON G3_gerencialCentroCusto.codigoCentroCustoERP	= Lancamento.Lancamento_CentroResultadoCod
                            JOIN	GrupoRoma_DealernetWF..CentroResultado	(nolock) ON CentroResultado.CentroResultado_Codigo			= G3_gerencialCentroCusto.codigoCentroCustoERP

                            WHERE	G3_gerencialEmpresas.id         			= ".$criterios->codigoEmpresa."
                            AND		G3_gerencialCentroCusto.id					= ".$criterios->codigoCentroCusto."
                            AND		Lancamento.Lancamento_PlanoContaCod			= ".$criterios->codigoConta."
                            AND		MONTH(Lancamento.Lancamento_Data)			= ".$criterios->mesLancamento."
                            AND		YEAR(Lancamento.Lancamento_Data)			= ".$criterios->anoLancamento."
                            ORDER BY    dataLancamento");
    }
    
}