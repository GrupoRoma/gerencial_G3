<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GerencialTabelaRateio extends Model
{
    use HasFactory;

    protected   $table      = 'gerencialTabelaRateios';
    protected   $guarded    = ['id'];

    public  $viewTitle      = 'Tabela de Referência';
    public  $columnList     = ['descricao', 'tabelaAtiva'];
    public  $columnAlias    = ['descricao' => 'Nome da Tabela de rateio', /*'idEmpresa' => 'Empresa',*/ 'tabelaAtiva' => 'Tabela de Rateio Ativa'];
    public  $columnValue    = [];  // ['empresaAtiva'               => ['S' => 'Sim', 'N' => 'Não']];
    public  $customType     = ['tabelaAtiva'   => ['type'      => 'radio', 'values'    => ['S' => 'Sim', 'N' => 'Não']]];
    public  $rules          = ['descricao'  => 'required', /*'idEmpresa'  => 'required',*/ 'tabelaAtiva' => 'required'];
    public $rulesMessage    = [ 'descricao'     => 'NOME DA TABELA DE RATEIO: Obrigatório',
                                /*'idEmpresa'     => 'EMPRESA: Obrigatório',*/
                                'tabelaAtiva'   => 'TABELA DE RATEIO ATIVA: Obrigatório'
                            ];
 
    public function fk_gerencialEmpresas($columnValueName = 'id') {
        $fkData = GerencialEmpresas::orderBy('nomeAlternativo')->get();

        $formValues = [];
        foreach($fkData as $row => $data) {
            $formValues[] = [$data->{$columnValueName}, $data->nomeAlternativo];
        }

        return ['options' => $formValues, 'type' => '']; 
    }
    
    /**
    * Retona os centros de custo associados à tabela de rateio
    */
    public function gerencialTabelaRateioPercentual() {
        return $this->hasMany('App\Models\GerencialTabelaRateioPercentual', 'idTabela');
    }

    public function vd_gerencialEmpresas($id) {
        $viewData = GerencialEmpresas::where('id', $id)->get();

        foreach ($viewData as $row => $data) {
            return $data->nomeAlternativo;
        }
    }

    /**
     *  Retorna dos dados da tabela de rateio informada
     * 
     *  @param  int     ID da tabela de rateio
     * 
     *  @return object  DB Row data
     * 
     */
    public function getTabela($id) {
        $dbData = $this->find($id);

        return $dbData;
    }
    
}
