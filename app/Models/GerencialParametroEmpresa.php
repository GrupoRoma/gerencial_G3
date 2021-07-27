<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GerencialParametroEmpresa extends Model
{
    use HasFactory;

    protected   $table          = 'gerencialParametroEmpresas';

    protected   $guarded        = ['id'];
    public      $viewTitle      = 'Transferência entre Empresas';
    public      $columnList     = ['idEmpresaOrigem',  'idEmpresaDestino', 'parametroAtivo'];
    public      $columnAlias    = ['idEmpresaOrigem'    => 'Emrpesa de Origem',
                                    'idEmpresaDestino'  => 'Empresa de Destino',
                                    'parametroAtivo'        => 'Parâmetro Ativo'];

    public      $columnValue    = ['parametroAtivo'         => ['S'  => 'Sim', 'N'  => 'Não']];
    public      $customType     = ['parametroAtivo'         => ['type'      => 'radio', 'values'    => ['S' => 'Sim', 'N' => 'Não']]];
    public      $rules          = ['idEmpresaOrigem'    => 'required', 
                                   'idEmpresaDestino'  => 'required', 
                                   'parametroAtivo'        => 'required'];

    public      $rulesMessage   = ['idEmpresaOrigem'   => 'EMPRESA DE ORIGEM: Obrigatório',
                                   'idEmpresaDestino'  => 'EMPRESA DE DESTINO: Obrigatório',
                                   'parametroAtivo'    => 'PARÂMETRO ATIVO: Obrigatório'
                                  ];  

    /**
     * Retona o Centro de Custo associado
     */
    public function gerencialEmpresas() {
        return $this->hasOne('App\Models\GerencialEmpresas');
    }

    public function vd_gerencialEmpresas($id) {
        $viewData = GerencialEmpresas::where('id', $id)->get();

        foreach ($viewData as $row => $data) {
            return $data->nomeAlternativo;
        }
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
