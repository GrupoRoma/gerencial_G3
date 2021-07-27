<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GerencialGrupoConta extends Model
{
    protected $table    = 'gerencialGrupoConta';
    protected $guarded  = ['id', 'idUsuario_created'];

    public $viewTitle       = 'Grupo de Conta';
    public $columnList      = ['codigoGrupoConta', 
                                'descricaoGrupoConta', 
                                'idSubGrupoConta',
                                'infoGrupoConta', 
                                'receitaCustoMercadoria', 
                                'ordemExibicao', 
                                'grupoContaAtivo'];

    public $columnAlias     = ['codigoGrupoConta'           => 'Código',
                                'descricaoGrupoConta'       => 'Descrição do Grupo de Conta',
                                'idSubGrupoConta'           => 'Sub-Grupo de Conta',
                                'infoGrupoConta'            => 'Informações Detalhadas',
                                'receitaCustoMercadoria'    => 'Grupo de Receita / Custo da Mercadoria',
                                'ordemExibicao'             => 'Ordem para exibição',
                                'grupoContaAtivo'           => 'Grupo de Conta Ativo'];

    public $columnValue     = ['grupoContaAtivo'        => ['S' => 'Sim', 'N' => 'Não']];

    public $customType      = ['receitaCustoMercadoria' => ['type'      => 'radio',
                                                            'values'    => ['REC' => 'Receita', 'CST' => 'Custo', 'NAP' => 'N/A']],
                               'grupoContaAtivo'        => ['type'      => 'radio',
                                                            'values'    => ['S' => 'Sim', 'N' => 'Não']]
                              ];

    public $rules  = ['codigoGrupoConta'            => 'required|max:99999',
                        'idSubGrupoConta'           => 'required',
                        'descricaoGrupoConta'       => 'required', 
                        'infoGrupoConta'            => 'nullable', 
                        'receitaCustoMercadoria'    => 'required', 
                        'ordemExibicao'             => 'nullable', 
                        'grupoContaAtivo'           => 'required'];

    public $rulesMessage    = [ 'codigoGrupoConta'          => 'CÓDIGO DO GRUPO DE CONTA: Obrigatório',
                                'idSubGrupoConta'           => 'SUB-GRUPO DE CONTA: Obrigatório',
                                'descricaoGrupoConta'       => 'DESCRIÇÃO DO GRUPO DE CONTA: Obrigatório',
                                'receitaCustoMercadoria'    => 'GRUPO DE RECEITA / CUSTO DA MERCADORIA: Obrigatório',
                                'grupoContaAtivo'           => 'GRUPO DE CONTA ATIVO: Obrigatório'

                              ];

    public function vd_gerencialSubGrupoConta($id) {
        $viewData = GerencialSubGrupoConta::where('id', $id)->get();

        foreach ($viewData as $row => $data) {
            return $data->descricaoSubGrupoConta;
        }
    }

    public function fk_gerencialSubGrupoConta($columnValueName = 'id') {
        $fkData = GerencialSubGrupoConta::orderBy('descricaoSubGrupoConta')->get();

        $formValues = [];
        foreach($fkData as $row => $data) {
            $formValues[] = [$data->{$columnValueName}, $data->descricaoSubGrupoConta];
        }

        return ['options' => $formValues, 'type' => '']; 
    }

}
