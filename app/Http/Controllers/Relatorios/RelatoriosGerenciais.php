<?php

namespace App\Http\Controllers\Relatorios;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;
use App\Models\GerencialLancamento;
use App\Reports\ReportGenerator;
use App\Models\Report\ReportCustomData;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelatoriosGerenciais extends Controller
{
    protected   $reportGen;
    protected   $customData;

    public function __construct() 
    {
        $this->reportGen        = new ReportGenerator;
        $this->lancamentos      = new GerencialLancamento;
        $this->customData       = new ReportCustomData;
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
        if (isset($request->periodoLancamento) && !empty($request->periodoLancamento)) {
            $inicial    = explode('/', $request->periodoLancamento[0]);
            $final      = explode('/', $request->periodoLancamento[1]);

            // Mês e Ano de lançamento
            $conditions[]   = ['column' => 'G3_gerencialLancamentos.mesLancamento', 'operator' => 'IN', 'value' => [$inicial[0], $final[0]]];
            $conditions[]   = ['column' => 'G3_gerencialLancamentos.anoLancamento', 'operator' => 'IN', 'value' => [$inicial[1], $final[1]]];
        }

        if (isset($request->idEmpresa) && !empty($request->idEmpresa)) {
            $conditions[]   = ['column' => 'G3_gerencialLancamentos.idEmpresa', 'operator' => '=', 'value' => $request->idEmpresa];
        }

        if (isset($request->idTipoLancamento) && !empty($request->idTipoLancamento)) {
            $conditions[]   = ['column' => 'G3_gerencialLancamentos.idTipoLancamento', 'operator' => '=', 'value' => $request->idTipoLancamento];
        }
/* 
        $conditions  = [
#            ['column'   => 'G3_gerencialLancamentos.idEmpresa', 'operator'  => 'IN', 'value' => [1,2]],
#            ['column'   => 'G3_gerencialLancamentos.idContaGerencial', 'operator'  => '=', 'value' => $dataRateio->codigoContaGerencialDestino],
#            ['column'   => 'G3_gerencialLancamentos.centroCusto', 'operator'  => 'IN', 'value' => explode(',', $dataRateio->codigoCentroCustoDestino)],
#            ['column'   => 'G3_gerencialLancamentos.idTipoLancamento', 'operator'  => '<>', 'value' => 7]
        ];  */

        $this->lancamentos->addGetColumns([ 'tipoLancamento'    => 'G3_gerencialTipoLancamento.descricaoTipoLancamento',
                                            'numeroLote'        => 'G3_gerencialLancamentos.numeroLote'
                                          ]);
        $reportData = $this->lancamentos->getLancamentos(json_encode($conditions));

        $this->reportGen->setData($reportData);

        return $this->reportGen->buildReport();
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

        if (($this->reportGen->configData->selectionShowData ?? TRUE) || $request->reportSelection) {

            if ($this->reportGen->configData->customQuery ?? FALSE) {
                // Atribui os valores de filtro case tenha sido definidos
                if (!empty($customFilters)) $this->reportGen->configData->customData->filter = $customFilters;

                // Define os dados para montagem do relatório
                if (!$this->reportGen->setData( $this->customData->customData($this->reportGen->configData->customData))) {
                    return view('relatorios.reportNoData', ['mensagem' => $this->reportGen->error]);
                }
            }
            else    {
                $dataQuery  = DB::table($tableName);
                if (!empty($whereRaw))  $dataQuery->whereRaw($whereRaw);

                if (!$this->reportGen->setData($dataQuery->get())) {
                    return view('relatorios.reportNoData', ['mensagem' => $this->reportGen->error]);
                }
            }

            return $this->reportGen->buildReport();
        }
    }
}
