<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GerencialParametroCentroCusto extends Model
{
    use HasFactory;

    protected   $table  = "gerencialParametroCentroCustos";

    protected $guarded      = ['id'];

    public $viewTitle       = 'Transferência de Centro de Custo';
    public $columnList      = ['idEmpresa', 'idCentroCustoOrigem',  'idCentroCustoDestino', 'parametroAtivo'];

    public $columnAlias     = [ 'idEmpresa'             => 'Empresa Vinculada',
                                'idCentroCustoOrigem'    => 'Centro de Custo de Origem',
                                'idCentroCustoDestino'  => 'Centro de Custo de Destino',
                                'parametroAtivo'        => 'Parâmetro Ativo'];

    public $columnValue     = ['parametroAtivo'         => ['S'  => 'Sim', 'N'  => 'Não']];
    public $customType      = ['parametroAtivo'         => ['type'      => 'radio', 'values'    => ['S' => 'Sim', 'N' => 'Não']]];

    public $rules           = [ 'idEmpresa'             => 'nullable',
                                'idCentroCustoOrigem'   => 'required', 
                                'idCentroCustoDestino'  => 'required', 
                                'parametroAtivo'        => 'required'];

    public $rulesMessage    = [ 'idCentroCustoOrigem'   => 'CENTRO DE CUSTO DE ORIGEM: Obrigatório',
                                'idCentroCustoDestino'  => 'CENTRO DE CUSTO DE DESTINO: Obrigatório',
                                'parametroAtivo'        => 'PARÂMETRO ATIVO: Obrigatório'
                            ];   

    /**
     * Retona o Centro de Custo associado
     */
    public function gerencialCentroCusto() {
        return $this->hasOne('App\Models\GerencialCentroCusto');
    }

    public function vd_gerencialCentroCusto($id) {
        $viewData = GerencialCentroCusto::where('id', $id)->get();

        foreach ($viewData as $row => $data) {
            return $data->descricaoCentroCusto;
        }
    }

    public function vd_gerencialEmpresas($id = NULL) {
        if (empty($id)) return '';
        
        $viewData = GerencialEmpresas::where('id', $id)->get();

        foreach ($viewData as $row => $data) {
            return $data->nomeAlternativo;
        }
    }


    public function fk_gerencialCentroCusto($columnValueName = 'id') {
        $fkData = GerencialCentroCusto::orderBy('siglaCentroCusto')->get();

        $formValues = [];
        foreach($fkData as $row => $data) {
            $formValues[] = [$data->{$columnValueName}, $data->descricaoCentroCusto.' ('.$data->siglaCentroCusto.')'];
        }

        return ['options' => $formValues, 'type' => '']; 
    }

    public function fk_gerencialEmpresas($columnValueName = 'id') {
        $fkData = GerencialEmpresas::orderBy('nomeAlternativo')->get();

        $formValues = [];
        foreach($fkData as $row => $data) {
            $formValues[] = [$data->{$columnValueName}, $data->nomeAlternativo];
        }

        return ['options' => $formValues, 'type' => '']; 
    }

}
