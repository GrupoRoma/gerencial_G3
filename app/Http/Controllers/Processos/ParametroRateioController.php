<?php

namespace App\Http\Controllers\Processos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Processos\GerencialExcessoesController;

use App\Models\Processos\ImportarContabilidade;

use App\Models\GerencialLancamento;
use App\Models\GerencialTipoLancamento;
use App\Models\GerencialParametroRateio;
use App\Models\GerencialTabelaRateio;
use App\Models\GerencialBaseCalculo;
use App\Models\GerencialContaGerencial;
use App\Models\GerencialEmpresas;
use App\Models\GerencialCentroCusto;
use App\Models\GerencialRegional;
use App\Models\GerencialPeriodo;
use App\Models\GerencialTabelaRateioPercentual;
use App\Models\Processos\Rateios;

class ParametroRateioController extends Controller
{
    protected   $titulo             = "PROCESSAMENTO DOS PARÂMETROS DE RATEIO";
    protected   $errors;
    protected   $importa;
    protected   $validateErrors;
    protected   $barraProgresso;
    protected   $periodoCorrente;

    protected   $lancamentoGerencial;
    protected   $tipoLancamento;
    protected   $historico;
    protected   $basesCalculo;
    protected   $periodo;
    protected   $parametro;
    protected   $tabelaRateio;
    protected   $tabelaPercentuais;

    protected   $rateios;

    public function __construct() 
    {
        $this->importa              = new ImportarContabilidade;
        $this->lancamentoGerencial  = new GerencialLancamento;
        $this->tipoLancamento       = new GerencialTipoLancamento;
        $this->periodo              = new GerencialPeriodo;
        $this->basesCalculo         = new GerencialBaseCalculo;
        $this->parametro            = new GerencialParametroRateio;
        $this->tabelaRateio         = new GerencialTabelaRateio;
        $this->tabelaPercentuais    = new GerencialTabelaRateioPercentual;
        $this->rateios              = new Rateios;
        $this->empresas             = new GerencialEmpresas;

        $this->periodoCorrente = $this->periodo->current();
    }
    
    public function index() {
        return view('processamento.processamentoRateio');
    }

    public function indexLogistica() {
        return view('processamento.processamentoLogistica');
    }

