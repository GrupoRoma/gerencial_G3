<?php

namespace App\Http\Controllers\Utils;

use Exception;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Utils\Utilitarios;
use App\Models\GerencialEmpresas;
use App\Models\GerencialContaGerencial;
use App\Models\GerencialCentroCusto;

class UtilsController extends Controller
{
    protected   $utils;
    protected   $empresa;
    protected   $contaGerencial;
    protected   $centroCusto;

    protected   $formErrors;

    public function __construct() {
        $this->utils            = new Utilitarios;
        $this->empresa          = new GerencialEmpresas;
        $this->contaGerencial   = new GerencialContaGerencial;
        $this->centroCusto      = new GerencialCentroCusto;

    }
    
    /**
     *  Importa os parâmetros de grupo (G2) para os parâmetros de rateio (G3)
     */
    public function importarParametros() {

        $parametroData   = $this->utils->getParametrosG2();

        // Limpa (trunca) a tabela de parâmetros
        $this->utils->clearTable('ParametroRateio');

        // Corrige os valores dos ID's das colunas:
        //    EMP_CD_DE, EMP_CD_PARA
        foreach($parametroData as $row => $parametro) {
            $empresaDe  = explode(',', $parametro->EMP_CD_DE);
            $empresaPara= explode(',', $parametro->EMP_CD_PARA);
            
            $empresaDeConvertido = '';
            foreach ($empresaDe as $r => $codigoEmpresa) {
                $empresaDeConvertido .= ($this->empresa->getEmpresaERP($codigoEmpresa)->id ?? NULL).',';
            }

            $empresaParaConvertido = '';
            foreach ($empresaPara as $r => $codigoEmpresa) {
                $empresaParaConvertido .= ($this->empresa->getEmpresaERP($codigoEmpresa)->id ?? NULL).',';
            }

            $cCustoDe   = explode(',', $parametro->COD_CCUSTO_DE);
            $cCustoPara = explode(',', $parametro->COD_CCUSTO_PARA);

            $cCustoDeConvertido = '';
            foreach ($cCustoDe as $r => $codigoCentroCusto) {
                $cCustoDeConvertido .= ($this->centroCusto->getCentroCustoCodigoERP($codigoCentroCusto)->id ?? NULL).',';
            }

            $cCustoParaConvertido = '';
            foreach ($cCustoPara as $r => $codigoCentroCusto) {
                $cCustoParaConvertido .= ($this->centroCusto->getCentroCustoCodigoERP($codigoCentroCusto)->id ?? NULL).',';
            }

            $contaGerencialOrigem   = $this->contaGerencial->getId($parametro->COD_GRP_DE);
            $contaGerencialDestino  = $this->contaGerencial->getId($parametro->COD_GRP_PARA);

            // Base de cálculo
            switch ($parametro->COD_PAR_BCALC)
            {
                case '24':  $codigoBaseCalculo  = 4; break;
                case '25':  $codigoBaseCalculo  = 5; break;
                case '26':  $codigoBaseCalculo  = 6; break;
            }

            $dataSave   = [ 'descricaoParametro'            => ($parametro->observacao ?? '--'),
                            'idBaseCalculo'                 => $codigoBaseCalculo,
                            'idTipoLancamento'              => 7,
                            'codigoEmpresaOrigem'           => substr($empresaDeConvertido, 0, -1),
                            'codigoEmpresaDestino'          => substr($empresaParaConvertido, 0, -1),
                            'codigoContaGerencialOrigem'    => $contaGerencialOrigem,
                            'codigoContaGerencialDestino'   => $contaGerencialDestino,
                            'codigoCentroCustoOrigem'       => substr($cCustoDeConvertido, 0, -1),
                            'codigoCentroCustoDestino'      => substr($cCustoParaConvertido, 0, -1),
                            'historicoPadrao'               => '[PARAMETRO RATEIO]',
                            'formaAplicacao'                => 'PESO',
                            'parametroAtivo'                => 'S',
                            'created_at'                    => date('Y-m-d H:i:s'),
                            'created_by'                    => 1];

            $this->utils->saveParametro($dataSave);
       }

    }

    /**
     *  csvExport
     *  Exporta os dados de um relatório no formato CSV
     * 
     *  @param  Request     Dados a serem exportados (csvData)
     * 
     */
    public function csvExport(Request $request) {
        $csvFileName    = $request->csvFileName.'-'.date('YmdHis').'.csv';

        $headers = ["Content-type"        => "text/csv",
                    "Content-Disposition" => "attachment; filename=$csvFileName",
                    "Pragma"              => "no-cache",
                    "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                    "Expires"             => "0"];

        $csvData = $request->csvData;

        return response()->stream( function() use ($csvData) {
            echo $csvData;
        }, 200, $headers);
    }

    public function filialDP(Request $request) {

        return response($this->empresa->custom_codigoFilialDP($request->value), 200);
    }

}
