<?php

namespace App\Models\Utils;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Utilitarios extends Model
{
    use HasFactory;

    /**
     *  carrega os dados da tabela de parâmetros do gerencial 2.0
     */
    public function getParametrosG2() {

        return DB::select("SELECT * FROM GAMA..SGA_PAR_GRUPO WHERE mes_ano = '08/2021' AND COD_SEGM = '5'");
    }

    /**
     *  Limpa a tabela de dados informada
     * 
     *  @param  string  tablename
     * 
     */
    public function clearTable($tableName) {
        return DB::table('gerencial'.$tableName)->truncate();
    }

    /**
     *  Inclui o registro na tabela de G3_gerencialParametroRateio
     *  
     *  @param  array   ['columnName'   => 'insertValue', ...]
     * 
     *  @return boolean 
     */
    public function saveParametro($data) {
        // Prepara a string sql
        $columns    = '';
        $valuesInt  = '';
        $valuesPar  = [];
        foreach ($data as $columnName => $insertValue) {
            $columns    .= $columnName.',';
            $valuesInt  .= '?,';
            $valuesPar[] = $insertValue;
        }

        return DB::insert('insert into GAMA..G3_gerencialParametroRateio ('.substr($columns,0,-1).') 
                                    values ('.substr($valuesInt,0,-1).')', $valuesPar);
    }


    public function validateMessage($errorsFound, $customMessages) {

        if (isset($customMessages) && !empty($customMessages)) {
            foreach ($errorsFound as $column => $errors) {
                $validate[]   =  $customMessages[$column];
            }
        }
        else $validate[]    = 'Ocorreu um erro desconhecido, favor entrar em contato com o administrador do sistema';

        return $validate;
    }
}
