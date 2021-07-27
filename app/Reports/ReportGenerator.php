<?php

namespace App\Reports;
use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use phpDocumentor\Reflection\Types\Boolean;

class ReportGenerator extends Controller
{
    // Build Parts
    protected   $built_header;
    protected   $built_bodyHeader;
    protected   $built_body;
    protected   $built_bodyFooter;
    protected   $built_footer;

    // Definição de estilos (CSS)
    protected   $customCss              = FALSE;
    protected   $customCssFile;
    protected   $cssHeaderTitle;
    protected   $cssHeaderSubTitle;
    protected   $cssHeaderData;
    protected   $cssLineData;
    protected   $cssAlignNumbers;
    protected   $cssAlignDate;
    protected   $cssSubTotals;
    protected   $cssTotals;
    
    protected   $report;
    protected   $numberOfColumns;

    // Custom columns collection
    protected   $customColumns;
    
    // Data columns names
    protected   $dataColumnNames;

    
    // Gerador de relatórios
    // A partir de um array / objeto / json
    public $config;
    public $configData;
    public $configColumnsFormat;
    public $error;

    // Informações de agrupamento de linhas (group)
    protected   $groupLastValue;

    // Calculates
    protected   $calc_Count;
    protected   $calc_Sum;
    protected   $calc_Average;
    protected   $calc_TotalCount;
    protected   $calc_TotalSum;
    protected   $calc_TotalAverage;


    // Conteúdo do relatório
    public  $reportData;
    public  $reportCSVData  = '';

    protected   $reportToolBar;
    protected   $filterTool = TRUE;
    
    public function __construct() {
//        $this->loadViewsFrom(__DIR__.'/views', 'reportgen');

    }

    /**
     *  loadConfig
     *  carrega as configurações do relatório
     * 
     *  @param  string  nome do arquivo
     * 
     *  @return boolean
     * 
     */
    public function loadConfig($jsonFileName) {
        $configFile = app_path('Reports/config/'.$jsonFileName.'-config.json');
        $configData = app_path('Reports/config/'.$jsonFileName.'-config-data.json');

        // Arquivo de configuração base do relatório
        if(!file_exists($configFile)) {
            throw new Exception('Arquivo de configuração do relatório ['.$configFile.'] não encontrado');
        }
        
        try {
            // Carrega as configurações gerais
            $this->config      = json_decode(file_get_contents($configFile));

            // Identifica e define os padrões de estilo
            $this->setCss();
        }
        catch (Exception $msg){
            $this->error    = $msg;
            return false;
        }

        // Arquivo de configuração dos dados do relatório
        if(!file_exists($configData)) {
            throw new Exception('Arquivo de configuração do relatório ['.$configData.'] não encontrado');
        }
        
        try {
            // Carrega as configuração para os dados do relatório
            $this->configData      = json_decode(file_get_contents($configData));

            // Formatação por colunas
            foreach ($this->configData->columnsFormat as $item => $format) {
                $keyFormat = $this->configData->columnsFormat[$item];
                $keyColumnName = array_keys((array) $keyFormat)[0];

                $this->configColumnsFormat[$keyColumnName] = $this->configData->columnsFormat[$item]->$keyColumnName;
            }
            settype($this->configColumnsFormat, 'object');

            return TRUE;            
        }
        catch (Exception $msg){
            $this->error    = $msg;
            return FALSE;
        }
    }

    /**
     *  setCss
     *  Define os estilos do relatório
     * 
     */
    protected function setCss() {
        // Define os estilos (CSS) a partir de um arquivo CSS customizado
        if((isset($this->config->reportStyle->cssFile) && !empty($this->config->reportStyle->cssFile))
            && File::exists(asset($this->config->reportStyle->cssFile))) {
            $this->customCss        = TRUE;
            $this->customCssFile    = $this->config->reportStyle->cssFile;

            $this->cssHeaderTitle   = $this->config->reportStyle->headerTitle;
            $this->cssHeaderSubTitle= $this->config->reportStyle->headerSubTitle;
            $this->cssHeaderData    = $this->config->reportStyle->headerDataClass;
            $this->cssLineData      = $this->config->reportStyle->lineDataClass;
            $this->cssAlignNumbers  = $this->config->reportStyle->alignNumbers;
            $this->cssAlignDate     = $this->config->reportStyle->alignDate;
            $this->cssSubTotals     = $this->config->reportStyle->subTotals;
            $this->cssTotals        = $this->config->reportStyle->totals;
        }
        // Utiliza os estilos padrões
        else {
            $this->cssHeaderTitle   = 'report-title';
            $this->cssHeaderSubTitle= 'report-sub-title';
            $this->cssHeaderData    = 'report-data-header';
            $this->cssLineData      = 'report-data';
            $this->cssAlignNumbers  = 'report-align-number';
            $this->cssAlignDate     = 'report-align-date';
            $this->cssSubTotals     = 'report-sub-totals';
            $this->cssTotals        = 'report-totals';
        }
    }


