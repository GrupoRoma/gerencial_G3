<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GerencialParametroRateio extends Model
{
    protected $table    = 'gerencialParametroRateio';

    protected $guarded  = ['id', 'idUsuario_created'];

    public $viewTitle       = 'Parâmetros de Rateio';
    public $columnList      = ['descricaoParametro', 
                                'idBaseCalculo', 
                                'idTipoLancamento', 
                                'codigoEmpresaOrigem', 
                                'codigoContaGerencialOrigem', 
                                'codigoCentroCustoOrigem',
                                'codigoEmpresaDestino',
                                'codigoContaGerencialDestino', 
                                'codigoCentroCustoDestino',
                                'historicoPadrao',
                                'formaAplicacao',
                                'parametroAtivo'];

    public $columnsGrid     = ['descricaoParametro', 
                                'codigoEmpresaOrigem', 
                                'codigoContaGerencialOrigem', 
                                'codigoCentroCustoOrigem',
                                'codigoEmpresaDestino',
                                'codigoContaGerencialDestino', 
                                'codigoCentroCustoDestino'];


    public $columnAlias     = ['descricaoParametro'             => 'Descrição', 
                                'idBaseCalculo'                 => 'Base de Cálculo',
                                'idTipoLancamento'              => 'Tipo de Lancamento', 
                                'codigoContaGerencialOrigem'    => 'Conta Gerencial [ORIGEM]', 
                                'codigoContaGerencialDestino'   => 'Conta Gerencial [DESTINO]', 
                                'codigoEmpresaOrigem'           => 'Empresa [ORIGEM]',
                                'codigoEmpresaDestino'          => 'Empresa [DESTINO]',
                                'codigoCentroCustoOrigem'       => 'Centro de Custo [ORIGEM]',
                                'codigoCentroCustoDestino'      => 'Centro de Custo [DESTINO]',
                                'historicoPadrao'               => 'Histórico Padrão',
                                'formaAplicacao'                => 'Forma de Aplicação',
                                'parametroAtivo'                => 'Parâmetro Ativo'];

    public $columnValue     = ['formaAplicacao'             => ['PESO' => 'Peso', 'TBLA' => 'Tabela']];

    public $customType      = ['formaAplicacao'             => ['type'      => 'radio',
                                                                'values'    => ['PESO' => 'Peso em relação à Base de Cálculo', 'TBLA' => 'Tabela de Referência']],
                               'parametroAtivo'             => ['type'      => 'radio',
                                                                'values'    => ['S' => 'Sim', 'N' => 'Não']]
                              ];

    public $rules  = ['descricaoParametro'              => 'required', 
                        'idBaseCalculo'                 => 'required_if:formaAplicacao,PESO', 
                        'idTipoLancamento'              => 'required', 
                        'codigoContaGerencialOrigem'    => 'required', 
                        'codigoContaGerencialDestino'   => 'required', 
                        'codigoEmpresaOrigem'           => 'required', 
                        'codigoEmpresaDestino'          => 'required',
                        'codigoCentroCustoOrigem'       => 'required',
                        'codigoCentroCustoDestino'      => 'required',
                        'historicoPadrao'               => 'nullable',
                        'formaAplicacao'                => 'required',
                        'idTabelaRateio'                => 'required_if:formaAplicacao,TBLA',
                        'parametroAtivo'                => 'required'];

    public $rulesMessage    = [ 'descricaoParametro'            => 'DESCRIÇÃO: Obrigatório',
                                'idBaseCalculo'                 => 'BASE DE CÁLCULO: Obrigatório se a Forma de Aplicação = PESO EM RELAÇÃO À BASE DE CÁLCULO',
                                'idTipoLancamento'              => 'TIPO DE LANÇAMENTO: Obrigatório',
                                'codigoContaGerencialOrigem'    => 'CONTA GERENCIAL DE ORIGEM: Obrigatório',
                                'codigoContaGerencialDestino'   => 'CONTA GERENCIAL DE DESTINO: Obrigatório',
                                'codigoEmpresaOrigem'           => 'EMPRESA DE ORIGEM: Obrigatório',
                                'codigoEmpresaDestino'          => 'EMPRESA DE DESTINO: Obrigatório',
                                'codigoCentroCustoOrigem'       => 'CENTRO DE CUSTO DE ORIGEM: Obrigatório',
                                'codigoCentroCustoDestino'      => 'CENTRO DE CUSTO DE DESTINO: Obrigatório',
                                'formaAplicacao'                => 'FORMA DE APLICAÇÃO: Obrigatório',
                                'idTabelaRateio'                => 'TABELA DE RATEIO: Obrigatório se a Forma de Aplicação = TABELA DE REFERÊNCIA',
                                'parametroAtivo'                => 'PARÂMETRO ATIVO: Obrigatório'
                            ];  

    public function vd_gerencialBaseCalculo($id) {
        $viewData = GerencialBaseCalculo::where('id', $id)->get();

        foreach ($viewData as $row => $data) {
            return $data->descricaoBaseCalculo;
        }
    }

    public function vd_gerencialTabelaRateios($id) {
        $viewData = GerencialTabelaRateio::where('id', $id)->get();

        foreach ($viewData as $row => $data) {
            return $data->descricao;
        }
    }

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

    public function vd_codigoEmpresaOrigem($values) {
        $viewData = GerencialEmpresas::whereIn('id', explode(',', $values))->get();

        $listData = '';
        foreach ($viewData as $row => $data) {
             $listData .= (!empty($listData) ? '<br>' : '').$data->nomeAlternativo;
        }

        return $listData;
    }

    public function vd_codigoEmpresaDestino($values) {
        return $this->vd_codigoEmpresaOrigem($values);
    }

    public function vd_codigoContaGerencialOrigem($values) {
        $viewData = GerencialContaGerencial::whereIn('id', explode(',', $values))->get();

        $listData = '';
        foreach ($viewData as $row => $data) {
             $listData .= (!empty($listData) ? '<br>' : '').$data->codigoContaGerencial.' - '.$data->descricaoContaGerencial;
        }

        return $listData;
    }

    public function vd_codigoContaGerencialDestino($values) {
        return $this->vd_codigoContaGerencialOrigem($values);
    }

    public function vd_codigoCentroCustoOrigem($values) {
        $viewData = GerencialCentroCusto::whereIn('id', explode(',', $values))->get();

        $listData = '';
        foreach ($viewData as $row => $data) {
             $listData .= (!empty($listData) ? '<br>' : '').$data->siglaCentroCusto.' - '.$data->descricaoCentroCusto;
        }

        return $listData;
    }

    public function vd_codigoCentroCustoDestino($values) {
        return $this->vd_codigoCentroCustoOrigem($values);
    }

    public function vd_gerencialCentroCusto($id) {
        $viewData = GerencialCentroCusto::where('id', $id)->get();

        foreach ($viewData as $row => $data) {
            return $data->descricaoCentroCusto;
        }
    }

    public function fk_gerencialBaseCalculo($columnValueName = 'id') {
        $fkData = GerencialBaseCalculo::orderBy('descricaoBaseCalculo')->get();

        $formValues[] = ['', ''];
        foreach($fkData as $row => $data) {
            $formValues[] = [$data->{$columnValueName}, $data->descricaoBaseCalculo];
        }

        return ['options' => $formValues, 'type' => '']; 
    }

    public function fk_gerencialContaGerencial($columnValueName = 'id') {
        $fkData = GerencialContaGerencial::orderBy('codigoContaGerencial')->get();

        $formValues[] = ['', ''];
        foreach($fkData as $row => $data) {
            $formValues[] = [$data->{$columnValueName}, $data->codigoContaGerencial.' - '.$data->descricaoContaGerencial];
        }

        return ['options' => $formValues, 'type' => '']; 
    }

    public function fk_gerencialTabelaRateio($columnValueName = 'id') {
        $fkData = GerencialTabelaRateio::orderBy('descricao')->get();

        $formValues = [];
        foreach($fkData as $row => $data) {
            $formValues[] = [$data->{$columnValueName}, $data->descricao];
        }

        return ['options' => $formValues, 'type' => '']; 
    }

    /*
     *  Formulário com drop de empresas para seleção multipla (default)
     */
    public function custom_codigoEmpresaOrigem($values = NULL, $multi = TRUE) {
        $empresas = GerencialEmpresas::where('empresaAtiva', 'S')->orderBy('nomeAlternativo')->get();

        $htmlForm = "<select name='codigoEmpresaOrigem".($multi ? '[]\' multiple' : '')." id='codigoEmpresaOrigem' class='form-control'>";
        if (!$multi) $htmlForm .= "<option></option>";

        $values = explode(',', $values);

        foreach ($empresas as $row => $data) {
            $htmlForm .= "<option value='".$data->id."' ".(in_array($data->id, $values) ? 'selected' : '').">".
                            $data->nomeAlternativo.
                         "</option>";
        }

        $htmlForm .= "</select>";

        return $htmlForm.($multi ? "<small class='form-text text-muted'><b>CTRL+Click</b> para selecionar mais de uma opção</small>" : "");
    }

    /*
     *  Formulário com drop de empresas para seleção multipla (default)
     */
    public function custom_codigoEmpresaDestino($values = NULL, $multi = TRUE) {
        $empresas = GerencialEmpresas::where('empresaAtiva', 'S')->orderBy('nomeAlternativo')->get();

        $htmlForm = "<select name='codigoEmpresaDestino".($multi ? '[]\' multiple' : '')." id='codigoEmpresaDestino' class='form-control'>";
        if (!$multi) $htmlForm .= "<option></option>";

        $values = explode(',', $values);

        foreach ($empresas as $row => $data) {
            $htmlForm .= "<option value='".$data->id."' ".(in_array($data->id, $values) ? 'selected' : '').">".
                            $data->nomeAlternativo.
                         "</option>";
        }

        $htmlForm .= "</select>";

        return $htmlForm.($multi ? "<small class='form-text text-muted'><b>CTRL+Click</b> para selecionar mais de uma opção</small>" : "");
    }

    /*
     *  Formulário com drop de contas gerenciais para seleção multipla (default)
     */
    public function custom_codigoContaGerencialOrigem($values = NULL, $multi = FALSE) {
        $empresas = GerencialContaGerencial::where('contaGerencialAtiva', 'S')->orderBy('descricaoContaGerencial')->get();

        $htmlForm = "<select name='codigoContaGerencialOrigem".($multi ? '[]\' multiple' : '')."' id='codigoContaGerencialOrigem' class='form-control'>";
        if (!$multi) $htmlForm .= "<option></option>";

        $values = explode(',', $values);

        foreach ($empresas as $row => $data) {
            $htmlForm .= "<option value='".$data->id."' ".(in_array($data->id, $values) ? 'selected' : '').">".
                            $data->codigoContaGerencial.' - '.$data->descricaoContaGerencial.
                         "</option>";
        }

        $htmlForm .= "</select>";

        return $htmlForm.($multi ? "<small class='form-text text-muted'><b>CTRL+Click</b> para selecionar mais de uma opção</small>" : "");
    }

    /*
     *  Formulário com drop de contas gerenciais para seleção multipla (default)
     */
    public function custom_codigoContaGerencialDestino($values = NULL, $multi = FALSE) {
        $empresas = GerencialContaGerencial::where('contaGerencialAtiva', 'S')->orderBy('descricaoContaGerencial')->get();

        $htmlForm = "<select name='codigoContaGerencialDestino".($multi ? '[]\' multiple' : '')."' id='codigoContaGerencialDestino' class='form-control'>";
        if (!$multi) $htmlForm .= "<option></option>";

        $values = explode(',', $values);

        foreach ($empresas as $row => $data) {
            $htmlForm .= "<option value='".$data->id."' ".(in_array($data->id, $values) ? 'selected' : '').">".
                            $data->codigoContaGerencial.' - '.$data->descricaoContaGerencial.
                         "</option>";
        }

        $htmlForm .= "</select>";

        return $htmlForm.($multi ? "<small class='form-text text-muted'><b>CTRL+Click</b> para selecionar mais de uma opção</small>" : "");
    }

    /*
     *  Formulário com drop de centros de custo para seleção multipla (default)
     */
    public function custom_codigoCentroCustoOrigem($values = NULL, $multi = TRUE) {
        $empresas = GerencialCentroCusto::where('centroCustoAtivo', 'S')->orderBy('descricaoCentroCusto')->get();

        $htmlForm = "<select name='codigoCentroCustoOrigem".($multi ? '[]\' multiple' : '')." id='codigoCentroCustoOrigem' class='form-control'>";
        if (!$multi) $htmlForm .= "<option></option>";

        $values = explode(',', $values);

        foreach ($empresas as $row => $data) {
            $htmlForm .= "<option value='".$data->id."' ".(in_array($data->id, $values) ? 'selected' : '').">".
                            $data->siglaCentroCusto.' - '.$data->descricaoCentroCusto.
                         "</option>";
        }

        $htmlForm .= "</select>";

        return $htmlForm.($multi ? "<small class='form-text text-muted'><b>CTRL+Click</b> para selecionar mais de uma opção</small>" : "");
    }

    /*
     *  Formulário com drop de centros de custo para seleção multipla (default)
     */
    public function custom_codigoCentroCustoDestino($values = NULL, $multi = TRUE) {
        $empresas = GerencialCentroCusto::where('centroCustoAtivo', 'S')->orderBy('descricaoCentroCusto')->get();

        $htmlForm = "<select name='codigoCentroCustoDestino".($multi ? '[]\' multiple' : '')." id='codigoCentroCustoDestino' class='form-control'>";
        if (!$multi) $htmlForm .= "<option></option>";

        $values = explode(',', $values);

        foreach ($empresas as $row => $data) {
            $htmlForm .= "<option value='".$data->id."' ".(in_array($data->id, $values) ? 'selected' : '').">".
                            $data->siglaCentroCusto.' - '.$data->descricaoCentroCusto.
                         "</option>";
        }

        $htmlForm .= "</select>";

        return $htmlForm.($multi ? "<small class='form-text text-muted'><b>CTRL+Click</b> para selecionar mais de uma opção</small>" : "");
    }
    
    public function fk_gerencialEmpresas($columnValueName = 'id') {
        $fkData = GerencialEmpresas::orderBy('nomeAlternativo')->get();

        $formValues[] = ['', ''];
        foreach($fkData as $row => $data) {
            $formValues[] = [$data->{$columnValueName}, $data->nomeAlternativo];
        }

        return ['options' => $formValues, 'type' => '']; 
    }

    public function fk_gerencialCentroCusto($columnValueName = 'id') {
        $fkData = GerencialCentroCusto::orderBy('siglaCentroCusto')->get();

        $formValues[] = ['', ''];
        foreach($fkData as $row => $data) {
            $formValues[] = [$data->{$columnValueName}, $data->siglaCentroCusto.' - '.$data->descricaoCentroCusto];
        }

        return ['options' => $formValues, 'type' => '']; 
    }

    public function fk_gerencialTipoLancamento($columnValueName = 'id') {
        $fkData = GerencialTipoLancamento::orderBy('descricaoTipoLancamento')->get();

        $formValues[] = ['', ''];
        foreach($fkData as $row => $data) {
            $formValues[] = [$data->{$columnValueName}, $data->descricaoTipoLancamento];
        }

        return ['options' => $formValues, 'type' => '']; 
    }


    /**
     *  Calcula o valor de origem do parâmetro de rateio
     * 
     *  @param  string  Mês de regferência
     *  @param  int     Ano de referência
     *  @param  string  Lista de códigos de empresa de origem
     *  @param  string  Lista de códigos de conta gerencial de origem
     *  @param  string  Lista de códigos de centros de custo de origem
     * 
     *  @return object
     */
    public function valorOrigem($mes, $ano, $codigoEmpresa, $codigoContaGerencial, $codigoCentroCusto) {
        $codigoEmpresa          = str_replace(',',"','", $codigoEmpresa);
        $codigoContaGerencial   = str_replace(',',"','", $codigoContaGerencial);
        $codigoCentroCusto      = str_replace(',',"','", $codigoCentroCusto);

        $valorOrigem = DB::select("SELECT	G3_gerencialLancamentos.idEmpresa,
                                            G3_gerencialLancamentos.centroCusto,
                                            G3_gerencialLancamentos.idContaGerencial,
                                            valorOrigem		= SUM(G3_gerencialLancamentos.valorLancamento)
                                  FROM  GAMA..G3_gerencialLancamentos	(nolock)
                                  JOIN GAMA..G3_gerencialEmpresas		(nolock) ON G3_gerencialEmpresas.id     = G3_gerencialLancamentos.idEmpresa
                                  JOIN GAMA..G3_gerencialCentroCusto	(nolock) ON G3_gerencialCentroCusto.id  = G3_gerencialLancamentos.centroCusto
                                  WHERE G3_gerencialLancamentos.mesLancamento	    = '".$mes."'
                                  AND   G3_gerencialLancamentos.anoLancamento	    = '".$ano."'
                                  AND   G3_gerencialLancamentos.idEmpresa	        IN ('".$codigoEmpresa."')
                                  AND   G3_gerencialLancamentos.idContaGerencial    IN ('".$codigoContaGerencial."')
                                  AND   G3_gerencialLancamentos.centroCusto         IN ('".$codigoCentroCusto."')
                                  AND   G3_gerencialEmpresas.empresaAtiva           = 'S'
                                  AND   G3_gerencialCentroCusto.centroCustoAtivo    = 'S'
                                  GROUP BY G3_gerencialLancamentos.idEmpresa, 
                                           G3_gerencialLancamentos.centroCusto, 
                                           G3_gerencialLancamentos.idContaGerencial");
        return $valorOrigem;
    }

}
