<?php

namespace App\Http\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Reports\ReportGenerator;

use App\Models\GerencialLancamento;
use App\Models\GerencialEmpresas;
use App\Models\GerencialCentroCusto;
use App\Models\GerencialRegional;
use App\Models\GerencialContaGerencial;

class RelatorioGerencialController extends Controller
{
    //
    protected   $lancamentos;
    protected   $reportGen;
    protected   $listaEmpresas;
    protected   $listaRegionais;
    protected   $listaCentroCusto;
    protected   $empresaReport;
    protected   $centroCustoReport;
    protected   $contaGerencial;

    protected   $margemBrutaVertical;
    protected   $margemBrutaHorizontal;
    protected   $receitaVertical;
    protected   $receitaHorizontal;
    protected   $margemTotalHorizontal;
    protected   $margemTotalVertical;
    protected   $receitaTotalHorizontal;
    protected   $receitaTotalVertical;


    protected   $filterValidateErrors;

    protected   $reportConditions;
    protected   $reportLayout;
    protected   $reportConfig;
    protected   $reportConfigData;

    protected   $preparedData;
    protected   $hrTotals;
    
    // considerar Lançamentos Extras, Valores Acumulados, Exibir Casas Decimais ou Consolidado
    protected   $configReport;

    public function __construct() 
    {
        $this->reportGen        = new ReportGenerator;
        $this->contaGerencial   = new GerencialContaGerencial;

        // Carrega as configurações do relatório
        $this->reportGen->loadConfig('gerencial');

        $this->contaGerencial->getInfoContaGerencial();

        $this->reportConfig     = $this->reportGen->config;
        $this->reportConfigData = $this->reportGen->configData;

        $this->listaEmpresas    = GerencialEmpresas::where('empresaAtiva', 'S')
                                                    ->orderBy('nomeAlternativo')
                                                    ->get();

        $this->listaRegionais   = GerencialRegional::orderBy('descricaoRegional')->get();

        // Identifica os Centros de Custo para a Análise Vertical
        $this->listaCentroCusto = GerencialCentroCusto::where('centroCustoAtivo', 'S')
                                                        ->orderBy('ordemExibicao')
                                                        ->orderBy('descricaoCentroCusto')
                                                        ->get();

        $this->lancamentos      = new GerencialLancamento;
    }

    public function index() {

        return view('relatorios.gerencial.filtroGerencial', ['empresas' => $this->listaEmpresas, 
                                                             'regionais' => $this->listaRegionais, 
                                                             'centroCusto' => $this->listaCentroCusto]);
    }

    /**
     *  build
     *  Prepara para exibição do relatório, condições e dados
     * 
     *  @param  use Illuminate\Http\Request
     * 
     *  @return response    (on error)
     */
    public function build(Request $request) {
        // Valida os critérios de seleção dos dados
        if (!$this->validateConditions($request->all())) {
            $erro    = 'Verifique as condições para emissão do relatório!';
            
            if (!empty($this->filterValidateErrors)) {
                $erro .= '<ul>';
                    foreach ($this->filterValidateErrors as $error) {
                        $erro    .= '<li>'.$error.'</li>';
                    }
                $erro .= '</ul>';
            }

            $this->error[]    = $erro;
            return response($this->error, '500');
        }

        // Calcula os percentuais de Margem Bruta
        $this->calculaMargemBruta($request->all());
        
        //--- ANÁLISE VERTICAL
        // Prepara as condições para seleção dos dados
        $this->prepareConditions($request->all());
        
        // Carrega os dados para o relatório
        $this->reportConditions[]   = ['column' => 'G3_gerencialCentroCusto.analiseVertical', 'value'   => 'S'];

        // Identifica os Centros de Custo para a Análise Vertical
        $this->centroCustoReport = GerencialCentroCusto::where('centroCustoAtivo', 'S')
                                                        ->where('analiseVertical', 'S')
                                                        ->orderBy('ordemExibicao')
                                                        ->orderBy('descricaoCentroCusto')
                                                        ->get();

        $verticalData = $this->lancamentos->getLancamentos(json_encode($this->reportConditions));

        // Prepara os dados para exibição no relatório
        $this->prepareVerticalData($verticalData);

        // Gera e exibe o relatório
        $verticalReport     = $this->generateReport();

        //--- ANÁLISE HORIZONTAL
        // Prepara as condições para seleção dos dados
        $this->prepareConditions($request->all());
        
        // Carrega os dados para o relatório
        $this->reportConditions[]   = ['column' => 'G3_gerencialCentroCusto.analiseVertical', 'value'   => 'N'];

        // Identifica os Centros de Custo para a Análise Vertical
        $this->centroCustoReport = GerencialCentroCusto::where('centroCustoAtivo', 'S')
                                                        ->where('analiseVertical', 'N')
                                                        ->orderBy('ordemExibicao')
                                                        ->orderBy('descricaoCentroCusto')
                                                        ->get();

        $horizontalData     = $this->lancamentos->getLancamentos(json_encode($this->reportConditions));

        // Prepara os dados para exibição no relatório
        $this->prepareHorizontalData($horizontalData);

        // Gera e exibe o relatório
        $horizontalReport   =  $this->generateReport('H');

        return $verticalReport.'<p>'.$horizontalReport;
    }

