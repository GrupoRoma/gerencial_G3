<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GerencialSubGrupoConta extends Model
{
    //
    protected $table    = 'gerencialSubGrupoConta';
    protected $guarded  = ['id', 'idUsuario_created'];

    public $viewTitle       = 'Sub-Grupo de Conta';
    public $columnList      = ['descricaoSubGrupoConta', 
                                'baseMargemBruta', 
                                'subGrupoAtivo'];

    public $columnAlias     = ['descricaoSubGrupoConta'    => 'Descrição do Sub-Grupo de Conta',
                                'baseMargemBruta'           => 'Base para Margem Bruta',
                                'subGrupoAtivo'             => 'Sub-Grupo de Conta Ativo'];

    public $columnValue     = ['subGrupoAtivo'              => ['S' => 'Sim', 'N' => 'Não'],
                                'baseMargemBruta'           => ['S' => 'Sim', 'N' => 'Não']];

    public $customType      = ['baseMargemBruta'            => ['type'      => 'radio',
                                                                'values'    => ['S' => 'Sim', 'N' => 'Não']],
                               'subGrupoAtivo'              => ['type'      => 'radio',
                                                                'values'    => ['S' => 'Sim', 'N' => 'Não']]
                              ];

    public $rules  = ['descricaoSubGrupoConta'    => 'required', 
                        'baseMargemBruta'           => 'required', 
                        'subGrupoAtivo'             => 'required'];

    public $rulesMessage    = [ 'descricaoSubGrupoConta'    => 'DESCRIÇÃO DO SUB-GRUPO DE CONTA: Obrigatório',
                                'baseMargemBruta'           => 'BASE PARA MARGEM BRUTA: Obrigatório',
                                'subGrupoAtivo'             => 'SUB_GRUPO DE CONTA: Obrigatório'
                            ];

    /**
     * Retona o Grupo de Conta associado à Conta Gerencial
     * 
     */
    public function gerencialGrupoConta() {
        return $this->hasOne('App\Models\GerencialGrupoConta');
    }

    public function vd_gerencialGrupoConta($id) {
        $viewData = GerencialGrupoConta::where('id', $id)->get();

        foreach ($viewData as $row => $data) {
            return $data->descricaoGrupoConta;
        }
    }

    public function fk_gerencialGrupoConta($columnValueName = 'id') {
        $fkData = GerencialGrupoConta::orderBy('codigoGrupoConta')->get();

        $formValues = [];
        foreach($fkData as $row => $data) {
            $formValues[] = [$data->{$columnValueName}, $data->codigoGrupoConta.' - '.$data->descricaoGrupoConta];
        }

        return ['options' => $formValues, 'type' => '']; 
    }
}
