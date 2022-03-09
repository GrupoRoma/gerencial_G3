<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GerencialTipoLancamento extends Model
{
    //
    protected $table    = 'gerencialTipoLancamento';
    protected $guarded  = ['id'];

    // Determina que os registros da tabela não podem ser excluídos pelo CRUD
    public  $deleteAble  = FALSE;
    
    // Identifica os ID's que não podem ser deletados
    //public  $noDeleteID = [1,2,3,4,5,6,7,8,9,10,11,14];

    public $viewTitle       = 'Tipos de Lançamento';
    public $columnList      = ['descricaoTipoLancamento', 
                                'sobreTipoLancamento', 
                                'historicoTipoLancamento', 
                                'historicoIncremental', 
                                'tipoLancamentoAtivo',
                                'ordemProcessamento'];

    public $columnAlias     = ['descricaoTipoLancamento'    => 'Descrição',
                                'sobreTipoLancamento'       => 'Sobre',
                                'historicoTipoLancamento'   => 'Histórico Padrão',
                                'historicoIncremental'      => 'Histórico Incremental',
                                'tipoLancamentoAtivo'       => 'Tipo de Lançamento Ativo',
                                'ordemProcessamento'        => 'Ordem de Processamento'];

    public $columnValue     = ['historicoIncremental'       => ['S' => 'Sim', 'N' => 'Não'],
                                'tipoLancamentoAtivo'       => ['S' => 'Sim', 'N' => 'Não']];

    public $customType      = ['historicoIncremental'       => ['type'      => 'radio',
                                                                'values'    => ['S' => 'Sim', 'N' => 'Não']],
                               'tipoLancamentoAtivo'        => ['type'      => 'radio',
                                                                'values'    => ['S' => 'Sim', 'N' => 'Não']]];
    public $rules  = ['descricaoTipoLancamento'     => 'required', 
                        'sobreTipoLancamento'       => 'nullable', 
                        'historicoTipoLancamento'   => 'nullable', 
                        'historicoIncremental'      => 'required', 
                        'tipoLancamentoAtivo'       => 'required',
                        'ordemProcessamento'       => 'required'];

    public $rulesMessage    = [ 'descricaoTipoLancamento'   => 'DESCRIÇÃO: Obrigatório',
                                'historicoIncremental'      => 'HISTÓRICO INCREMENTAL: Obrigatório',
                                'tipoLancamentoAtivo'       => 'TIPO DE LANÇAMENTO ATIVO: Obrigatório',
                                'ordemProcessamento'        => 'ORDER DE PROCESSAMENTO: Obrigatório'
                            ];

    public function getHistoricoLancamento(int $codigoTipoLancamento) {
        $tipoLancamento = $this->where('tipoLancamentoAtivo', 'S')
                               ->where('id', $codigoTipoLancamento)
                               ->get();
                               
        if (!empty($tipoLancamento[0]['historicoTipoLancamento'])) {
            return ['historicoPadrao'   => $tipoLancamento[0]['historicoTipoLancamento'],
                    'incremental'       => $tipoLancamento[0]['historicoIncremental']];
        }
        else return FALSE;
    }

    public function getTipoLancamento(Int $codigoTipo) {
        $dbData = $this->where('id', $codigoTipo)->get();

        if (isset($dbData[0]->id))  return $dbData[0];
        else                        return FALSE;
    }
}