    /**
     *  setData
     *  Determina os dados a serem impressos
     * 
     *  @param  mixed   (array ou json object)
     * 
     *  @return boolean
     * 
     */
    public function setData($dataObject) {
        // Verifica o tipo de variárel da coleção de dados
        if (!is_object($dataObject) && !is_array($dataObject)) {
            throw new Exception('Os dados para o relatório não são do tipo suportado (object ou array).');
        }

        try {
            // Converte os dados em objeto
            $this->reportData       = (object) $dataObject;

            // Identifica e associa o nome das colunas do objeto de dados
            $this->dataColumnNames  = array_keys((array) $dataObject[0]);
            
            // total de colunas do relatório
            $this->numberOfColumns  = count($this->dataColumnNames);

            return TRUE;
        }
        catch (Exception $msg) {
            $this->error    = $msg;
            return FALSE;
        }
        
    }

    /**
     *  formatData
     *  Prepara o dado para exibição de acordo com o tipo de dado identificado
     */
    protected function formatData(String $columnName, $columnValue) {
        
        $formatedValue  = $columnValue;
        
#        if (isset($this->configData->columnsFormat) && !empty($this->configData->columnsFormat)) {

 #           foreach ($this->configData->columnsFormat as $item => $format) {
                if (isset($this->configColumnsFormat->$columnName->dataType)) {

                    switch ($this->configColumnsFormat->$columnName->dataType) {
                        case 'number':
                        case 'decimal':
                        case 'float':
                            $formatedValue  = number_format($columnValue, ($this->configColumnsFormat->$columnName->decimalPrecision ?? 0), ',','.');
                            break;
                        case 'negativeNumber':
                            if ($columnValue < 0) {
                                $formatedValue  = '( '.number_format(($columnValue * -1), ($this->configColumnsFormat->$columnName->decimalPrecision ?? 0), ',','.').' )';
                                $formatedValue = '<span class="negative">'.$formatedValue."</span>";
                            }
                            else {
                                $formatedValue  = number_format($columnValue, ($this->configColumnsFormat->$columnName->decimalPrecision ?? 0), ',','.');
                            }
                            break;
                        default:
                            $formatedValue = $columnValue;
                            break;
                    }
                }
#            }
#        }

        return $formatedValue;
    }

    /**
     *  addCustomColumn
     *  Adiciona colunas customizadas ao relatório
     *  
     *  @param  object  Dados da coluna customizada
     * 
     */
    protected function addCustomColumn(object $columnProperties) {
        // Coluna de dados customizada
        $this->customColumns[]  = [ 'columnName'        => $columnProperties->name,
                                    'headerAlias'       => $columnProperties->header,
                                    'value'             => $columnProperties->value,
                                    'valueCondition'    => $columnProperties->condition];

        // incrementa 1 no total de colunas do relatório
        $this->numberOfColumns ++;
    }

