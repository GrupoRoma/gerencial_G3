<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GerencialBaseCalculoConta extends Model
{
    use HasFactory;

    protected $table        = 'gerencialBaseCalculoContas';
    protected $guarded      = ['id'];

    public $viewTitle       = 'Base de Cálculo (CONTAS)';
    public $columnList      = ['idBaseCalculo', 'idContaGerencial'];
    public $columnAlias     = ['idBaseCalculo'      => 'Base de Cálculo', 'idContaGerencial'  => 'Contas Gerenciais'];
    public $columnValue     = [];
    public $customType      = [];
    public $rules           = ['idBaseCalculo'      => 'required', 
                               'idContaGerencial'   => 'required'];
    public $rulesMessage    = [ 'idBaseCalculo'     => 'BASE DE CÁLCULO: preenchimento obrigatório',
                                'idContaGerencial'  => 'CONTA GERENCIAL: preenchimento obrigatório ou <br>já existe uma associação cadastrada para esta conta gerencial e base de cálculo.'
                              ];
    public $fkValue         = ['idContaGerencial'   => 'descricaoContaGerencial',
                               'idBaseCalculo'      => 'descricaoContaGerencial'];

    public function gerencialBaseCalculo() {
        return $this->hasOne('App\Models\GerencialBaseCalculo');
    }

    public function gerencialContaGerencial() {
        return $this->hasMany('App\Models\GerencialContaGerencial');
    }

    public function vd_gerencialBaseCalculo($id) {
        $viewData = GerencialBaseCalculo::where('id', $id)->get();

        foreach ($viewData as $row => $data) {
            return $data->descricaoBaseCalculo;
        }
    }

    public function vd_gerencialContaGerencial($id) {
        $viewData = GerencialContaGerencial::where('id', $id)->get();

        foreach ($viewData as $row => $data) {
            return $data->descricaoContaGerencial;
        }
    }

    public function fk_gerencialBaseCalculo($columnValueName = 'id') {
        $fkData = GerencialBaseCalculo::orderBy('descricaoBaseCalculo')->get();

        $formValues = [];
        foreach($fkData as $row => $data) {
            $formValues[] = [$data->{$columnValueName}, $data->descricaoBaseCalculo];
        }

        return ['options' => $formValues, 'type' => '']; 
    }

    
    public function fk_gerencialContaGerencial($columnValueName = 'id') {
        $fkData = GerencialContaGerencial::orderBy('codigoContaGerencial')->get();

        $formValues = [];
        foreach($fkData as $row => $data) {
            $formValues[] = [$data->{$columnValueName}, $data->codigoContaGerencial.' - '.$data->descricaoContaGerencial];
        }

        return ['options' => $formValues, 'type' => '']; 
    }
}
