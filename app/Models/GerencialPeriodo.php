<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GerencialPeriodo extends Model
{
    public  $mesAtivo;
    public  $anoAtivo;

    public  $errors;

    protected   $situacaoCheck = "'AB', 'LC'";

    protected $table = 'gerencialPeriodos';

    protected $guarded  = ['id', 'idUsuario'];

    public $viewTitle       = 'Períodos Gerenciais';
    public $columnList      = ['periodoMes', 
                                'periodoAno', 
                                'periodoAtivo', 
                                'periodoSituacao', 
                                'observacoes'];

    public $columnAlias     = ['periodoMes'         => 'Mês de Referência',
                                'periodoAno'        => 'Ano de Referência',
                                'periodoAtivo'      => 'Período Ativo',
                                'periodoSituacao'   => 'Situação do Período',
                                'observacoes'       => 'Observações'];

    public  $columnInfo     = ['periodoSituacao'    => ['AB'    => 'ABERTO PARA LANÇAMENTOS E CONSULTAS (PUBLICADO)',
                                                        'FC'    => 'FECHADO PARA LANÇAMENTOS A ABERTO A CONSULTAS (PUBLICADO)',
                                                        'LC'    => 'ABERTO PARA LANÇAMENTOS E FECHADO PARA CONSULTAS (EM ANDAMENTO)']];
    public $columnValue     = ['periodoAtivo'       => ['S' => 'Sim', 'N' => 'Não'],
                               'periodoSituacao'    => ['AB' => 'Em Andamento', 'FC' => 'Publicado', 'LC' => 'Aberto para Lançamentos']];

    public $customType      = ['periodoAtivo'       => ['type'      => 'radio',
                                                        'values'    => ['S' => 'Sim', 'N' => 'Não']],
                               'periodoSituacao'    => ['type'      => 'radio',
                                                        'values'    => ['AB' => '[AB] Em Andamento', 'FC' => '[PB] Publicado', 'LC' => '[LG] Lançamentos Gerenciais']]
                              ];

    public $rules  = ['periodoMes'           => 'required|max:12', 
                        'periodoAno'        => 'required',
                        'periodoAtivo'      => 'required', 
                        'periodoSituacao'   => 'required', 
                        'observacoes'       => 'nullable'];

    public $rulesMessage    = [ 'periodoMes'        => 'MÊS DE REFERÊNCIA: Obrigatório',
                                'periodoAno'        => 'ANO DE REFERÊNCIA: Obrigatório',
                                'periodoAtivo'      => 'PERÍODO ATIVO: Obrigatório',
                                'periodoSituacao'   => 'SITUAÇÃO DO PERÍODO: Obrigatório'
                              ];

   /**
     *  Define o período padrão para as validações de importação
     * 
     *  @param  string      Mês de referência
     *  @param  string      Ano de referência
     * 
     *  @return boolean
     */
    public function setPeriodo(string $parMes, string $parAno) {
        if (empty($parMes)) return FALSE;
        if (empty($parAno)) return FALSE;

        $this->mesAtivo = $parMes;
        $this->anoAtivo = $parAno;

        return TRUE;
    }

    public function setCheckSituacao(String $situacao = NULL) {
        $this->situacaoCheck    = "'".($situacao ?? "AB', 'LC")."'";
    }

    /** 
     *	VALIDAÇÃO DE PERÍODO ABERTO
     *	O período cadastrado deve estar ATIVO = S e
     *	a situação do período dever ser AB: Aberto ou LC: Aberto para Lançamentos
     *
     *	Na validação para importação dos lançamentos contábeis, deve retornar apenas um resultado,
     *	caso retorne mais de um período o sistema irá retornar ERRO DE VALIDAÇÃO
     *
     *  @param  string      Mês de referência
     *  @param  string      Ano de referência
     *
     *  @return array      Período nos formatos 2020, 12, 202012, 122020, 12/2020 e a situação e observações
     */
    public function checkPeriodo(string $parMes = NULL, string $parAno = NULL) {
        $query      =  "SELECT ano		    = G3_gerencialPeriodos.periodoAno,
                                mes		    = G3_gerencialPeriodos.periodoMes,
                                observacoes = G3_gerencialPeriodos.observacoes,
                                situacao    = G3_gerencialPeriodos.periodoSituacao
                        FROM GAMA..G3_gerencialPeriodos		(nolock)
                        /* PERÍODO ATIVO */
                        WHERE G3_gerencialPeriodos.periodoAtivo   = 'S'
                        /* SITUAÇÂO IGUAL A [AB] Aberto para Consultas e Lançamentos ou [LC] Aberto somente para Lançamentos */
                        AND   G3_gerencialPeriodos.periodoSituacao IN (".$this->situacaoCheck.")";

        if (!empty($parMes) && !empty($parAno)) {
            $query .= "AND   G3_gerencialPeriodos.periodoMes     = '$parMes'
                       AND   G3_gerencialPeriodos.periodoAno     = '$parAno'
                       ORDER BY ano DESC, mes DESC";

            $dbData     = DB::select($query);
        }
        else $dbData     = DB::select($query);

        $this->situacaoCheck    = "'AB', 'LC'";

        if (count($dbData) == 0) {
            $this->errors[] = "Não foi encontrado nenhum período (mês/ano) ativo e aberto [AB | LC]";
            return FALSE;
        }

        $returnData     = [];
        $periodosAbertos= '';
        foreach ($dbData as $data) {
            $returnData[] = ['ano'       => $data->ano, 
                            'mes'        => $data->mes, 
                            'anoMes'     => $data->ano.$data->mes, 
                            'mesAno'     => $data->mes.$data->ano, 
                            'MESANO'     => $data->mes.'/'.$data->ano,
                            'situacao'   => $data->situacao,
                            'observacoes'=> $data->observacoes] ;
            
            $periodosAbertos .= $data->mes.'/'.$data->ano.(!empty($periodosAbertos) ? ', ' : '');
        }

        if(count($dbData) > 1) {
            $this->errors[] = "Os períodos ".$periodosAbertos." estão abertos. Favor corrigir e tentar novamente.";
            return FALSE;
        }

        return $returnData;
    }

    /**
     *  current
     *  Retorna os dados do período que estiver ativo (corrente)
     *  considerando os estados (1)AB: Aberto e (2)LC: Aberto para Lancamento
     * 
     *  @return object  (periodo = {mes, ano, mesAno, })
     */
    public function current() {
        $current    = $this->where('periodoAtivo', 'S')
                           ->whereIn('periodoSituacao', ['AB', 'LC'])
                           ->orderBy('periodoAno')
                           ->orderBy('periodoMes', 'DESC')
                           ->first();

        //$current    = (object) $dbData[0]->attributes;

        return (object) ['ano'        => $current->periodoAno, 
                         'mes'        => $current->periodoMes, 
                         'anoMes'     => $current->periodoAno.$current->periodoMes, 
                         'mesAno'     => str_pad($current->periodoMes,2,'0',STR_PAD_LEFT).$current->periodoAno, 
                         'MESANO'     => str_pad($current->periodoMes,2,'0',STR_PAD_LEFT).'/'.$current->periodoAno,
                         'situacao'   => $current->periodoSituacao,
                         'observacoes'=> $current->observacoes] ;
       
    }

    
}