    /**
     *  validateConditions
     *  Verifica se foram informadas as condições obrigatórias para gerar o relatório
     * 
     *  @param  requestForm (all)
     * 
     *  @return boolean
     */
    private function validateConditions($conditions) {

        // Período
        if (!isset($conditions['periodo']) ||
             empty($conditions['periodo']) ||
             strlen($conditions['periodo']) < 6) {
                 $this->filterValidateErrors[] = "PERÍODO - Não foi informado ou não está no formato mm/YYYY (ex: 12/2020) ".strlen($conditions['periodo'].'<br> conteúdo: '.$conditions['periodo']);
        }
        else {
            $conditions['periodo'] = str_pad($conditions['periodo'],7,'0',STR_PAD_LEFT);

            // Verifica se existem lançamentos registrados para o período
            $dbData = GerencialLancamento::where('gerencialLancamentos.mesLancamento', substr($conditions['periodo'],0,2))
                                         ->where('gerencialLancamentos.anoLancamento', substr($conditions['periodo'],3,4))
                                         ->limit(10)
                                         ->get();
            if (!isset($dbData[0])) {
                $this->filterValidateErrors[] = "Não foram encontrados lançamentos para o período informado ".$conditions['periodo'];
                return FALSE;
            }
        }

        // Empresa ou Regional
        if ((!isset($conditions['codigoEmpresa']) || empty($conditions['codigoEmpresa'])) &&
            (!isset($conditions['codigoRegional']) || empty($conditions['codigoRegional']))) {
                $this->filterValidateErrors[] = "EMPRESA | REGIONAL - Selecione pelo menos uma Empresa ou Regional para gerar o relatório.";
        }
        else {
            // Identifica as empresas para o relatório
            if (!empty($conditions['codigoEmpresa']))   $this->empresaReport    = GerencialEmpresas::whereIn('id', $conditions['codigoEmpresa'])->get();
            if (!empty($conditions['codigoRegional']))  $this->empresaReport    = GerencialEmpresas::whereIn('codigoRegional', $conditions['codigoRegional'])->get();
        }
        


        return (!empty($this->filterValidateErrors) ? FALSE : TRUE);
    }

