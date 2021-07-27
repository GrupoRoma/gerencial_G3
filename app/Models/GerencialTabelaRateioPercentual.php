<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GerencialTabelaRateioPercentual extends Model
{
    use HasFactory;

    protected $table    = 'gerencialTabelaRateioPercentual';
    protected $fillable = ['idTabela', 'idCentroCusto', 'percentual'];

    /**
     * Atualiza os dados dos centros de custos e seus respectivos percentuais
     * 
     *  @param  integer     Identificador da tabela de rateio
     *  @param  object      Dados do formulário para atualização da tabela
     * 
     *  @return boolean     TRUE
     */
    public function updatePercentuals(int $idTabela, object $dataPercentuais) {
        foreach ($dataPercentuais as $codigoCentroCusto => $percentual) {
            $found = $this->where('idTabela', $idTabela)->where('idCentroCusto', $codigoCentroCusto);

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

        return  TRUE;
    }

    /**
     *  Carrega os centros de custos e respectivos percentuais
     *  da tabela de rateio informada
     * 
     *  @param  int     ID da tabela de rateio
     *  @param  int     ID do centro de custo
     * 
     *  @return object  DB Data row
     * 
     */
    public function getPercentuais($idTabela, $idCentroCusto) {
        $dbData = $this->where('idTabela', $idTabela)->where('idCentroCusto', $idCentroCusto)->get();
        
        return $dbData[0];
    }

}
