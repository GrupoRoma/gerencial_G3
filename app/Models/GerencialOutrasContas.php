<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GerencialOutrasContas extends Model
{
    use HasFactory;

    protected   $table  = 'gerencialOutrasContas';

    protected $guarded  = ['id', 'destino'];

    public  $viewTitle      = 'Outras Contas Contábeis';
    public  $columnList     = ['codigoEmpresaERP', 
                                'codigoContaContabilERP', 
                                'percentualSaldo',
                                'historicoPadrao',
                                'outrasContasAtivo'];

    public $columnAlias     = ['codigoEmpresaERP'           => 'Empresa de Origem',
                                'codigoContaContabilERP'    => 'Conta Contabil',
                                'percentualSaldo'           => '% do Saldo contabil',
                                'historicoPadrao'           => 'Histórico Padrão',
                                'outrasContasAtivo'         => 'Ativo'];

    public $columnValue     = ['outrasContasAtivo'          => ['S' => 'Sim', 'N' => 'Não']];
    public $customType      = ['outrasContasAtivo'          => ['type'      => 'radio',
                                                                'values'    => ['S' => 'Sim', 'N' => 'Não']]];

    public $rules  = ['codigoEmpresaERP'            => 'required',
                        'codigoContaContabilERP'    => 'required',
                        'percentualSaldo'           => 'required',
                        'historicoPadrao'           => 'nullable',
                        'outrasContasAtivo'         => 'required'];

    public $rulesMessage    = [ 'codigoEmpresaERP'      => 'EMPRESA ORIGEM / DESTINO: Obrigatório',
                                'codigoContaContabilERP'=> 'CONTA CONTÁBIL ORIGEM / DESTINO: Obrigatório',
                                'percentualSaldo'       => '% SALDO CONTABIL ORIGEM / DESTINO: Obrigatório',
                                'outrasContasAtivo'     => 'ATIVO: Obrigatório'
                            ];   

    /**
     * Retona a Empresa associada
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

    public function vd_gerencialCentroCusto($id) {
        $viewData = GerencialCentroCusto::where('id', $id)->get();

        foreach ($viewData as $row => $data) {
            return $data->descricaoCentroCusto;
        }
    }

    public function vd_codigoContaContabilERP($id) {
        $viewData = DB::select("SELECT PlanoConta.PlanoConta_Codigo, PlanoConta.PlanoConta_ID, PlanoConta.PlanoConta_Descricao 
                                FROM   GrupoRoma_DealernetWF..PlanoConta
                                WHERE  PlanoConta.PlanoConta_Codigo = $id");

        foreach ($viewData as $row => $data) {
            return $data->PlanoConta_ID.' - '.$data->PlanoConta_Descricao;
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

    public function fk_gerencialCentroCusto($columnValueName = 'id') {
        $fkData = GerencialCentroCusto::orderBy('descricaoCentroCusto')->get();

        $formValues = [];
        foreach($fkData as $row => $data) {
            $formValues[] = [$data->{$columnValueName}, $data->descricaoCentroCusto];
        }

        return ['options' => $formValues, 'type' => '']; 
    }

    public function fk_gerencialContaContabil($columnValueName = 'id') {
        $fkData = GerencialContaContabil::orderBy('contaContabil')->get();

        $formValues = [];
        foreach($fkData as $row => $data) {
            $formValues[] = [$data->{$columnValueName}, $data->contaContabil];
        }

        return ['options' => $formValues, 'type' => '']; 
    }

     /*
     *  Formulário com drop das empresas cadastradas no ERP
     */
    public function custom_codigoContaContabilERP($columnValueName ='PlanoConta_Codigo') {
        $customData = DB::select("SELECT PlanoConta.PlanoConta_Codigo, PlanoConta.PlanoConta_ID, PlanoConta.PlanoConta_Descricao 
                                  FROM   GrupoRoma_DealernetWF..PlanoConta
                                  WHERE  PlanoConta.Estrutura_Codigo = '5'
                                  AND    PlanoConta.PlanoConta_Nivel = 5
                                  AND    PlanoConta.PlanoConta_Ativo = 1
                                  AND    PlanoConta.PlanoConta_TipoContabil NOT IN ('RES','DSP','REC','ATV')
                                  ORDER BY PlanoConta.PlanoConta_ID");

        $formValues = [];
        foreach ($customData as $row => $data) {
            $formValues[]   = [$data->{$columnValueName}, $data->PlanoConta_ID.' - '.$data->PlanoConta_Descricao];
        }
        
        return ['options' => $formValues, 'type' => ''];

    } //-- custom_codigoContaContabilERP --//



}