    /**
     * prepareConditions
     * prepara as condições para geração do relatório de acordo com as opções selecionadas pelo usuário
     * 
     *  @param requestForm (all)
     * 
     */
    private function prepareConditions($conditions) {
        $this->configReport = (object) $this->configReport;
        
        $this->configReport->periodo = str_pad($conditions['periodo'],7,'0', STR_PAD_LEFT);

        // Identifica o período para o relatório
        $this->reportConditions = [
                                    ['column'   => 'G3_gerencialLancamentos.mesLancamento',
                                     'value'    => substr(str_pad($conditions['periodo'],7,'0', STR_PAD_LEFT),0,2)],
                                    ['column'   => 'G3_gerencialLancamentos.anoLancamento',
                                     'value'    => substr(str_pad($conditions['periodo'],7,'0', STR_PAD_LEFT),3,4)]
                                  ];
        // Identifica a empresa selecionada
        if (isset($conditions['codigoEmpresa']) && !empty($conditions['codigoEmpresa'])) {
            $this->reportConditions[] = ['column'   => 'G3_gerencialLancamentos.idEmpresa', 
                                         'operator' => 'IN', 
                                         'value'    => $conditions['codigoEmpresa']];
        }
         
        if (isset($conditions['codigoRegional']) && !empty($conditions['codigoRegional'])) {
            $this->reportConditions[] = ['column'   => 'G3_gerencialRegional.id', 'operator' => 'IN', 'value' => $conditions['codigoRegional']];
        }

        if (isset($conditions['codigoCentroCusto']) && !empty($conditions['codigoCentroCusto'])) {
            $this->reportConditions[] = ['column'   => 'G3_gerencialLancamentos.centroCusto',
                                         'operator' => 'IN', 
                                         'value'    => $conditions['codigoCentroCusto']];
        }

        // Configurações para o relatório
        if (isset($conditions['extras']))       $this->configReport->lancamentosExtras    = TRUE;
        else                                    $this->configReport->lancamentosExtras    = FALSE;
        if (isset($conditions['acumulado']))    $this->configReport->acumulado            = TRUE;
        else                                    $this->configReport->acumulado            = FALSE;
        if (isset($conditions['decimal']))      $this->configReport->decimais             = TRUE;
        else                                    $this->configReport->decimais             = FALSE;
        if (isset($conditions['consolidado']))  $this->configReport->consolidado          = TRUE;
        else                                    $this->configReport->consolidado          = FALSE;

        /**
         *  Layout do relatório
         *  empresa | regional | comparativoMensal | comparativoEmpresa | comparativoRegional
         * 
         */
        $this->reportLayout = $conditions['layoutRelatorio'];

    }   //-- prepareCOnditions --//


