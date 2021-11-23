<?php

namespace App\Http\Controllers\Processos;

use App\Http\Controllers\Controller;
use App\Models\GerencialAmortizacao;
use App\Models\Processos\GerencialExcecoes;
use Illuminate\Http\Request;

use App\Models\Processos\ImportarContabilidade;
use App\Models\GerencialContaContabil;
use App\Models\GerencialLancamento;
use App\Models\GerencialPeriodo;

class GerencialExcessoesController extends Controller
{
    public  $errors;

    private $excecoes;
    private $importa;
    private $lancamento;
    private $periodo;

    public function __construct() 
    {
        $this->excecoes     = new GerencialExcecoes;
        $this->importa      = new ImportarContabilidade;
        $this->lancamento   = new GerencialLancamento;
        $this->periodo      = new GerencialPeriodo;
    }

    /**
     *  Processa a importação as exceções cadastradas como Outras Contas Contábeis
     *  [EXCEÇÔES > OUTRAS CONTAS CONTÁBEIS]
     * 
     *  como a importação de saldos de contas patrimoniais (por exemplo)
     * 
     *  @param  integer     codigoRegional
     *  @param  Illuminate\Http\Request request
     * 
     *  @return none
     */
    public function importaOutrasContas(int $codigoRegional, Request $request) {
        $dbContas = $this->excecoes->getOutrasContas($codigoRegional);

        if (!$dbContas) {
            $this->errors[] = ['errorTitle' => 'PROCESSAMENTO DE EXCEÇÔES | OUTRAS CONTAS', 'error'   => 'Não foram encontradas exceções de outras contas cadastradas para processamento.'];
            return view('processamento.validacao', ['errors' => $this->errors]);
        }
        else {

            // Determina o período ativo
            $this->periodo->setPeriodo($request->mesReferencia, $request->anoReferencia);

            // Processas as exceções para registro no gerencial
            $lancamentos = [];
            foreach ($dbContas as $row => $conta) {

                // [{"fieldName": fieldName, "fieldCriteria": [=,<>,>=,<=,...], "values": values, "andOr": 'AND [default]'}]
                $filterData[]   = ["fieldName" => "G3_gerencialRegional.id", "values" => $conta['codigoRegional']];
                $filterData[]   = ["fieldName" => "Lancamento.Lancamento_PlanoContaCod", "values" => $conta['codigoContaContabilOrigem']];
                
                // Carrega o saldo dos lançamentos contabeis para a exceção
                if (!$this->importa->getSaldoContabil($filterData)) {
                    $this->errors[] = ['errorTitle' => 'OUTRAS CONTAS [SALDO CONTÁBIL]', 'error'   => 'Não foram encontrados lançamentos para as exceções de outras contas'];
                    return view('processamento.validacao', ['errors' => $this->errors]);
                }

                foreach ($this->importa->dataLancamentos as $index => $saldo) {
                    /* Identifica a conta gerencial associada */
                    $dbContaGerencial = GerencialContaContabil::where('codigoContaContabilERP', $conta['codigoContaContabilOrigem'])
                                                            ->get();

                    /* Calcula a proporção do valor da origem a ser registrado */
                    $valorOrigem = $saldo->valorLancamento * ($conta['percentualSaldoOrigem'] / 100);

                    $lancamentos[]  = ['mesLancamento'         => $this->importa->mesAtivo,
                                        'anoLancamento'        => $this->importa->anoAtivo,
                                        'idEmpresa'            => $conta['codigoEmpresaDestino'],
                                        'centroCusto'          => $conta['codigoCentroCustoDestino'],
                                        'idContaGerencial'     => $dbContaGerencial[0]->idContaGerencial,
                                        'creditoDebito'        => $saldo->creditoDebito,
                                        'valorLancamento'      => $valorOrigem * ($conta['percentualSaldoDestino'] / 100),
                                        'historicoLancamento'  => '[EXCEÇÕES | OUTRAS CONTAS] '.$saldo->historicoLancamento.' R$ ORIGEM: '.number_format($valorOrigem,2,',','.'),
                                        'idTipoLancamento'     => $saldo->idTipoLancamento,
                                        'codigoContaContabil'  => $saldo->codigoContaContabil];
                }
            }

            // Exclui os lançamentos gerados anteriormente, caso o processamento esteja sendo executado novamente
            $this->lancamento->deleteLancamentosGerenciais([['fieldName' => 'idTipoLancamento', 'values' => 11],
                                                        ['fieldName' => 'historicoLancamento', 'fieldComparison' => 'like ', 'values' => "'%EXCEÇÕES | OUTRAS CONTAS%'"]]);

            // Grava os lançamentos de outras contas contábeis
            if ($this->lancamento->gravaLancamento($lancamentos)) {
                $this->errors[] = ['errorTitle' => 'OUTRAS CONTAS [SALDO CONTÁBIL]', 'error'   => 'Saldo importado com sucesso!'];
                return TRUE;
            }
            else {
                $this->errors[] = ['errorTitle' => 'OUTRAS CONTAS [SALDO CONTÁBIL]', 'error'   => 'Ocorreu um erro na importação do saldo de Outras Contas. verifique os parâmetros e tente novamente.'];
                return FALSE;
            }
        }
    } //-- importaOutrasContas --//