    /**
     *  checkGroup
     *  Agrupamento de dados por coluna
     * 
     *  @param  string      Nome da coluna a ser agrupada
     *  @param  mixed       Valor corrente da coluna de dados
     * 
     *  @return string      Código HTML para a "quebra" / agrupamento de dados
     * 
     */
    protected function checkGroup(String $columnName, $columnValue) {
        $groupValue     = '';

        if (isset($this->configData->columnGroup->$columnName)) {
            if(!isset($this->groupLastValue[$columnName]) ||
                      $this->groupLastValue[$columnName] !== $columnValue) {

                // Verifica se existem totais para serem exibidos
                $groupValue .= ($this->checkTotals() ?? '');

                $groupValue .= '<TR HEIGHT="5px" CLASS="report-clear-row"><TD COLSPAN="'.$this->numberOfColumns.'" CLASS="report-clear-row"></TD></TR>';
                $groupValue .= "<!--// AGRUPAMENTO DE COLUNA: $columnValue -->";
                $groupValue .= "\t\t<TR>\n";
                
                $groupValue .= "\t\t\t<TD COLSPAN='".$this->numberOfColumns."' CLASS='report-group-row'>";
                
                $groupValue .= strtoupper(($this->configData->columnGroup->$columnName) ?? '');
                $groupValue .= strtoupper($columnValue);

                $groupValue .= "</TD>";
                
                $groupValue .= "\t\t</TR>\n";

                $this->groupLastValue[$columnName] = $columnValue;
            }
        }

        return $groupValue;

    }

    /**
     *  reportCalculate
     *  Acumular os totais
     * 
     *  @param  string      Nome da coluna
     *  @param  mixed       Valor corrnte da coluna de dados
     * 
     */
    protected function reportCalculate(String $columnName, $columnValue) {

        if (isset($this->configData->columnCalculate->$columnName)) {
            ($this->calc_Sum[$columnName]           ?? $this->calc_Sum[$columnName]         = 0);
            ($this->calc_Count[$columnName]         ?? $this->calc_Count[$columnName]       = 0);
            ($this->calc_TotalSum[$columnName]      ?? $this->calc_TotalSum[$columnName]    = 0);
            ($this->calc_TotalCount[$columnName]    ?? $this->calc_TotalCount[$columnName]  = 0);

#            switch ($this->configData->columnCalculate->$columnName->calcType) {
#                case 'SUM':
                    $this->calc_Sum[$columnName]        += $columnValue;
                    $this->calc_TotalSum[$columnName]   += $columnValue;
#                    break;
#                case 'COUNT':
                    $this->calc_Count[$columnName]      ++;
                    $this->calc_TotalCount[$columnName] ++;
#                    break;
#            }
        }
    }

    /**
     *  checkTotals
     *  Retorna os valores calculados até o momento
     * 
     *  @param  boolean     Indica se devem ser retornados os valores dos sub-totais (TRUE)
     * 
     *  @return string      Código HTML da linha da totais / sub-totais
     */
    protected function checkTotals($subTotais = TRUE) {

        if ($subTotais && (count((array) $this->calc_Sum) == 0 && count((array) $this->calc_Count) == 0 && count((array) $this->calc_Average) == 0) ||
            !$subTotais && (count((array) $this->calc_TotalSum) == 0 && count((array) $this->calc_TotalCount) == 0 && count((array) $this->calc_TotalAverage) == 0)) {
            return FALSE;
        }

        $dataTotals = '';

        // Verifica as colunas retornadas pelos dados do relatório
        foreach ($this->dataColumnNames as $columnName) {
            // Verifica se é uma coluna calculada
            if (isset($this->configData->columnCalculate->$columnName)) {

                $dataTotals   .= '<td class="report-totals text-'.($this->configColumnsFormat->$columnName->horizontalAlign ?? "left ").'" ';

                if (!$this->configData->columnsWrap)    $dataTotals .= " NOWRAP ";

                $dataTotals   .= '>';
                
                switch ($this->configData->columnCalculate->$columnName->calcType) {
                    case 'SUM':
                        $columnValue    = ($subTotais ? $this->calc_Sum[$columnName] : $this->calc_TotalSum[$columnName]);
                        $icon           = 'fas fa-plus-square fa-sm';
                        break;
                    case 'COUNT':
                        $columnValue    = ($subTotais ? $this->calc_Count[$columnName] : $this->calc_TotalCount[$columnName]);
                        $icon           = 'fa fa-tasks fa-sm';
                        break;
                    case 'AVG':
                        if ($subTotais) $columnValue    = ($this->calc_Sum[$columnName] / ($this->calc_Count[$columnName] != 0 ? $this->calc_Count[$columnName] : 1));
                        else            $columnValue    = ($this->calc_TotalSum[$columnName] / ($this->calc_TotalCount[$columnName] != 0 ? $this->calc_TotalCount[$columnName] : 1));
                        $icon           = 'fas fa-chart-bar fa-sm';
                        break;
                }

                //$dataTotals   .= " <span class='".$icon." text-secondary'></span> ";
                $dataTotals   .= $this->formatData($columnName, $columnValue);
                $dataTotals   .= '</td>';
            }
            elseif (!in_array($columnName, $this->configData->columnsHide)) {
                $dataTotals .= "<TD></TD>";
            }
        }

        $totals  = "\t\t<TR CLASS='report-totals'>\n";
        $totals .= $dataTotals;      
        $totals .= "\t\t</TR>\n";

        $this->calc_Sum     = [];
        $this->calc_Count   = [];
        $this->calc_Average = [];

        return $totals;
    }