    /**
     *  prepareVerticalData
     *  Prepara os dados para exibição do relatório gerencial
     * 
     *  Os dados são carregados para análise vertical com a seguinte ordenação dos dados:
     * 
     *  Empresa > GRUPO DE CONTA > SUB-GRUPO DE CONTA > CONTA > CENTRO CUSTO
     * 
     *  @param  object      database data collection
     * 
     */
    private function prepareVerticalData($dataReport) {
        if (empty($dataReport))     return FALSE;

        $this->preparedData = [];
        
        foreach ($dataReport as $row => $data) {

            // Json Data
            if (!isset($this->preparedData['layoutEmpresa']
                                        [$data->nomeEmpresa]
                                        [$data->subGrupoConta]
                                        [$data->grupoConta]
                                        [stringMask($data->numeroContaGerencial, '##.###').' - '.$data->contaGerencial]
                                        ['jsonData'])) {
                $this->preparedData['layoutEmpresa']
                                    [$data->nomeEmpresa]
                                    [$data->subGrupoConta]
                                    [$data->grupoConta]
                                    [stringMask($data->numeroContaGerencial, '##.###').' - '.$data->contaGerencial]
                                    ['jsonData']    = json_encode($data);
                $this->preparedData['layoutRegional']
                                    [$data->nomeRegional]
                                    [$data->subGrupoConta]
                                    [$data->grupoConta]
                                    [stringMask($data->numeroContaGerencial, '##.###').' - '.$data->contaGerencial]
                                    ['jsonData']    = json_encode($data);
            }

            // Dados centro de custo
            if (!isset($this->preparedData['layoutEmpresa']
                                        [$data->nomeEmpresa]
                                        [$data->subGrupoConta]
                                        [$data->grupoConta]
                                        [stringMask($data->numeroContaGerencial, '##.###').' - '.$data->contaGerencial]
                                        [$data->siglaCentroCusto])) {
                $this->preparedData['layoutEmpresa']
                                    [$data->nomeEmpresa]
                                    [$data->subGrupoConta]
                                    [$data->grupoConta]
                                    [stringMask($data->numeroContaGerencial, '##.###').' - '.$data->contaGerencial]
                                    [$data->siglaCentroCusto]  = $data->valorLancamento;
                $this->preparedData['layoutRegional']
                                    [$data->nomeRegional]
                                    [$data->subGrupoConta]
                                    [$data->grupoConta]
                                    [stringMask($data->numeroContaGerencial, '##.###').' - '.$data->contaGerencial]
                                    [$data->siglaCentroCusto]  = $data->valorLancamento;
            }
            else {
                $this->preparedData['layoutEmpresa']
                                    [$data->nomeEmpresa]
                                    [$data->subGrupoConta]
                                    [$data->grupoConta]
                                    [stringMask($data->numeroContaGerencial, '##.###').' - '.$data->contaGerencial]
                                    [$data->siglaCentroCusto]  += $data->valorLancamento;
                $this->preparedData['layoutRegional']
                                    [$data->nomeRegional]
                                    [$data->subGrupoConta]
                                    [$data->grupoConta]
                                    [stringMask($data->numeroContaGerencial, '##.###').' - '.$data->contaGerencial]
                                    [$data->siglaCentroCusto]  += $data->valorLancamento;
            }

            // Acumula o total da conta (horizontal)
            if (!isset($this->hrTotals[stringMask($data->numeroContaGerencial, '##.###').' - '.$data->contaGerencial])) {
                $this->hrTotals[stringMask($data->numeroContaGerencial, '##.###').' - '.$data->contaGerencial] = 0;
            }
            $this->hrTotals[stringMask($data->numeroContaGerencial, '##.###').' - '.$data->contaGerencial] += $data->valorLancamento;
        }

    }   //-- prepareVerticalData --//

/**
     *  prepareHorizontalData
     *  Prepara os dados para exibição do relatório gerencial
     * 
     *  Os dados são carregados para análise horizontal com a seguinte ordenação dos dados:
     * 
     *  CENTRO DE CUSTO > GRUPO DE CONTA > SUB-GRUPO DE CONTA > CONTA GERENCIAL > EMPRESA
     * 
     *  @param  object      database data collection
     * 
     */
    private function prepareHorizontalData($dataReport) {
        if (empty($dataReport))     return FALSE;

        $this->preparedData = [];
        
        foreach ($dataReport as $row => $data) {

            // Json Data
            if (!isset($this->preparedData['layoutEmpresa']
                                        [$data->siglaCentroCusto]
                                        [$data->subGrupoConta]
                                        [$data->grupoConta]
                                        [stringMask($data->numeroContaGerencial, '##.###').' - '.$data->contaGerencial]
                                        ['jsonData'])) {
                $this->preparedData['layoutEmpresa']
                                    [$data->siglaCentroCusto]
                                    [$data->subGrupoConta]
                                    [$data->grupoConta]
                                    [stringMask($data->numeroContaGerencial, '##.###').' - '.$data->contaGerencial]
                                    ['jsonData']    = json_encode($data);

                $this->preparedData['layoutRegional']
                                    [$data->siglaCentroCusto]
                                    [$data->subGrupoConta]
                                    [$data->grupoConta]
                                    [stringMask($data->numeroContaGerencial, '##.###').' - '.$data->contaGerencial]
                                    ['jsonData']    = json_encode($data);
            }

            // Dados centro de custo
            if (!isset($this->preparedData['layoutEmpresa']
                                        [$data->siglaCentroCusto]
                                        [$data->subGrupoConta]
                                        [$data->grupoConta]
                                        [stringMask($data->numeroContaGerencial, '##.###').' - '.$data->contaGerencial]
                                        [$data->nomeEmpresa])) {
                $this->preparedData['layoutEmpresa']
                                    [$data->siglaCentroCusto]
                                    [$data->subGrupoConta]
                                    [$data->grupoConta]
                                    [stringMask($data->numeroContaGerencial, '##.###').' - '.$data->contaGerencial]
                                    [$data->nomeEmpresa]  = $data->valorLancamento;
                $this->preparedData['layoutRegional']
                                    [$data->siglaCentroCusto]
                                    [$data->subGrupoConta]
                                    [$data->grupoConta]
                                    [stringMask($data->numeroContaGerencial, '##.###').' - '.$data->contaGerencial]
                                    [$data->nomeRegional]  = $data->valorLancamento;
            }
            else {
                $this->preparedData['layoutEmpresa']
                                    [$data->siglaCentroCusto]
                                    [$data->subGrupoConta]
                                    [$data->grupoConta]
                                    [stringMask($data->numeroContaGerencial, '##.###').' - '.$data->contaGerencial]
                                    [$data->nomeEmpresa]  += $data->valorLancamento;
                $this->preparedData['layoutRegional']
                                    [$data->siglaCentroCusto]
                                    [$data->subGrupoConta]
                                    [$data->grupoConta]
                                    [stringMask($data->numeroContaGerencial, '##.###').' - '.$data->contaGerencial]
                                    [$data->nomeRegional]  += $data->valorLancamento;
            }

            // Acumula o total da conta (horizontal)
            if (!isset($this->hrTotals[stringMask($data->numeroContaGerencial, '##.###').' - '.$data->contaGerencial])) {
                $this->hrTotals[stringMask($data->numeroContaGerencial, '##.###').' - '.$data->contaGerencial] = 0;
            }
            $this->hrTotals[stringMask($data->numeroContaGerencial, '##.###').' - '.$data->contaGerencial] += $data->valorLancamento;
        }

    }   //-- prepareHorizontalData --//