    /**
     *  Processa o regisro das amortizações cadastradas se existirem
     *  [EXCEÇÕES > AMORTIZAÇÕES ]
     *  
     *  @param  Illuminate\Http\Request     request
     * 
     */
    public function processaAmortizacao(Request $request) {
        
        $estornaAmortizacao = FALSE;

        // Determina o período ativo
        $this->periodo->setPeriodo($request->mesReferencia, $request->anoReferencia);

        // Carrega as amrotizações cadastradas, ativas e com parcelas amortizadas 
        // menor que o total de parcelas
        if (!$dbData = $this->excecoes->getAmortizacoes()) {
            $this->errors[] = ['errorTitle' => 'AMORTIZAÇÃO', 'error'   => 'Nenhuma amortização encontrada!'];
            return FALSE;
        } 
        else {

            // Verifica se existem lançamentos de amortização no período para
            // que sejam estornados os valores de saldo e parcelas amortizadas
            $filterLancamento = [['column' => 'G3_gerencialLancamentos.mesLancamento', 'value' => $this->importa->mesAtivo],
                                 ['column' => 'G3_gerencialLancamentos.anoLancamento', 'value' => $this->importa->anoAtivo],
                                 ['column' => 'G3_gerencialLancamentos.idTipoLancamento', 'value' => '11'],
                                 ['column' => 'G3_gerencialLancamentos.historicoLancamento', 'operator' => 'like ', 'value' => '%EXCEÇÔES | AMORTIZAÇÃO%']
                                ];
            $estornaAmortizacao = $this->lancamento->lancamentoExists($filterLancamento);

            $lancamentos        = [];
            foreach ($dbData as $row => $amortiza) {
                // verifica a forma de cálculo do valor da parcela
                if ($amortiza->tipoValor == 'PRP')  { 
                    // Valor Proporcional ao total de parcelas informadas
                    $valorParcela       = $amortiza->valorPrincipal / $amortiza->numeroParcelas;
                }
                else {
                    $valorParcela = $amortiza->valorParcela;
                }

                // Identifica para quais empresas devem ser rateados o valor da parcela
                $empresasDestino    = explode(',', $amortiza->empresasDestino);

                // Calcula o valor do rateio para cada empresa
                $parcelaEmpresa     = $valorParcela / count($empresasDestino);

                // Processa os lançamentos a serem gerados por empresa
                foreach ($empresasDestino as $index => $codigoEmpresa) {
                    $historico      = '[EXCEÇÔES | AMORTIZAÇÃO] '.$amortiza->historico;
                    $historico     .= ' - Principal=> '.number_format($amortiza->valorPrincipal,2,',','.').' | ';
                    $historico     .= 'Parcela: '.number_format($valorParcela,2,',','.').' / '.count($empresasDestino).' = '.number_format($parcelaEmpresa,2,',','.');

                    $lancamentos[]  = ['mesLancamento'         => $this->importa->mesAtivo,
                                        'anoLancamento'        => $this->importa->anoAtivo,
                                        'idEmpresa'            => $codigoEmpresa,
                                        'centroCusto'          => $amortiza->idCentroCusto,
                                        'idContaGerencial'     => $amortiza->idContaGerencialDestino,
                                        'creditoDebito'        => 'DEB',
                                        'valorLancamento'      => $parcelaEmpresa,
                                        'historicoLancamento'  => $historico,
                                        'idTipoLancamento'     => $amortiza->idTipoLancamento,
                                        'codigoContaContabil'  => NULL];
                }


                // Identifica o registro de amortização para atulização dos dados
                $updateAmortizacao = GerencialAmortizacao::find($amortiza->id);

                // Se a amortização para o período já tiver sido executada
                // faz o estorno dos valores registrados de Saldo e Número de Parcelas Amortizadas
                if ($estornaAmortizacao) {
                    $amortiza->parcelasAmortizadas  --;
                    $updateAmortizacao->saldoAmortizacao    = ($amortiza->valorPrincipal - ($valorParcela * ($amortiza->parcelasAmortizadas ?? 1)));
                    $updateAmortizacao->parcelasAmortizadas = $amortiza->parcelasAmortizadas;

                    $updateAmortizacao->save();
                }

                // Calcula o valor do saldo a amortizar
                $saldoAmortizacao   = $amortiza->valorPrincipal - ($valorParcela * (($amortiza->parcelasAmortizadas ?? 0)+1));

                // Atualiza os valores da amortização
                if (empty($updateAmortizacao->valorParcela)) $updateAmortizacao->valorParcela = $valorParcela;
                $updateAmortizacao->saldoAmortizacao    = $saldoAmortizacao;
                $updateAmortizacao->parcelasAmortizadas = ($amortiza->parcelasAmortizadas+1);

                $updateAmortizacao->save();

            }

            // Exclui os lançamentos gerados anteriormente, caso o processamento esteja sendo executado novamente
            $this->lancamento->deleteLancamentosGerenciais([['fieldName' => 'idTipoLancamento', 'values' => 11],
                                                        ['fieldName' => 'historicoLancamento', 'fieldComparison' => 'like ', 'values' => "'%EXCEÇÔES | AMORTIZAÇÃO%'"]]);

            // Registra os lançamentos gerenciais de amortização
            if ($this->lancamento->gravaLancamento($lancamentos)) {
                $this->errors[] = ['errorTitle' => 'AMORTIZAÇÃO', 'error'   => 'Amortizações processadas com sucesso!'];
                return TRUE;
            }
            else {
                $this->errors[] = ['errorTitle' => 'AMORTIZAÇÃO', 'error'   => 'Ocorreu um erro no processamento das amortizações. verifique as configurações e tente novamente.'];
                return FALSE;
            }
        }


    }
}
