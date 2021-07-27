<?php

namespace App\Models\Processos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\GerencialAmortizacao;

class GerencialExcecoes extends Model
{
    use HasFactory;

    private $listaExcecoes;

    /**
     *  getOutrasContas
     *  retorna as exceções de contas contábeis a serem importadas
     * 
     *  @param  integer     codigo da regional que está sendo processada a importação
     * 
     *  @return mixed       FALSE: Nenhuma conta cadastrada ou DBData (lista de outras contas)
     * 
     */
    public function getOutrasContas($codigoRegional = NULL) {
        
        $filtroRegional = '';
        if (!empty($codigoRegional))    $filtroRegional = "AND G3_gerencialEmpresas.codigoRegional = '$codigoRegional'";

        $dbData     = DB::select("SELECT codigoEmpresaOrigem    = G3_gerencialOutrasContas.codigoEmpresaERP,
                                         codigoRegional         = G3_gerencialEmpresas.codigoRegional,
                                         contaContabilOrigem    = G3_gerencialOutrasContas.codigoContaContabilERP,
                                         saldoOrigem            = G3_gerencialOutrasContas.percentualSaldo,
                                         historicoLancamento    = G3_gerencialOutrasContas.historicoPadrao,
                                         destinoJson            = G3_gerencialOutrasContas.destino
                                  FROM GAMA..G3_gerencialOutrasContas   (nolock)
                                  JOIN GAMA..G3_gerencialEmpresas       (nolock) ON G3_gerencialEmpresas.codigoEmpresaERP   = G3_gerencialOutrasContas.codigoEmpresaERP
                                  WHERE G3_gerencialOutrasContas.outrasContasAtivo = 'S'
                                  $filtroRegional
                                  ORDER BY G3_gerencialOutrasContas.codigoEmpresaERP");

        // Se forem encontradas outras contas para importação retorna FALSO
        if (count($dbData) == 0)    return FALSE;
        else {
            foreach ($dbData as $row => $data) {
                $datajSon = json_decode($data->destinoJson);

                $outrasContas[]    = ['codigoRegional'          => $data->codigoRegional,
                                    'codigoEmpresaOrigem'       => $data->codigoEmpresaOrigem,
                                    'codigoContaContabilOrigem' => $data->contaContabilOrigem,
                                    'percentualSaldoOrigem'     => $data->saldoOrigem,
                                    'codigoEmpresaDestino'      => $datajSon->empresaDestino,
                                    'percentualSaldoDestino'    => $datajSon->proporcaoDestino,
                                    'codigoCentroCustoDestino'  => $datajSon->centroCustoDestino];
            }

            return $outrasContas;
        }
    }

    /**
     *  getAmortizacoes
     *  retorna todas as amortizacoes ativas e que ainda estejam em aberto (parcelasAmortizadas < numeroParcelas)
     * 
     *  @return mixed   dbData | FALSE: nenhuma amortização cadastrada
     * 
     */
    public function getAmortizacoes() {
        $dbData = GerencialAmortizacao::where('amortizacaoAtiva', 'S')
                                      ->whereRaw('numeroParcelas > parcelasAmortizadas')
                                      ->orWhereNull('parcelasAmortizadas')
                                      ->orderBy('created_at')
                                      ->get();

        if ($dbData->count() == 0)    return FALSE;
        else                        return $dbData;
    }
}