    /**
     *  showFilterTool
     *  Determina se deve ser exibido o botão para exibição do filtro
     * 
     *  @param  boolean     TRUE | FALSE
     * 
     */
    public function showFilterTool($showHide) {
        $this->filterTool = $showHide ?? TRUE;
    }

    /**
     *  reportTool
     *  Barra de ferramentas padrão
     * 
     *  @return string      Código HTML da barra de ferramentas
     *  
     */
    protected function reportTool() {
        $this->reportToolBar   = "<DIV CLASS='report-tool-bar'>";

            // FILTRO
            if ($this->filterTool) {
                $this->reportToolBar   .= "<BUTTON TYPE='button' CLASS='btn btn-secondary report-tool m-1' data-toggle='collapse' data-target='#report-selection' aria-expanded='true' aria-controls='report-selection'> <SPAN CLASS='fa fa-filter fa-2x p-2'></SPAN> </BUTTON>";
            }

            // IMPRIMIR
            $this->reportToolBar   .= "<BUTTON TYPE='button' CLASS='btn btn-secondary report-tool m-1' data-action='print' data-target='#report-area'> <SPAN CLASS='fa fa-print fa-2x p-2'></SPAN> </BUTTON>";

            // CSV EXPORT
//            $this->reportToolBar   .= "<BUTTON TYPE='button' CLASS='btn btn-secondary report-tool m-1' data-action='exportCsv' data-content='".$this->reportCSVData."'> <SPAN CLASS='fa fa-file-csv fa-2x p-2'></SPAN> </BUTTON>";
            $this->reportToolBar   .= "<BUTTON TYPE='button' CLASS='btn btn-secondary report-tool m-1' ONCLICK='$(\"#csvExport\").submit()'> <SPAN CLASS='fa fa-file-csv fa-2x p-2'></SPAN> </BUTTON>";

        $this->reportToolBar  .= "</DIV>";

        $this->reportToolBar .= '<FORM ID="csvExport" ACTION="'.route('csvExport') .'" METHOD="POST" TARGET="_blank">
                                    <INPUT TYPE="hidden" NAME="csvFileName" ID="csvFileName"    VALUE="'.$this->configData->reportConfigFile.'" />                            
                                    <INPUT TYPE="hidden" NAME="csvData"     ID="csvData"        VALUE="'.$this->reportCSVData.'" />
                                    <INPUT TYPE="hidden" NAME="_token" VALUE="'.csrf_token().'" />   
                                 </FORM>';

        return $this->reportToolBar;

    }

/****************************************** REPORT BUILDERS **********************************/


