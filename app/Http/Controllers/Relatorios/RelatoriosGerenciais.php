<?php

namespace App\Http\Controllers\Relatorios;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;
use App\Models\GerencialLancamento;
use App\Reports\ReportGenerator;
use App\Models\Report\ReportCustomData;
use App\Models\GerencialPeriodo;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelatoriosGerenciais extends Controller
{
    protected   $reportGen;
    protected   $customData;
    protected   $periodo;

    public      $reportConditions;

    public function __construct() 
    {
        $this->reportGen        = new ReportGenerator;
        $this->lancamentos      = new GerencialLancamento;
        $this->customData       = new ReportCustomData;
        $this->periodo          = new GerencialPeriodo;
    }

    public function lancamentosGerenciais(Request $request) {

        // Carrega as configurações do relatório
        $this->reportGen->loadConfig('lancamentosGerenciais');

        // Exibe a tela de filtro de seleção
        if (isset($this->reportGen->configData->selectionData)
//            && !$request->reportSelection) {
            && !empty($this->reportGen->configData->selectionData)) {
            echo view('relatorios.selectionData', ['config'       => $this->reportGen->configData->selectionData, 
                                                        'title'        => $this->reportGen->config->reportHeader->title,
                                                        'tableName'    => 'gerencialLancamentos',
                                                        'model'        => app('App\\Models\\gerencialLancamento'), //'gerencialLancamentos',
                                                        'visibility'   => ($request->reportSelection == 1 ? '' : 'show'),
                                                        'formData'     => $request])->render();
        }

        // Desabilita a exibição do botão de filtro do relatório
        if (!isset($this->reportGen->configData->selectionData))    $this->reportGen->showFilterTool(FALSE);

        // Prepara as condições para o filtro
        $conditions = [];
        foreach ($this->reportGen->configData->selectionData as $filterColumns) {
            if ($filterColumns->columnName == 'periodoGerencial' && !empty($request->{$filterColumns->columnName})) {
                $inicial    = explode('/', $request->periodoGerencial[0]);
                $final      = explode('/', $request->periodoGerencial[1]);
    
                // Mês e Ano de lançamento
                $conditions[]   = ['column' => 'G3_gerencialLancamentos.mesLancamento', 'operator' => 'IN', 'value' => [$inicial[0], $final[0]]];
                $conditions[]   = ['column' => 'G3_gerencialLancamentos.anoLancamento', 'operator' => 'IN', 'value' => [$inicial[1], $final[1]]];
            }
            else {
                if (!empty($request->{$filterColumns->columnName})) {
                    $conditions[] = ['column'   => 'G3_gerencialLancamentos.'.$filterColumns->columnName, 'operator' => '=', 'value' => $request->{$filterColumns->columnName}];
                }
            }
        }
  
        if (!isset($request->periodoGerencial)) {
            $conditions     = [];
            $conditions[]   = ['column' => 'G3_gerencialLancamentos.mesLancamento', 'operator' => '=', 'value' => $this->periodo->mesAtivo];
            $conditions[]   = ['column' => 'G3_gerencialLancamentos.anoLancamento', 'operator' => '=', 'value' => $this->periodo->anoAtivo];
        }

        $this->lancamentos->addGetColumns([ 'tipoLancamento'    => 'G3_gerencialTipoLancamento.descricaoTipoLancamento',
                                            'numeroLote'        => 'G3_gerencialLancamentos.numeroLote'
                                          ]);
        if ($reportData = $this->lancamentos->getLancamentos(json_encode($conditions))) {
            $this->reportGen->setData($reportData);
            return $this->reportGen->buildReport();
        }
        else {
            return view('relatorios.reportNoData', ['mensagem' => 'Nenhuma informação encontrada que atenda os critérios selecionados.']);
        }
    }
    

    public function cadastro(Request $request) {
        $model      = app('App\\Models\\'.ucfirst(Route::currentRouteName()));
        $tableName  = $model->getTable();

        $this->reportGen->loadConfig($tableName);

        // Exibe a tela de filtro de seleção
        if (isset($this->reportGen->configData->selectionData) 
            && !empty($this->reportGen->configData->selectionData)) {
            echo view('relatorios.selectionData', ['config'       => $this->reportGen->configData->selectionData, 
                                                        'title'        => $this->reportGen->config->reportHeader->title,
                                                        'tableName'    => $tableName,
                                                        'model'        => $model,
                                                        'visibility'   => ($request->reportSelection == 1 ? '' : 'show'),
                                                        'formData'     => $request])->render();
        }

        // Desabilita a exibição do botão de filtro do relatório
        if (!isset($this->reportGen->configData->selectionData))    $this->reportGen->showFilterTool(FALSE);

        // Valida o filtro e exibe o reltório
        $whereRaw       = '';
        $customFilters  = [];
        if ($request->reportSelection) {
            foreach ($this->reportGen->configData->selectionData as $filter) {
                
                if (isset($request->{$filter->columnName})) {
                    // No Custom Data
                    $whereRaw   .= (empty($whereRaw) ? '' : 'AND ' ).$tableName.'.'.$filter->columnName.
                                    " = '".$request->{$filter->columnName}."'\n";

                    // Custom Data Filters
                    if ($this->reportGen->configData->customQuery) {
                        $customFilters[]    = [ 'columnName'  => $filter->columnName,
                                                'operator'    => '=',
                                                'value'       => $request->{$filter->columnName}];
                    }
                }
            }
        }
//DB::enableQueryLog();
        if (($this->reportGen->configData->selectionShowData ?? TRUE) || $request->reportSelection) {

            if ($this->reportGen->configData->customQuery ?? FALSE) {

                // Prepara as condições para o filtro
                if ($request->reportSelection)  $this->buildConditions($request);

                // Atribui os valores de filtro caso tenha sido definidos
                if (!empty($this->reportConditions))    $this->customData->setConditions((object) $this->reportConditions);

                // Atribui os valores de filtro case tenha sido definidos
                if (!empty($customFilters)) $this->reportGen->configData->customData->filter = $customFilters;

                // Define os dados para montagem do relatório
                if (!$this->reportGen->setData( $this->customData->customData($this->reportGen->configData->customData))) {
                    return view('relatorios.reportNoData', ['mensagem' => 'ERRO DE QUALQUER COISA - CAD'. $this->reportGen->error]);
                }
            }
            else    {
                $dataQuery  = DB::table($tableName);
                if (!empty($whereRaw))  $dataQuery->whereRaw($whereRaw);

                if (!$this->reportGen->setData($dataQuery->get())) {
                    return view('relatorios.reportNoData', ['mensagem' => 'ERRO DE ALGUMA COISA - CAD'. $this->reportGen->error]);
                }
            }
//dd($whereRaw, DB::getQueryLog());

            return $this->reportGen->buildReport();
        }
    }

    /**
     *  generalReports
     *  Gera relatórios a partir dos arquivos de configuração (json) definido como parâmetro na rota
     * 
     *  @param  Request
     * 
     */
    public function generalReports(Request $request) {

        // Identifica o nome do arquivo de configurações a partir da rota informada
        $reportConfigFile = Route::currentRouteName();

        // Carrega as configurações do relatório
        $this->reportGen->loadConfig($reportConfigFile);

        if (!isset($this->reportGen->configData->reportBaseModel)) {
            return response(['Não foi informado o modelo de dados base para o relatório. Informe ao administrador do sistema'], 500);
        }

        $model      = app('App\\Models\\'.$this->reportGen->configData->reportBaseModel);
        $tableName  = $model->getTable();

        // Exibe a tela de filtro de seleção
        if (isset($this->reportGen->configData->selectionData) 
            && !empty($this->reportGen->configData->selectionData)) {
            echo view('relatorios.selectionData', ['config'       => $this->reportGen->configData->selectionData, 
                                                        'title'        => $this->reportGen->config->reportHeader->title,
                                                        'tableName'    => $tableName,
                                                        'model'        => $model,
                                                        'visibility'   => ($request->reportSelection == 1 ? '' : 'show'),
                                                        'formData'     => $request])->render();
            }

        // Desabilita a exibição do botão de filtro do relatório
        if (!isset($this->reportGen->configData->selectionData))    $this->reportGen->showFilterTool(FALSE);

        // Prepara as condições para o filtro
        if ($request->reportSelection)  $this->buildConditions($request);

        // Verifica se os dados para o relatório são obtidos por uma consulta customizada
        if ($this->reportGen->configData->customQuery ?? FALSE) {

            // Atribui os valores de filtro caso tenha sido definidos
            if (!empty($this->reportConditions))    $this->customData->setConditions((object) $this->reportConditions);

            // Define os dados para montagem do relatório
            if (!$this->reportGen->setData( $this->customData->customData($this->reportGen->configData->customData))) {
                return view('relatorios.reportNoData', ['mensagem' => 'ERRO DE QUALQUER COISA - GR'.$this->reportGen->error]);
            }
        }
        else    {
            return view('relatorios.reportNoData', ['mensagem' => 'ERRO DE ALGUMA COISA - GR'. $this->reportGen->error]);
        }

        return $this->reportGen->buildReport();
    }

    /**
     *  buildConditions
     * 
     *  @param  Request     selection form values
     * 
     */
    public function buildConditions(Request $request)
    {

        $this->reportConditions = [];
        foreach ($this->reportGen->configData->selectionData as $filterColumns) {

            if (!empty($request->{$filterColumns->columnName})) {
                switch ($filterColumns->formType) {
                    case "period":
                        // Prepara as condições para seleção dos períodos de lançamentos gerenciais
                        if ($filterColumns->columnName == 'periodoGerencial') {
                            $inicial    = explode('/', $request->periodoGerencial[0]);
                            $final      = explode('/', ($request->periodoGerencial[1] ?? $request->periodoGerencial[0]));

                            // Mês do lançamento
                            $this->reportConditions[]   = [ 'columnName'    => 'G3_gerencialLancamentos.mesLancamento', 
                                                            'operator'      => '>=',
                                                            'value'         => "'".$inicial[0]."'"];
                            $this->reportConditions[]   = [ 'columnName'    => 'G3_gerencialLancamentos.mesLancamento', 
                                                            'operator'      => '<=',
                                                            'value'         => "'".$final[0]."'"];
                            // Ano do lançamento
                            $this->reportConditions[]   = [ 'columnName'    => 'G3_gerencialLancamentos.anoLancamento', 
                                                            'operator'      => '>=',
                                                            'value'         => "'".$inicial[1]."'"];
                            $this->reportConditions[]   = [ 'columnName'    => 'G3_gerencialLancamentos.anoLancamento', 
                                                            'operator'      => '<=',
                                                            'value'         => "'".$final[1]."'"];
                        }
                        // Prepara as condições para seleção de períodos de datas
                        // Usando o tipo de formulário date do HTML5 (<input type=date />)
                        else {
                            $dateStart  = $request->{$filterColumns->columnName}[0];
                            $dateEnd    = $request->{$filterColumns->columnName}[1];

                            $this->reportConditions[]   = [ 'columnName'    => $filterColumns->columnName, 
                                                            'operator'      => '>=',
                                                            'value'         => "'".$dateStart."'"];
                            $this->reportConditions[]   = [ 'columnName'    => $filterColumns->columnName, 
                                                            'operator'      => '<=',
                                                            'value'         => "'".$dateEnd."'"];
                        }
                        break;
                    default:
                        $this->reportConditions[] = [   'columnName'    => $filterColumns->columnName, 
                                                        'operator'      => ($filterColumns->operator ?? '='), 
                                                        'value'         => $request->{$filterColumns->columnName}];
                        break;
                }
            }
        }
    }
}