    /**
     *  Processa os parâmetros de rateio para aplicar a realocação de valores
     *  entre as contas gerenciais e centros de custo de origem e destino
     * 
     */
    public function processarParametros() {

        set_time_limit(0);

        // Inicializa a transação
        DB::beginTransaction();

        // Tipo de lancamento
        // 7: PARÂMETRO RATEIO
        $this->historico  = $this->tipoLancamento->getHistoricoLancamento(7);

        // Exclui o lançamentos gerados por processamentos anteriores no período corrente
        $this->lancamentoGerencial->deleteLancamentosGerenciais([['fieldName' => 'idTipoLancamento', 'values' => 7]]);


        // Processa os parâmetros de rateio por PESO
        if (!$this->parametroRateio_Peso()) {
            DB::rollBack();
            return view('processamento.validacao', ['errors' => $this->errors]);
        }
        else { 
            // Processa os parâmetros de rateio por TABELA
            if (!$this->parametroRateio_Tabela())   {
                DB::rollBack();
                return view('processamento.validacao', ['errors' => $this->errors]);
            }
        }

        DB::commit();
        // Sucesso no processamento dos parâmetros de rateio
        return ("<span id='showMsg' data-title='PROCESSAMENTO DOS PARÂMETROS'
                                    data-message='Parâmetros de Rateio processados com sucesso! '></span>");

    }

    /**
     *  PROCESSA OS PARÂMETROS DE RATEIO UTILIZANDO OS CRITÉRIOS DE RATEIO
     *  POR PESO SOBRE A BASE DE CÁLCULO (antigo parâmetro de grupo)
     *  
     *  @return boolean
     */
    public function parametroRateio_Peso() {
        // RATEIOS POR PESO | Base de Cálculo
        $rateioPeso     = GerencialParametroRateio::where('parametroAtivo', 'S')
                                                  ->where('formaAplicacao', 'PESO')
                                                  ->join('gerencialTipoLancamento', 'gerencialTipoLancamento.id', '=', 'gerencialParametroRateio.idTipoLancamento')
                                                  ->orderBy('gerencialTipoLancamento.ordemProcessamento')
                                                  //->where('id', 134)
                                                  ->get();

        // PROCESSA OS RATEIOS POR PESO DE CONTA
        // EM REALÇÃO À BASE  DE CÁLCULO
        foreach ($rateioPeso as $row => $dataRateio) {

            // Identifica as empresas para o lançamento de contrapartida
            $empresasOrigem     = explode(',', $dataRateio->codigoEmpresaOrigem);

            // Identifica os centros de custo para lançamento de contrapartida
            $centroCustoOrigem  = explode(',', $dataRateio->codigoCentroCustoOrigem);

            // Inicializa as variáveis de valor a apropriar e dos lançamentos de contrapartida a serem gravados
            $valorApropriar             = 0;
            $lancamentoContraPartida    = [];

            // Gerar os lançamentos de contrapartida para cada uma das empresas e centros de custo
            // Processa todas empresas de origem
            foreach ($empresasOrigem as $codigoEmpresaOrigem) {
                
                // Processa todos os centros de custo de origem
                foreach ($centroCustoOrigem as $codigoCentroCustoOrigem) {

                    // Apura os valores da Origem do Rateio
                    $rateioOrigem   = $this->parametro->valorOrigem($this->periodoCorrente->mes,
                                                                    $this->periodoCorrente->ano,
                                                                    $codigoEmpresaOrigem,
                                                                    $codigoCentroCustoOrigem,
                                                                    $dataRateio->codigoContaGerencialOrigem);
                    if (!empty($rateioOrigem)) {
                        $historicoLancamento  = $this->historico['historicoPadrao'].' CONTRAPARTIDA | ';
                        if ($this->historico['incremental'] == 'S') {
                            $historicoLancamento .= 'EMP. ORIGEM: '.$codigoEmpresaOrigem;
                            $historicoLancamento .= ' | C.CUSTO ORIGEM: '.$codigoCentroCustoOrigem;
                        }

                        foreach ($rateioOrigem as $row => $valores) {
                            $lancamentoContraPartida[]  = [ 'mesLancamento'         => $this->periodoCorrente->mes, 
                                                            'anoLancamento'         => $this->periodoCorrente->ano, 
                                                            'idEmpresa'             => $codigoEmpresaOrigem,
                                                            'centroCusto'           => $codigoCentroCustoOrigem,
                                                            'idContaGerencial'      => $dataRateio->codigoContaGerencialOrigem,
                                                            'creditoDebito'         => ($valores->valorOrigem > 0 ? 'DEB' : 'CRD'),
                                                            'valorLancamento'       => ($valores->valorOrigem * -1),
                                                            'historicoLancamento'   => $historicoLancamento,
                                                            'idTipoLancamento'      => 7,   /* PARÂMETRO RATEIO */
                                                            'codigoContaContabil'   => NULL];
                                                            //'codigoContaContabil'   => $valores->codigoContaContabil];

                            // Acumula o valor total da origem a ser rateado
                            $valorApropriar += $valores->valorOrigem;
                        }
                    }
                }
            }

            // Registra os lancamentos de contrapartida
            $this->lancamentoGerencial->gravaLancamento($lancamentoContraPartida);

            // Processa os dados de destino para cálculo do valor a ser apropriado
            // {["column": "nome_da_coluna", "operator": "simbolo_operacao-ex: =, <>, ...", "value": "valor_filtro"], [...]}
            $criterios  = [
                            ['column'   => 'G3_gerencialLancamentos.idEmpresa', 'operator'  => 'IN', 'value' => explode(',', $dataRateio->codigoEmpresaDestino)],
                            ['column'   => 'G3_gerencialLancamentos.idContaGerencial', 'operator'  => '=', 'value' => $dataRateio->codigoContaGerencialDestino],
                            ['column'   => 'G3_gerencialLancamentos.centroCusto', 'operator'  => 'IN', 'value' => explode(',', $dataRateio->codigoCentroCustoDestino)],
                            ['column'   => 'G3_gerencialLancamentos.idTipoLancamento', 'operator'  => '<>', 'value' => 7]
                        ];
            // Calcula os valores para a base de cálculo
/*             $valoresBaseCalculo = $this->basesCalculo->calculaBases($this->periodoCorrente->mes,
                                                                    $this->periodoCorrente->ano); */

// Calcula os valores para a base de cálculo a partir da(s) empresa(s)
// e centro(s) de custo de destino
             $valoresBaseCalculo = $this->basesCalculo->calculaBases($this->periodoCorrente->mes,
                                                                    $this->periodoCorrente->ano,
                                                                    $dataRateio->codigoEmpresaDestino,
                                                                    $dataRateio->codigoCentroCustoDestino);

            // Registra os lançamentos de rateio nos destinos
            // Valor a Apropriar * Peso (empresa / centro de custo)
            foreach ($this->lancamentoGerencial->getLancamentosRateio(  $this->periodoCorrente->mes, 
                                                                        $this->periodoCorrente->ano,
                                                                        $dataRateio->codigoEmpresaDestino,
                                                                        $dataRateio->codigoCentroCustoDestino ) as $row => $destino) {



                // Se existir base de cáculo para o Parâmetro de Rateio (Empresa e Centro Custo)
                // processa o rateio e registra o lançamento
                if (isset($valoresBaseCalculo[$dataRateio->idBaseCalculo]['EMPRESA'][$destino->codigoEmpresa]['CENTRO_CUSTO'][$destino->codigoCentroCusto]['PESO_EMPRESA'])) {

/*                     $pesoCentroCusto = $valoresBaseCalculo[$dataRateio->idBaseCalculo]
                                                            ['EMPRESA'][$destino->codigoEmpresa]
                                                            ['CENTRO_CUSTO'][$destino->codigoCentroCusto]['PESO_EMPRESA'];
 */
                        $pesoCentroCusto = $valoresBaseCalculo[$dataRateio->idBaseCalculo]
                                                                ['EMPRESA'][$destino->codigoEmpresa]
                                                                ['CENTRO_CUSTO'][$destino->codigoCentroCusto]['PESO_TOTAL'];

                    $valorRateio        = ($valorApropriar * $pesoCentroCusto);

                    if ($valorRateio <> 0) {
                        $historicoRateio  = $this->historico['historicoPadrao'].' RATEIO | ';
                        if ($this->historico['incremental'] == 'S') {
                            $historicoRateio .= 'EMP. ORIGEM: '.$dataRateio->codigoEmpresaOrigem;
                            $historicoRateio .= ' | C.CUSTO ORIGEM: '.$dataRateio->codigoCentroCustoOrigem;

                            $historicoRateio .= ' | VALOR A APROPRIAR: '.number_format($valorApropriar,0,',','.');
                            $historicoRateio .= ' | PESO APLICADO: '.number_format($pesoCentroCusto,10,',','.');
                        }

                        // Registra o lancamento de rateio
                        $lancamentoRateio = [ 'mesLancamento'         => $this->periodoCorrente->mes, 
                                                'anoLancamento'         => $this->periodoCorrente->ano, 
                                                'idEmpresa'             => $destino->codigoEmpresa,
                                                'centroCusto'           => $destino->codigoCentroCusto,
                                                'idContaGerencial'      => $dataRateio->codigoContaGerencialDestino,
                                                'creditoDebito'         => ($valorRateio > 0 ? 'DEB' : 'CRD'),
                                                'valorLancamento'       => ($valorRateio * ($valorRateio > 0 ? -1 : 1)),
                                                'historicoLancamento'   => $historicoRateio,
                                                'idTipoLancamento'      => 7,   /* PARÂMETRO RATEIO */
                                                //'codigoContaContabil'   => $destino->codigoContaContabilERP];
                                                'codigoContaContabil'   => NULL];


                        if (!empty($lancamentoRateio)) {
                            DB::enableQueryLog();
                            if (!$this->lancamentoGerencial->gravaLancamento([$lancamentoRateio])) {
                                $queryLog = DB::getQueryLog();
                                $this->erros = $this->lancamentoGerencial->errors;
/*    echo 'ERRO GRAVAÇÃO';
    echo '<br>'.$valorRateio;
    print_r($this->lancamentoGerencial->errors);
    dd($lancamentoRateio);
  */                              /* 
                                $this->errors[] = ['errorTitle' => "Código Empresa", 'error' => $destino->codigoEmpresa]; 
                                $this->errors[] = ['errorTitle' => "Histórico", 'error' => $historicoRateio];
                                $this->errors[] = ['errorTitle' => "Valor a Apropriar", 'error' => $valorApropriar];
                                $this->errors[] = ['errorTitle' => "Peso", 'error' => $pesoCentroCusto];
                                $this->errors[] = ['errorTitle' => "Valor Rateio", 'error' => ($valorRateio * ($valorRateio > 0 ? -1 : 1))];
                                $this->errors[] = ['errorTitle' => "Query", 'error' => $queryLog[0]['query']]; */

                                return FALSE;
                            }
                        }
                    }
                }
            }   //-- foreach Registro de Lançamentos
        }   //-- Rateio por peso

        return TRUE;

        // Exibe mensagem de conclusão do processamento dos parâmetros
        #return ("<span id='showMsg' data-title='PROCESSAMENTO DOS PARÂMETROS'
        #                            data-message='Parâmetros de Rateio processados com sucesso! '></span>");
    }   //-- parametroRateio_Peso


    /**
     *  PROCESSA OS PARÂMETROS DE RATEIO UTILIZANDO UMA TABELA DE RATEIO
     * 
     */
    public function parametroRateio_Tabela() {
        // RATEIOS POR TABELA
        $rateioTabela   = GerencialParametroRateio::where('parametroAtivo', 'S')
                                                    ->where('formaAplicacao', 'TBLA')
                                                    ->get();
        // PROCESSA OS RATEIOS POR PESO DE CONTA
        // EM REALÇÃO À BASE  DE CÁLCULO
        foreach ($rateioTabela as $row => $dataRateio) {

            // Identifica a tabela de rateio
            $tabelaRateio       = $this->tabelaRateio->getTabela($dataRateio->idTabelaRateio);

            // Identifica as empresas para o lançamento de contrapartida
            $empresasOrigem     = explode(',', $dataRateio->codigoEmpresaOrigem);

            // Identifica os centros de custo para lançamento de contrapartida
            $centroCustoOrigem  = explode(',', $dataRateio->codigoCentroCustoOrigem);

            // Inicializa as variáveis de valor a apropriar e dos lançamentos de contrapartida a serem gravados
            $valorApropriar             = 0;
//            $lancamentoContraPartida    = [];
            $contrapartida              = [];

            // Gerar os lançamentos de contrapartida para cada uma das empresas e centros de custo
            // Processa todas empresas de origem
            foreach ($empresasOrigem as $codigoEmpresaOrigem) {
                
                // Processa todos os centros de custo de origem
                foreach ($centroCustoOrigem as $codigoCentroCustoOrigem) {
                    // Apura os valores da Origem do Rateio
                    $rateioOrigem   = $this->parametro->valorOrigem($this->periodoCorrente->mes,
                                                                    $this->periodoCorrente->ano,
                                                                    $codigoEmpresaOrigem,
                                                                    $codigoCentroCustoOrigem,
                                                                    $dataRateio->codigoContaGerencialOrigem);
                    if (!empty($rateioOrigem)) {

                        // Prepara o histórico para o lançamento de contrapartida
                        $historicoLancamento  = $this->historico['historicoPadrao'].' CONTRAPARTIDA |';
                        if ($this->historico['incremental'] == 'S') {
                            $historicoLancamento .= ' TABELA: '.strtoupper($tabelaRateio->descricao).' |';
                            $historicoLancamento .= ' EMP. ORIGEM: '.$codigoEmpresaOrigem;
                            $historicoLancamento .= ' | C.CUSTO ORIGEM: '.$codigoCentroCustoOrigem;
                        }


                        // Processa os dados para cálculo do valor de origem a ser apropriado
                        foreach ($rateioOrigem as $row => $valores) {
                            // Inclui os dados do lançamento para gravação
                            // Se a conta gerencial de origem não for informada
                            // registra o lançamento de contrapartida na conta gerencial de destino
                            if (empty($dataRateio->codigoContaGerencialOrigem)) {
                                $contaGerencialContrapartida = $dataRateio->codigoContaGerencialDestino;
                            }
                            else {
                                $contaGerencialContrapartida = $dataRateio->codigoContaGerencialOrigem;
                            }

                            $contrapartida[]    = [ 'empresa'           => $codigoEmpresaOrigem,
                                                    'centroCusto'       => $codigoCentroCustoOrigem,
                                                    'contaGerencial'    => $contaGerencialContrapartida,
                                                    'historico'         => $historicoLancamento,
                                                    'valorOrigem'       => $valores->valorOrigem
                                                  ];
                            /* $lancamentoContraPartida[]  = [ 'mesLancamento'         => $this->periodoCorrente->mes, 
                                                            'anoLancamento'         => $this->periodoCorrente->ano, 
                                                            'idEmpresa'             => $codigoEmpresaOrigem,
                                                            'centroCusto'           => $codigoCentroCustoOrigem,
                                                            'idContaGerencial'      => $contaGerencialContrapartida,
                                                            'creditoDebito'         => ($valores->valorOrigem > 0 ? 'DEB' : 'CRD'),
                                                            'valorLancamento'       => ($valores->valorOrigem * -1),
                                                            'historicoLancamento'   => $historicoLancamento,
                                                            'idTipoLancamento'      => 7,   /* PARÂMETRO RATEIO * /
                                                            'codigoContaContabil'   => NULL];
                                                            //'codigoContaContabil'   => $valores->codigoContaContabil]; */

                            // Acumula o valor total da origem a ser rateado
                            $valorApropriar += $valores->valorOrigem;
                        }   //-- inclusão do lancamento de contrapartida
                    } //-- Validação do valor de origem vazio
                }   //-- Contrapartidas por empresa e centro de custo
            }   // Empresas de origem

            // Registra os lancamentos de contrapartida
//            $this->lancamentoGerencial->gravaLancamento($lancamentoContraPartida);

            /***** LANÇAMENTOS DE RATEIO ******/

            // Identifica as empresas de destino para aplicação do rateio
            $empresasDestino    = explode(',', $dataRateio->codigoEmpresaDestino);

            // Identifica os centros de custo de destino para aplicação do rateio
            $centroCustoDestino = explode(',', $dataRateio->codigoCentroCustoDestino);

            $lancamentoRateio       = [];
            $percentualTotalRateio  = 0;

            // Processa as empresas de destino
            foreach ($empresasDestino as $codigoEmpresa) {
                
                // Processa os centros de custo para cada empresa
                foreach ($centroCustoDestino as $codigoCentroCusto) {
                    // Identifica o percentual para o centro de custo
//                    $percentualCCusto   = $this->tabelaPercentuais->getPercentuais($dataRateio->idTabelaRateio, $codigoCentroCusto);
                    $percentualCCusto   = $this->tabelaPercentuais->getPercentuais($codigoEmpresa, $dataRateio->idTabelaRateio, $codigoCentroCusto);

                    if($percentualCCusto === FALSE) {
                        $nomeEmpresa        = GerencialEmpresas::find($codigoEmpresa);
                        $tabelaReferencia   = GerencialTabelaRateio::find($dataRateio->idTabelaRateio);
                        $centroCusto        = GerencialCentroCusto::find($codigoCentroCusto);

                        $this->errors[] = ['errorTitle' => "TABELA DE REFERÊNCIA", 'error' => 'Não foram encontrados valores na tabela de referência para:
                                                                                                <ul>
                                                                                                    <li>PARÂMETRO DE RATEIO: '.($dataRateio->descricaoParametro ?? "Não Identificado").'</li>
                                                                                                    <li>TABELA DE REFERENCIA: ['.$dataRateio->idTabelaRateio.']'.($tabelaReferencia->descricao ?? "Não Identificado").'</li>
                                                                                                    <li>EMPRESA: ['.$codigoEmpresa.']'.($nomeEmpresa->nomeAlternativo ?? "Não Identificada").'</li>
                                                                                                    <li>CENTRO DE CUSTO: ['.$codigoCentroCusto.']'.($centroCusto->descricaoCentroCusto ?? "Não Identificado").'</li>
                                                                                                </ul>'
                                                                                                ];
                        return FALSE;
                    }

                    // Calcula o valor do rateio de acordo com percentual do centro de custo
                    $valorRateio            = $valorApropriar * ($percentualCCusto->percentual / 100);
                    $percentualTotalRateio  += $percentualCCusto->percentual;

                    // Prepara o histórico do lançamento
                    $historicoLancamento  = $this->historico['historicoPadrao'].' RATEIO | ';
                    if ($this->historico['incremental'] == 'S') {
                        $historicoLancamento .= ' TABELA: '.strtoupper($tabelaRateio->descricao).' |';
                        $historicoLancamento .= ' EMP. ORIGEM: '.$codigoEmpresa;
                        $historicoLancamento .= ' | C.CUSTO ORIGEM: '.$codigoCentroCusto;
                        $historicoLancamento .= ' | VR. ORIGEM: '.number_format($valorApropriar,2,',','.');
                        $historicoLancamento .= ' | % C.CUSTO NA TABELA: '.number_format($percentualCCusto->percentual,2,',','.').'%';
                    }

                    // Inclui os dados do lançamento para gravação
                    $lancamentoRateio[] = [ 'mesLancamento'         => $this->periodoCorrente->mes, 
                                            'anoLancamento'         => $this->periodoCorrente->ano, 
                                            'idEmpresa'             => $codigoEmpresa,
                                            'centroCusto'           => $codigoCentroCusto,
                                            'idContaGerencial'      => $dataRateio->codigoContaGerencialDestino,
                                            'creditoDebito'         => ($valorRateio > 0 ? 'DEB' : 'CRD'),
                                            'valorLancamento'       => $valorRateio,
                                            'historicoLancamento'   => $historicoLancamento,
                                            'idTipoLancamento'      => 7,   /* PARÂMETRO RATEIO */
                                            'codigoContaContabil'   => NULL];
                }
            }

            // Registra os lancamentos de contrapartida
            $this->lancamentoGerencial->gravaLancamento($lancamentoRateio);

            // PROCESSA AS CONTRAPARTIDAS DE ACORDO COM OS VALORES RATEADOS
            $lancamentoContraPartida    = [];

            foreach ($contrapartida as $idx => $dataLancamento) {
                
                // Calcula o valor da contrapartida a partir do percentual total do rateio
                // Invertendo o sinal do valor
                $valorContrapartida = ($dataLancamento['valorOrigem'] * ($percentualTotalRateio / 100)) * -1;

                // Prepara os dados para registro do lançamento
                $lancamentoContraPartida[]  = [ 'mesLancamento'         => $this->periodoCorrente->mes, 
                                                'anoLancamento'         => $this->periodoCorrente->ano, 
                                                'idEmpresa'             => $dataLancamento['empresa'],
                                                'centroCusto'           => $dataLancamento['centroCusto'],
                                                'idContaGerencial'      => $dataLancamento['contaGerencial'],
                                                'creditoDebito'         => ($valorContrapartida > 0 ? 'DEB' : 'CRD'),
                                                'valorLancamento'       => $valorContrapartida,
                                                'historicoLancamento'   => $dataLancamento['historico'],
                                                'idTipoLancamento'      => 7,   /* PARÂMETRO RATEIO */
                                                'codigoContaContabil'   => NULL];                
            }
            // Grava os lançamentos de contrapartida
            $this->lancamentoGerencial->gravaLancamento($lancamentoContraPartida);

        }   //-- foreach RATEIO TABELA

        return TRUE;
    }   //-- parametroRateio_Tabela


    /**
     *  rateioLogistica
     *  Processa o cálculo do rateio da logística
     */
    public function rateioLogistica() {
        set_time_limit(0);

        $resultadoLiquido = $this->lancamentoGerencial->resultadoLiquido(['mes'=> $this->periodoCorrente->mes, 'ano' => $this->periodoCorrente->ano]);

        $dataRateio = $this->rateios->rateioLogistica($this->periodoCorrente->mes, $this->periodoCorrente->ano);

        // Calcula o total geral de veículos vendidos
        $totalVendidos  = 0;
        $vendidos       = [];
        $valorVeiculo   = 0;
        foreach ($dataRateio as $idx => $dataVendidos) {
            $totalVendidos  += $dataVendidos->veiculosVendidos;

            if (!isset($vendidos[$dataVendidos->estoque]))  $vendidos[$dataVendidos->codigoEmpresa][$dataVendidos->estoque] = 0;
            $vendidos[$dataVendidos->codigoEmpresa][$dataVendidos->estoque]   += $dataVendidos->veiculosVendidos;
        }

        // Calcula o valor por veículo em razão do total de veículos vendidos e o resultado líquido total
        $valorVeiculo       = $resultadoLiquido['TOTAL'] / $totalVendidos;

        $valorRateioVN   = [];
        foreach ($dataRateio as $idx => $dataVendidos) {
            if (isset($resultadoLiquido[$dataVendidos->codigoEmpresa])) {
                $valorRateio[$dataVendidos->codigoEmpresa][$dataVendidos->estoque] = $valorVeiculo * $vendidos[$dataVendidos->codigoEmpresa][$dataVendidos->estoque];
                $valorRateio[$dataVendidos->codigoEmpresa]['HISTORICO']  = "VALOR/VEÍCULO: ".number_format($valorVeiculo,2,',','.');
                $valorRateio[$dataVendidos->codigoEmpresa]['HISTORICO'] .=" | VEÍCULOS VENDIDOS: ".$vendidos[$dataVendidos->codigoEmpresa][$dataVendidos->estoque];
            }
        }

        // Identifica a conta que recebe os valores do rateio da logística
        $dataConta      = GerencialContaGerencial::where('rateioLogistica', 'S')->get();
        $idContaRateio  = $dataConta[0]->id ?? NULL;

        // Prepara os dados para gravar os lançamentos de rateio
        $lancamentoRateio   = [];
        foreach ($valorRateio as $empresa => $rateio) {
            // Identifica a regional da empresa
            $dataEmpresa    = GerencialEmpresas::where('codigoEmpresaERP', $empresa)->get();

            // Identifica a empresa para alocação dos valores da logística
            $dataRegional   = GerencialRegional::where('id', $dataEmpresa[0]->codigoRegional)->get();

            // Identifica o histórico do tipo de lançamento
            $dataHistorico  = GerencialTipoLancamento::find(5);
            $historico      = $dataHistorico->historicoTipoLancamento.($dataHistorico->historicoIncremental == 'S' ? $rateio['HISTORICO'] : '');

            // Processa os valores por centro de custo (VN / VD)
            foreach ($rateio as $centroCusto => $valor) {
                if ((int) $valor <> 0 ) {
                    $lancamentoRateio[] = [ 'anoLancamento'         => $this->periodoCorrente->ano,
                                            'mesLancamento'         => $this->periodoCorrente->mes,
                                            'codigoContaContabil'   => NULL,
                                            //'idEmpresa'             => $dataRegional[0]->codigoEmpresaRateioLogistica,
                                            'idEmpresa'             => $dataEmpresa[0]->id,
                                            'centroCusto'           => ($centroCusto == 'VN' ? 1 : 15), // 1. VN, 15. VD
                                            'idContaGerencial'      => $idContaRateio,
                                            'creditoDebito'         => ($valor < 0 ? 'CRD' : 'DEB'),
                                            'valorLancamento'       => number_format(((float) $valor * -1),2,'.',''),
                                            'idTipoLancamento'      => 5,       // [L] RATEIO LOGÍSTICA
                                            'historicoLancamento'   => $historico];
                }

            }
        }

        // Exclui os lancamentos de Rateio da Logística caso existam
        $deleteRateio   = [["fieldName" => 'idTipoLancamento', "fieldComparison" => "=", "values" => 5]];
        $this->lancamentoGerencial->deleteLancamentosGerenciais($deleteRateio);

        // Grava os lançamentos gerenciais
        $this->errors   = [];
        $success        = FALSE;
        if ($this->lancamentoGerencial->gravaLancamento($lancamentoRateio)) {
            $this->errors[] = ['errorTitle' => "RATEIO LOGÍSTICA", 'error' => 'Processamento de Rateio realizado com sucesso!']; 
            $success        = TRUE;
        }
        else {
            $this->errors[] = ['errorTitle' => "RATEIO LOGÍSTICA", 'error' => 'OCORREU UM ERRO AO PROCESSAR O RATEIO, TENTE NOVAMENTE!']; 
        }

        return view('processamento.validacao', ['errors' => $this->errors, 'success' => $success]);
    }
}
