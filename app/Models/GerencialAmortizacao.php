<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GerencialAmortizacao extends Model
{
    use HasFactory;

    protected   $table  = 'gerencialAmortizacao';

    protected $guarded  = ['id', 'saldoAmortizacao', 'parcelasAmortizadas'];

    public  $viewTitle      = 'Amortizações';
    public  $columnList     = ['descricao',
                                'idContaGerencial', 
                                'idTipoLancamento', 
                                'valorPrincipal',
                                'valorParcela',
                                'numeroParcelas',
                                'tipoValor',
                                'empresasDestino',
                                'idContaGerencialDestino',
                                'idCentroCusto',
                                'historico',
                                'amortizacaoAtiva'];

    public $columnAlias     = ['descricao'                  => 'Descrição',
                                'idContaGerencial'          => 'Conta Gerencial',
                                'idTipoLancamento'          => 'Tipo de Lancamento Associado',
                                'valorPrincipal'            => 'Valor a Amortizar',
                                'valorParcela'              => 'Valor da Parcela',
                                'numeroParcelas'            => 'Número de Parcelas',
                                'tipoValor'                 => 'Tipo Valor',
                                'empresasDestino'           => 'Empresas de Destino',
                                'idContaGerencialDestino'   => 'Conta Gerencial de Destino',
                                'idCentroCusto'             => 'Centro de Custo de Destino',
                                'historico'                 => 'Histórico Padrão',
                                'amortizacaoAtiva'          => 'Regra Ativa'];

    public $columnValue    = ['tipoValor'           => ['ABS' => 'Absoluto', 'PRP' => 'Proporcional'],
                              'amortizacaoAtiva'    => ['S'   => 'Sim', 'N' => 'Não']];

    public $customType     = ['tipoValor'           => ['type'      => 'radio',
                                                        'values'    => ['ABS' => 'Absoluto', 'PRP' => 'Proporcional']],
                              'amortizacaoAtiva'    => ['type'      => 'radio',
                                                        'values'    => ['S' => 'Sim', 'N' => 'Não']]                                                            
                                                            ];
    public $rules  = ['descricao'                   => 'nullable',
                        'idContaGerencial'          => 'required',
                        'idTipoLancamento'          => 'required',
                        'valorPrincipal'            => 'required',
                        'valorParcela'              => 'nullable',
                        'numeroParcelas'            => 'required',
                        'tipoValor'                 => 'nullable',
                        'empresasDestino'           => 'required',
                        'idContaGerencialDestino'   => 'required',
                        'historico'                 => 'nullable',
                        'amortizacaoAtiva'          => 'required'];

    public $rulesMessage    = [ 'idContaGerencial'          => 'CONTA GERENCIAL: Obrigatório.',
                                'idTipoLancamento'          => 'TIPO DE LANÇAMENTO ASSOCIADO: Obrigatório',
                                'valorPrincipal'            => 'VALOR A AMORTIZAR: Obrigatório',
                                'numeroParcelas'            => 'NÚMERO DE PARCELAS: Obrigatório',
                                'empresasDestino'           => 'EMPRESAS DE DESTINO: Obrigatório',
                                'idContaGerencialDestino'   => 'CONTA GERENCIAL DE DESTINO: Obrigatório',
                                'amortizacaoAtiva'          => 'REGRA ATIVA: Obrigatório'
                              ];

    /**
     * Retona a Conta Gerencial associada
     */
    public function gerencialContaGerencial() {
        return $this->hasOne('App\Models\GerencialContaGerencial');
    }

    /**
     * Retona a Empresa associada
     */
    public function gerencialEmpresas() {
        return $this->hasOne('App\Models\GerencialEmpresas');
    }

    /**
     * Retona o Centro de Custo associado
     */
    public function gerencialCentroCusto() {
        return $this->hasOne('App\Models\GerencialCentroCusto');
    }

    /**
     * Retona o Tipo de Lançamento associado
     */
    public function gerencialTipoLancamento() {
        return $this->hasOne('App\Models\GerencialTipoLancamento');
    }

    /**
     * Retorna os dados para visualização
     */
    public function vd_gerencialTipoLancamento($id) {
        $viewData = GerencialTipoLancamento::where('id', $id)->get();

        foreach ($viewData as $row => $data) {
            return $data->descricaoTipoLancamento;
        }
    }

    public function vd_gerencialContaGerencial($id) {
        $viewData = GerencialContaGerencial::where('id', $id)->get();

        foreach ($viewData as $row => $data) {
            return $data->descricaoContaGerencial;
        }
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

    public function fk_gerencialEmpresas($columnValueName = 'id') {
        $fkData = GerencialEmpresas::orderBy('nomeAlternativo')->get();

        $formValues = [];
        foreach($fkData as $row => $data) {
            $formValues[] = [$data->{$columnValueName}, $data->nomeAlternativo];
        }

        return ['options' => $formValues, 'type' => '']; 
    }

    public function fk_gerencialCentroCusto($columnValueName = 'id') {
        $fkData = GerencialCentroCusto::orderBy('siglaCentroCusto')->get();

        $formValues = [];
        foreach($fkData as $row => $data) {
            $formValues[] = [$data->{$columnValueName}, $data->descricaoCentroCusto.' ('.$data->siglaCentroCusto.')'];
        }

        return ['options' => $formValues, 'type' => '']; 
    }

    public function fk_gerencialContaGerencial($columnValueName = 'id') {
        $fkData = GerencialContaGerencial::orderBy('codigoContaGerencial')->get();

        $formValues = [];
        foreach($fkData as $row => $data) {
            $formValues[] = [$data->{$columnValueName}, $data->codigoContaGerencial.'.'.$data->descricaoContaGerencial];
        }

        return ['options' => $formValues, 'type' => '']; 
    }

    public function fk_gerencialTipoLancamento($columnValueName = 'id') {
        $fkData = GerencialTipoLancamento::orderBy('descricaoTipoLancamento')->get();

        $formValues = [];
        foreach($fkData as $row => $data) {
            $formValues[] = [$data->{$columnValueName}, $data->descricaoTipoLancamento];
        }

        return ['options' => $formValues, 'type' => '']; 
    }

    public function custom_empresasDestino($values = NULL, $multi = TRUE) {
        $customData = GerencialEmpresas::where('empresaAtiva', 'S')
                                        ->orderBy('nomeAlternativo')
                                        ->get();

        $htmlForm = "<select class='form-control' name='empresasDestino".($multi ? '[]\' multiple' : '\'')." id='empresasDestino'>";
        if (!$multi) $htmlForm .= "<option>--- selecione uma Empresa ---</option>";

        $values = explode(',', $values);
        foreach ($customData as $row => $data) {
            $htmlForm .= "<option value='".$data->id."' ".(in_array($data->id, $values) ? 'selected' : '').">".
                            $data->nomeAlternativo.
                         "</option>";
        }
        $htmlForm .= "</select>";

        return $htmlForm.($multi ? "<small class='form-text text-muted'><b>CTRL+Click</b> para selecionar mais de uma opção</small>" : "");
    }   //-- custom_empresasDestino --//

}