    /**
     *  calculaMargemBruta
     *  Calcula o valor da margem bruta por empresa / centro de custo e também centro de custo / empresa
     * 
     *  @param  Request     conditions      Condições para gerar o relatório
     * 
     *  @return none
     */
    public function calculaMargemBruta($conditions) {
        // Inclui as condições para carregar os dados de margem bruta e receita
        // ANÁLISE VERTICAL - MARGEM BRUTA
        $this->prepareConditions($conditions);
        $this->reportConditions[]   = ['column' => 'G3_gerencialSubGrupoConta.baseMargemBruta', 'value'   => 'S'];
        $this->reportConditions[]   = ['column' => 'G3_gerencialCentroCusto.analiseVertical', 'value'   => 'S'];

        $margemTotalVertical        = [];
        $receitaTotalVertical       = [];
        foreach ($this->lancamentos->getMargemBruta(json_encode($this->reportConditions)) as $row => $data) {
            if (!isset($this->margemTotalVertical[$data->nomeEmpresa])) {
                $this->margemTotalVertical[$data->nomeEmpresa]    = 0;
                $this->receitaTotalVertical[$data->nomeEmpresa]   = 0;
            }

            if (!isset($this->receitaVertical[$data->nomeEmpresa][$data->siglaCentroCusto])) {
                $this->receitaVertical[$data->nomeEmpresa][$data->siglaCentroCusto] = 0;
            }

            $this->margemBrutaVertical[$data->nomeEmpresa][$data->siglaCentroCusto]  = $data->percentualMargemBruta * 100;
            $this->receitaVertical[$data->nomeEmpresa][$data->siglaCentroCusto]     += $data->valorReceita;
            $this->margemTotalVertical[$data->nomeEmpresa]    += $data->valorMargemBruta;
            $this->receitaTotalVertical[$data->nomeEmpresa]   += $data->valorReceita;
        }

        // Inclui as condições para carregar os dados de margem bruta e receita
        // ANÁLISE HORIZONTAL - MARGEM BRUTA
        $this->prepareConditions($conditions);
        $this->reportConditions[]   = ['column' => 'G3_gerencialSubGrupoConta.baseMargemBruta', 'value'   => 'S'];
        $this->reportConditions[]   = ['column' => 'G3_gerencialCentroCusto.analiseVertical', 'value'   => 'N'];

        $margemTotalHorizontal      = [];
        $receitaTotalHorizontal     = [];
        foreach ($this->lancamentos->getMargemBruta(json_encode($this->reportConditions)) as $row => $data) {
            if (!isset($this->margemTotalHorizontal[$data->siglaCentroCusto])) {
                $this->margemTotalHorizontal[$data->siglaCentroCusto]    = 0;
                $this->receitaTotalHorizontal[$data->siglaCentroCusto]   = 0;
            }

            if (!isset($this->receitaHorizontal[$data->siglaCentroCusto][$data->nomeEmpresa])) {
                $this->receitaHorizontal[$data->siglaCentroCusto][$data->nomeEmpresa] = 0;
            }

            $this->margemBrutaHorizontal[$data->siglaCentroCusto][$data->nomeEmpresa]  = $data->percentualMargemBruta * 100;
            $this->receitaHorizontal[$data->siglaCentroCusto][$data->nomeEmpresa]     += $data->valorReceita;
            $this->margemTotalHorizontal[$data->siglaCentroCusto]    += $data->valorMargemBruta;
            $this->receitaTotalHorizontal[$data->siglaCentroCusto]   += $data->valorReceita;
        }

//dd('RV', $this->receitaVertical, 'RH', $this->receitaHorizontal);
    }

