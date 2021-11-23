<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GerencialTabelaRateioPercentual extends Model
{
    use HasFactory;

    protected $table    = 'gerencialTabelaRateioPercentual';
    protected $fillable = ['idTabela', 'idEmpresa', 'idCentroCusto', 'percentual'];

    /**
     * Atualiza os dados dos centros de custos e seus respectivos percentuais
     * 
     *  @param  integer     Identificador da tabela de rateio
     *  @param  object      Dados do formulário para atualização da tabela
     *                      {codigoEmpresa: {centroCusto: percentual, centrocusto: percentual, ...}}
     * 
     *  @return boolean     TRUE
     */
    public function updatePercentuals(int $idTabela, object $dataPercentuais) {

        // Percorre as empresas informadas
        foreach ($dataPercentuais as $codigoEmpresa => $centroCusto) {
            // Percorre os centros de custo e percentuais
            foreach ($centroCusto as $codigoCentroCusto => $percentual) {
                $found = $this->where('idTabela', $idTabela)
                              ->where('idEmpresa', $codigoEmpresa)
                              ->where('idCentroCusto', $codigoCentroCusto);

                // Exclui centro de custo
                if($percentual == '[DELETE]') {
                    $deleted  = $this->where('idTabela', $idTabela)
                                    ->where('idEmpresa', $codigoEmpresa)
                                    ->where('idCentroCusto', $codigoCentroCusto)
                                    ->delete();
                }
                // Atualiza ou inclui os dados da tabela de rateio
                else {
                    $this->updateOrCreate([ 'idTabela'      => $idTabela,
                                            'idEmpresa'     => $codigoEmpresa, 
                                            'idCentroCusto' => $codigoCentroCusto, 
                                            'percentual'    => $percentual]);
                }
            }
        }


/*         foreach ($dataPercentuais as $codigoCentroCusto => $percentual) {
            $found = $this->where('idTabela', $idTabela)
                          ->where('idCentroCusto', $codigoCentroCusto);
                          ->where('idCentroCusto', $codigoCentroCusto);

            // Exclui centro de custo
            if($percentual == '[DELETE]') {
                $deleted  = $this->where('idTabela', $idTabela)
                                 ->where('idCentroCusto', $codigoCentroCusto)
                                 ->delete();
            }
            else {
                $this->updateOrCreate([ 'idTabela'      => $idTabela,
                                        'idCentroCusto' => $codigoCentroCusto, 
                                        'percentual'    => $percentual]);
            }
        }
 */
        return  TRUE;
    }

    /**
     *  Carrega os centros de custos e respectivos percentuais
     *  da tabela de rateio informada
     * 
     *  @param  int     ID da empresa de destino
     *  @param  int     ID da tabela de rateio
     *  @param  int     ID do centro de custo
     * 
     *  @return object  DB Data row
     * 
     */
    public function getPercentuais($idEmpresa, $idTabela, $idCentroCusto) {
        $dbData = $this->where('idEmpresa', $idEmpresa)
                        ->where('idTabela', $idTabela)
                        ->where('idCentroCusto', $idCentroCusto)
                        ->get();
        
        return $dbData[0];
    }

}
