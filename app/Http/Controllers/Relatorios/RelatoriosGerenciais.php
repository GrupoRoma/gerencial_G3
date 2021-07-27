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
            && !empty($this->reportGen->configData->selectionData)) {
            echo view('relatorios.selectionData', ['config'       => $this->reportGen->configData->selectionData, 
                                                        'title'        => $this->reportGen->config->reportHeader->title,
                                                        'tableName'    => 'gerencialLancamentos',
                                                        'model'        => 'gerencialLancamentos',
                                                        'visibility'   => ($request->reportSelection == 1 ? '' : 'show'),
                                                        'formData'     => $request])->render();
        }
        else {
            $this->reportGen->showFilterTool(FALSE);
        }
        
         $conditions  = [
#            ['column'   => 'G3_gerencialLancamentos.idEmpresa', 'operator'  => 'IN', 'value' => [1,2]],
#            ['column'   => 'G3_gerencialLancamentos.idContaGerencial', 'operator'  => '=', 'value' => $dataRateio->codigoContaGerencialDestino],
#            ['column'   => 'G3_gerencialLancamentos.centroCusto', 'operator'  => 'IN', 'value' => explode(',', $dataRateio->codigoCentroCustoDestino)],
#            ['column'   => 'G3_gerencialLancamentos.idTipoLancamento', 'operator'  => '<>', 'value' => 7]
        ]; 

//dd($request);

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
        else {
            $this->reportGen->showFilterTool(FALSE);
        }

        // Valida o filtro e exibe o relatório
        if ((!isset($this->reportGen->configData->selectionData) || empty($this->reportGen->configData->selectionData))
            || $request->reportSelection == 1) {

            if ($this->reportGen->configData->customQuery ?? FALSE) {
                $this->reportGen->setData( $this->customData->customData($this->reportGen->configData->customData));
            }
            else    $this->reportGen->setData(DB::table($tableName)->get());

            return $this->reportGen->buildReport();
        }
    }
}