    /**
     *  generateReport
     *  gera o relatório e exibe
     * 
     *  @param  string     tipoAnalise (DEFAULT: 'V' - Vertical)
     * 
     */
    private function generateReport($tipoAnalise = 'V') {

        if (empty($this->preparedData)) return response("Não foi encontrada nenhuma informação para gerar o relatório", 500);
        else {
            return view("relatorios.gerencial.layoutEmpresa", ['configReport'           => $this->configReport,
                                                                'configLoaded'          => $this->reportConfig,
                                                                'infoConta'             => $this->contaGerencial->infoContaGer,
                                                                'nomeEmpresaRegional'   => 'EMPRESA',
                                                                'centrosCusto'          => $this->centroCustoReport,
                                                                'empresas'              => $this->empresaReport,
                                                                'reportData'            => $this->preparedData,
                                                                'hrTotals'              => $this->hrTotals,
                                                                'tipoAnalise'           => $tipoAnalise,
                                                                'margemBrutaHorizontal' => $this->margemBrutaHorizontal,
                                                                'margemBrutaVertical'   => $this->margemBrutaVertical,
                                                                'margemTotalVertical'   => $this->margemTotalVertical,
                                                                'margemTotalHorizontal' => $this->margemTotalHorizontal,
                                                                'receitaTotalVertical'  => $this->receitaTotalVertical,
                                                                'receitaTotalHorizontal'=> $this->margemTotalHorizontal,
                                                                'receitaVertical'       => $this->receitaVertical,
                                                                'receitaHorizontal'     => $this->receitaHorizontal
                                                            ]);
        }
    }

    /**
     *  Exibe os lançamentos gerenciais a partir do clique na linha do relatório
     * 
     *  @param  string      Mês de referência
     *  @param  int         Ano de referência
     *  @param  int         Código da empresa
     *  @param  int         Código da conta gerencial
     * 
     *  @return dbobject
     */
    public function detalhamentoContaGerencial(Request $request) { //String $mes, Int $ano, Int $codigoEmpresa, Int $codigoContaGerencial ) {
        $criterios      = [
                            ['column'   => 'G3_gerencialLancamentos.idEmpresa',         'value' => $request->codigoEmpresa],
                            ['column'   => 'G3_gerencialLancamentos.idContaGerencial',  'value' => $request->codigoContaGerencial],
                            ['column'   => 'G3_gerencialLancamentos.mesLancamento',     'value' => $request->mes],
                            ['column'   => 'G3_gerencialLancamentos.anoLancamento',     'value' => $request->ano]
                          ];

        $this->lancamentos->addGetColumns(['tipoLancamento' => 'G3_gerencialLancamentos.idTipoLancamento',
                                            'valor'         => 'G3_gerencialLancamentos.valorLancamento',
                                            'historico'     => 'G3_gerencialLancamentos.historicoLancamento']);

        $dataDetalhe            = $this->lancamentos->getLancamentos(json_encode($criterios));
        $reportData             = ['dataDetalhe'            => $dataDetalhe,
                                   'nomeConta'              => $dataDetalhe[0]->contaGerencial,
                                   'numeroContaGerencial'   => $dataDetalhe[0]->numeroContaGerencial,
                                   'mesAno'                 => str_pad($request->mes,2,"0", STR_PAD_LEFT).'/'.$request->ano];

        return view("relatorios.gerencial.detalheConta", ['reportData' => $reportData]); //['dataDetalhe' => $dataDetalhe, 'cabecalho' => $cabecalho]);
    }


}