    /**
     *  reportHeader
     *  Prepara o cabeçalho do relatório com a logo, título / subtítulo e data e hora
     */
    protected function reportHeader() {
        // Inicializa a linha do cabeçalho do relatório
        $header  = "<TR>\n";
        $header .= "\n<TH COLSPAN='".$this->numberOfColumns."' class='border-top-0'>";

        // Conteúdo do cabeçalho
        $header .= "\n\n<div class='report-header'>\n";
        $header .= "\t<div class='row'>\n";

        // Logo
        $header .= "\t\t<div class='report-logo col-2'>\n";
        // Verifica se está configurada a exibição da logo
        if ($this->config->reportHeader->showLogo 
            && (isset($this->config->reportHeader->logoReport) && !empty($this->config->reportHeader->logoReport))) {
            $header .= "\t\t\t<img src='".$this->config->reportHeader->logoReport."' class='report-logo-image'";
            
            // Define o tamanho máximo da logo (width)
            if (isset($this->config->reportHeader->logoMaxSize)) {
                $header .= " width='".$this->config->reportHeader->logoMaxSize."' ";
            }
            $header .= ">\n";
        }
        $header .= "\t\t</div>\n";

        // Título e sub-título
        $header .= "\t\t<div class='col-8'>\n";
        if (isset($this->config->reportHeader->title) && !empty($this->config->reportHeader->title)) {
            // Título
            $header .= "\t\t\t<span class='".$this->cssHeaderTitle."'>".$this->config->reportHeader->title."</span><br>\n";
            // Sub-Título
            $header .= "\t\t\t<span class='".$this->cssHeaderSubTitle."'>".$this->config->reportHeader->subTitle."</span><br>\n";
        }
        $header .= "\t\t</div>\n";

        // Data / Hora
        $header .= "\t\t<div class='report-data-hora text-right col-2'>\n";
        // Verifica se devem ser exibidas a data e a hora de emissão
        if ($this->config->reportHeader->showDateTime) {
            $header .= date('d/m/Y H:i:s');
        }
        $header .= "\t\t</div>\n";
        $header .= "\t</div>\n";
        $header .="</div>\n\n";

        $header .= "\t</TH>\n";
        $header .= "</TR>\n";
        
        // Carrega a propriedade da ReportGen
        $this->built_header = $header;
    }

    /**
     *  buildHeader
     *  Monta as colunas de título de cada coluna do relatório
     * 
     */
    protected function bodyHeader() {
        
        $spanColumns    = FALSE;
        $countSpanCols  = 0;
        $spanTotal      = 0;

        // $headerColmuns = '<THEAD>';
        $headerColumns  = '';
        $csvData        = '';


        // Verifica as colunas retornadas pelos dados do relatório
        foreach ($this->dataColumnNames as $columnName) {
            // DATA CSV EXPORT
            $csvData    .= $columnName.';';

            // Verifica se a coluna da consulta deve ser exibida 
            if (!in_array($columnName, $this->configData->columnsHide)) {
                
                // Verifica se a coluna do cabeçalho deve ser agrupada
                if (!$spanColumns) {
                    // Open TH TAG
                    $headerColumns  .= "<TH class='".$this->cssHeaderData."' ";
                    
                    // Horizontal align
                    $headerColumns  .= "text-".($this->configColumnsFormat->$columnName->horizontalAlign ?? "left ");
                    
                    // Conteúdo da coluna
                    $columnLable    = ($this->configData->columnsCustomHeader->$columnName ?? $columnName);

                    // Agrupamento de colunas (span)
                    if (isset($this->configData->columnsCustomHeader->$columnName->span)) {
                        $headerColumns .= " COLSPAN='".$this->configData->columnsCustomHeader->$columnName->span."' ";

                        // Conteúdo da coluna
                        $columnLable    = ($this->configData->columnsCustomHeader->$columnName->label ?? $columnName);
                        
                        // Identifica a quantidade de colunas a serem agrupadas
                        $spanTotal      = $this->configData->columnsCustomHeader->$columnName->span;
                        
                        // Define o agrupamento de colunas
                        $spanColumns    = TRUE;

                        // Inicializa o contador de colunas agrupadas
                        $countSpanCols  = 1;
                    }

                    // Close TH TAG
                    $headerColumns  .= ">";

                    // Conteúdo "label" da coluna
                    $headerColumns  .= $columnLable;

                    $headerColumns  .= "</TH>";
                }
                
                // Incrementa o contador de colunas agrupadas
                if ($spanColumns && $countSpanCols < $spanTotal) $countSpanCols  ++;
                
                // Inicializa o agrupamento de colunas
                else {
                    $spanColumns    = FALSE;
                    $countSpanCols  = 0;
                }
            }
        }

        // Colunas customizadas (adicionadas ao final das colunas)
        if ($this->configData->columnsCustom) {
            foreach ($this->configData->columnsCustom as $customHeader) {
                // DATA CSV EXPORT
                $csvData    .= $customHeader.';';

                $headerColumns  .= "<TH class='".$this->cssHeaderData."' ";
                
                // Horizontal align
                //$headerColumns  .= "text-".($this->configData->columnsFormat->$columnName->horizontalAlign ?? "left ");
                
                // Close TH TAG
                $headerColumns  .= ">";

                // Column content (title)
                $headerColumns  .= $customHeader->header;
                $headerColumns  .= "</TH>";

                $this->addCustomColumn($customHeader);
            }
        }

        // DATA CSV EXPORT
        $this->reportCSVData    .= substr($csvData,0,-1)."\r\n";

        $this->built_bodyHeader = $headerColumns;
    }

