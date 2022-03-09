<?php

namespace App\Http\Controllers\Processos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Processos\TVI;
use App\Models\GerencialPeriodo;
use App\Models\GerencialLancamento;
use App\Models\GerencialTipoLancamento;
use App\Models\GerencialEmpresas;
use App\Models\GerencialCentroCusto;

class TVIController extends Controller
{
    protected   $titulo     = "PROCESSAMENTO DAS TVI's REGISTRADAS";
    protected   $tvi;
    protected   $periodo;
    protected   $lancamento;
    protected   $tipoLancamento;

    protected   $historico;
    protected   $centroCusto;
    protected   $empresa;

    public function __construct() 
    {
        $this->tvi              = new TVI;
        $this->lancamento       = new GerencialLancamento;
        $this->periodo          = new GerencialPeriodo;
        $this->tipoLancamento   = new GerencialTipoLancamento;
        $this->centroCusto      = new GerencialCentroCusto;
        $this->empresa          = new GerencialEmpresas;
        
        // Carrega as definições do Tipo de Lançamento [TVI] Código 4
        $this->historico = $this->tipoLancamento->getHistoricoLancamento(4);
    }

    /**
     *  Registra os lançamentos de TVI - Transferência de Valores Internos
     * 
     *  As transferências que forem registradas no SGA serão processadas com o seguinte critério:
     * 
     *  1. Todas as TVI's na situação 'A - Aprovadas' serão importadas sem considerações
     *  2. Todas as TVI's na situação 'P - Pendentes' serão aprovadas sumariamente no ato do registro das TVI's no gerencial
     *  3. Todas as TVI's na situação 'N - Negadas' não serão processadas
     * 
     *  As TVI's aprovadas sumariamente terão sua situação alterada para 'S - Aprovação Sumária'
     * 
     *  @param  Request NULL
     * 
     *  @return Response    (mensagem de execução do registro dos lançamentos de TVI no gerencial) 
     */
    public function lancamentosTVI(Request $request)
    {
        // Verifica se existem períodos Abertos para lançamento [AB | LC]
        $this->periodo->setCheckSituacao('AB');
        $periodo = $this->periodo->checkPeriodo();

        // Valida os periodos
        if (!$periodo) {
            return ("<span id='showMsg' data-title='PERÍODO GERENCIAL'
                                                    data-message='".$this->periodo->errors."'></span>");
        }

        // Encontrou apenas um período aberto e segue o fluxo normal
        return view('processamento.processaLancamento', ['tituloPagina'     => $this->titulo,
                                                   'infoPagina'       => "Registra os lançamentos gerenciais referentes às TVI's não negadas.
                                                                            <ol>
                                                                                <li>Todas as TVI's na situação 'A - Aprovadas' serão registradas no gerencial</li>
                                                                                <li>Todas as TVI's na situação 'P - Pendentes' serão aprovadas sumariamente e registradas no gerencial</li>
                                                                                <li class='tw-7'>Todas as TVI's na situação 'N - Negadas' não serão processadas</li>
                                                                            </ol>
                                                                            As TVI's aprovadas sumariamente terão sua situação alterada para 'S - Aprovação Sumária'",
                                                   'action'           => 'registraTVI',
                                                   'buttonActionText' => "PROCESSAR REGISTRO DE TVI's",
                                                   'mes'              => ($periodo[0]['mes'] ?? null), 
                                                   'ano'              => ($periodo[0]['ano'] ?? null)]);
    }

    /**
     * Processa o registro dos lançamentos gerenciais das TVI's encontradas
     * 
     */
    public function registraTVI()
    {
        // INCIALIZA A TRANSAÇÃO COM O BANCO DE DADOS
        DB::beginTransaction();

        // Verifica e define o período ativo
        $periodo = $this->periodo->checkPeriodo();
        $this->periodo->setPeriodo($periodo[0]['mes'], $periodo[0]['ano']);

        //  Carrega as TVI's registradas para o período
        //dd($tviData = $this->tvi->getTVI($this->periodo->mesAtivo, $this->periodo->anoAtivo));
        $tviData = $this->tvi->getTVI($this->periodo->mesAtivo, $this->periodo->anoAtivo);

        /**
         * Verifica se existem lançamentos gerenciais referentes à TVI
         * registrados no período ativo
         * 
         * TIPO DE LANÇAMENTO = 4: [I] TRANSFERÊNCIA DE VALORES INTERNOS
         */
        $this->lancamento->deleteLancamentosGerenciais([['fieldName' => 'idTipoLancamento', 'values' => 4]]);

        $lancamentosContraPartida   = [];
        $lancamentosTVI             = [];
        $tviUpdate                  = [];
        foreach ($tviData as $row => $data) {
            // Nome do centro de custo
            $origemCentroCusto  = $this->centroCusto->getCentroCustoCodigoERP($data->codigoCentroCustoOrigem);
            $destinoCentroCusto = $this->centroCusto->getCentroCustoCodigoERP($data->codigoCentroCustoDestino);

            //Nome da empresa
            $origemEmpresa      = $this->empresa->getEmpresaERP($data->codigoEmpresaOrigem);
            $destinoEmpresa     = $this->empresa->getEmpresaERP($data->codigoEmpresaDestino);
            
            $historicoOrigem    = "DESTINO: ".$destinoEmpresa->nomeAlternativo.
                                    " / ".$destinoCentroCusto->siglaCentroCusto." - ".$destinoCentroCusto->descricaoCentroCusto.
                                    " | VALOR: ".number_format($data->valor,2,',','.');

            $historicoDestino   = "ORIGEM: ".$origemEmpresa->nomeAlternativo.
                                    " / ".$origemCentroCusto->sigleCentroCusto." - ".$origemCentroCusto->descricaoCentroCusto.
                                    " | VALOR: ".number_format($data->valor,2,',','.');

            // Prepara os lançamentos de TVI
            // CRÉDITO NA ORIGEM
            $lancamentosContraPartida[] = [ 'anoLancamento'         => $this->periodo->anoAtivo,
                                            'mesLancamento'         => $this->periodo->mesAtivo,
                                            'codigoContaContabil'   => NULL,
                                            'idEmpresa'             => $data->idEmpresaOrigem,
                                            'centroCusto'           => $origemCentroCusto->id,  // data->codigoCentroCustoOrigem,
                                            'idContaGerencial'      => $data->codigoContaGerencial,
                                            'creditoDebito'         => 'CRD',
                                            'valorLancamento'       => $data->valor,
                                            'idTipoLancamento'      => 4,
                                            'historicoLancamento'   => $this->historico['historicoPadrao']." ".
                                                                        ($this->historico['incremental'] == 'S' ? $historicoOrigem : ''),
                                            'numeroDocumento'       => $data->codigoTvi];
            
            // DÉBITO NO DESTINO
            $lancamentosTVI[]   = [ 'anoLancamento'         => $this->periodo->anoAtivo,
                                    'mesLancamento'         => $this->periodo->mesAtivo,
                                    'codigoContaContabil'   => NULL,
                                    'idEmpresa'             => $data->idEmpresaDestino,
                                    'centroCusto'           => $destinoCentroCusto->id,     // $data->codigoCentroCustoDestino,
                                    'idContaGerencial'      => $data->codigoContaGerencial,
                                    'creditoDebito'         => 'DEB',
                                    'valorLancamento'       => ($data->valor * -1),
                                    'idTipoLancamento'      => 4,
                                    'historicoLancamento'   => $this->historico['historicoPadrao']." ".
                                                                ($this->historico['incremental'] == 'S' ? $historicoDestino : ''),
                                    'numeroDocumento'       => $data->codigoTvi];

            /**
             *  Registra os dados para atualização das TVI que foram aprovadas sumariamente
             * 
             *  Todas as TVI's que estiverem com situação igual a P: Pendente serão aprovadas sumariamente
             */
            if ($data->situacao == 'P') $tviUpdate[]  = ['codigoTVI'  => $data->codigoTvi];

        }

        /** 
         * GRAVA OS LANCAMENTOS DE CONTRAPARTIDA - CRÉDITO NA ORIGEM
         * 
         *  Caso ocorra erro na gravação dos dados, interrompe o processamento
         *  cancela a operação de gravação no banco de dados e retorna mensagem de erro
         */
        if (!$this->lancamento->gravaLancamento($lancamentosContraPartida)) {
            DB::rollBack();

            return ("<span id='showMsg' data-title='PROCESSAMENTO DE T.V.I.'
                                                    data-message='Erro no registro das TVI´s de contra partida [Crédito na Origem]'></span>");
        }

        /** 
         * GRAVA OS LANCAMENTOS DE TRANSFERÊNCIA - DÉBITO NO DESTINO
         * 
         *  Caso ocorra erro na gravação dos dados, interrompe o processamento
         *  cancela a operação de gravação no banco de dados e retorna mensagem de erro
         */
        if (!$this->lancamento->gravaLancamento($lancamentosTVI)) {
            DB::rollBack();
            return ("<span id='showMsg' data-title='PROCESSAMENTO DE T.V.I.'
                                                    data-message='Erro no registro das TVI´s [Débito no Destino]'></span>");
        }

        /**
         *  Atualiza os registros de TVI aprovando sumariamente todas as TVI's pendentes
         * 
         */
        if (!empty($tviUpdate)) {
            if (!$this->tvi->aprovacaoSumaria($tviUpdate)) {
                DB::rollBack();
                return ("<span id='showMsg' data-title='PROCESSAMENTO DE T.V.I.'
                                                        data-message='Erro na atualização das TVI´s. [Aprovação sumária]'></span>");
            }
        }

        // REGISTRA TODA A OPERAÇÃO NO BANCO DE DADOS APÓS TODOS OS PROCESSAMENTOS REFLIZADOS COM SUCESSO
        DB::commit();

        return ("<span id='showMsg' data-title='PROCESSAMENTO DE T.V.I.'
                                                    data-message='TVI´s processadas com sucesso!'></span>");

    }
}