    /**
     *  bodyRows
     *  Processa os dados do relatório e prepara para montar o relatório
     */
    protected function bodyRows() {

        //$bodyData   = '<TBODY>';
        $bodyRows   = '';

        foreach ($this->reportData as $dataRow => $dataColumnValue) {

            $bodyData   = '';
            $csvData    = '';
            foreach($this->dataColumnNames as $dataColumn) {
                // AGRUPAMENTO DE DADOS
                // Verifica o agrupamento de llinhas de dados
                $bodyRows   .= $this->checkGroup($dataColumn, $dataColumnValue->$dataColumn);

                // CÁLCULOS
                $this->reportCalculate($dataColumn, $dataColumnValue->$dataColumn);

                // DATA CSV EXPORT
                $csvData    .= $dataColumnValue->$dataColumn.';';

                if (!in_array($dataColumn, $this->configData->columnsHide)) {
                    $bodyData   .= '<td class="body-data-col text-'.($this->configColumnsFormat->$dataColumn->horizontalAlign ?? "left ").'" ';

                    if (!$this->configData->columnsWrap)    $bodyData .= " NOWRAP ";

                    // Horizontal align

                    $bodyData   .= '>';
                    $bodyData   .= $this->formatData($dataColumn, $dataColumnValue->$dataColumn);
                    $bodyData   .= '</td>';
                }
            }

            $bodyRows   .= '<TR class="'.$this->cssLineData.' body-row">';
            $bodyRows   .= $bodyData;
            $bodyRows   .= "</TR>\n";

            // DATA CSV EXPORT
            $this->reportCSVData    .= substr($csvData,0,-1)."\r\n";
        }

        $this->built_body = $bodyRows;
    }

    /**
     *  buildReport
     *  Gera o código HTML do relatório
     * 
     *  @return string      Código HTML para exibição do relatório
     */
    public function buildReport() {

        $this->report   = "";

        // Cabeçalho
        $this->reportHeader();
        
        // Cabeçalho dos dados
        $this->bodyHeader();
        
        // Dados
        $this->bodyRows();

        if ($this->customCss) {
            $this->report = "<link href='".asset($this->customCssFile)."' rel='stylesheet'>";
        }

        if ($this->configData->showToolBar) {
            $this->report   .= $this->reportTool();
        }

        $this->report   .= "<DIV id='report-area'>\n";

        // Configuração para a impressão
        if(isset($this->config->configReport)) {
            $this->report   .= "<STYLE>\n";
            $this->report   .= "@media print {\n";
            $this->report   .= "\t@page {\n";
            
            if (isset($this->config->configReport->orientation))    $this->report   .= "\tsize: ".$this->config->configReport->pageFormat.' '.$this->config->configReport->orientation.";\n";

            $this->report   .= "\t}\n";
            $this->report   .= "}\n";
            $this->report   .= "</STYLE>\n";
        }

        //$this->report   .= "\t<TABLE class='table table-sm table-striped table-hover report-table'>";
        $this->report   .= "\t<TABLE class='table-sm table-striped table-hover report-table'>";

        $this->report   .= "<THEAD>\n";
        $this->report   .= $this->built_header;
        $this->report   .= $this->built_bodyHeader;
        $this->report   .= "</THEAD>\n\n";

        $this->report   .= "<TBODY>\n";
        $this->report   .= $this->built_body;

        $this->report   .= $this->checkTotals();
        $this->report   .= $this->checkTotals(FALSE);

        $this->report   .= "</TBODY>\n\n";

        $this->report   .= "\t</TABLE>\n";
        $this->report   .= "</DIV>\n\n";

        return $this->report;
    }
}
