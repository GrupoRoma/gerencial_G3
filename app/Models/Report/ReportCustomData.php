<?php

namespace App\Models\Report;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

class ReportCustomData extends Model
{
    use HasFactory;

    protected   $columnList;
    protected   $conditions;
    protected   $order;

    /**
     *  setColumnsList
     *  Define a lista de colunas para a consulta
     * 
     *  @param  object      ARRAY | JSON object
     *                      [column1, column2, column3, ...] | {column1, column2, column3, ...}
     */
    public function setColumns(object $columns) {
        if (!empty($columns))    $this->columnList   = (array) $columns;
    }

    /**
     *  setConditions
     *  Define as condições (filtros) para a consulta
     * 
     * Os operadores válidos são:
     *  = | <> | IN | NOT IN | LIKE | RAW
     * 
     *  Para os operadores IN e NOT IN o [value] tem que ser um array;
     *  Para o operador LIKE o [value] deve conter os wildcards [], %, _, ^, -
     *  Para o operador RAW toda a condições where deve ser escrita em [columnName], as demais propriedades são desconsideradas
     *      ex: {["columnName": "columnName <> columnValue", "operator": "RAW"]}
     *          {["columnName": "columnName not in (select * from tablename)", "operator": "RAW"]}
     * 
     *  @param  string      JSON conditions {["columnName": "[columnName]", 
     *                                        "operator": "[= | <> | IN | NOT IN | LIKE | RAW]", 
     *                                        "value": "[value]"]}
     * 
     */
    public function setConditions(object $conditions) {
        if (!empty($conditions))    $this->conditions   = (object) $conditions;
    }

    public function setOrder(string $orderBy) {
        $this->order    = $orderBy;
    }

    /**
     *  gerencialContaContabil
     *  Consulta para relatório do cadastro de conta gerencial x conta contábil
     * 
     *  @return mixed      dbResult | FALSE
     * 
     */
    public function CDgerencialContaContabil() {
        $dbData     = DB::table('gerencialContaContabil');

        $dbData->join('gerencialContaGerencial', 'gerenicalContaGerencial.id', '=', 'gerencialContaContabil.idContaGerencial');
        $dbData->join('GrupoRoma_DealernetWF..PlanoConta', 'PlanoConta.PlanoConta_Codigo', '=', 'gerencialContaContabil.codigoContaContabilERP');
        $dbData->leftJoin('gerencialCentroCusto', 'gerenicalCentroCusto.id', '=', 'gerencialContaContabil.idCentroCusto');
        $dbData->leftJoin('GrupoRoma_DealernetWF..SubConta', 'SubConta.SubConta_Codigo', '=', 'gerencialContaContabil.codigoSubContaERP');

        if (!empty($this->conditions))  {
           $conditions = json_decode($this->conditions);

           foreach ($conditions as $row => $condition) {
                $operator    = strtoupper($condition->operator ?? '=');

                switch ($operator) {
                    case 'IN':
                    case 'NOT IN':
                        if (!is_array($condition->value))   return FALSE;
                        $dbData->whereIn($condition->columnName, $operator, $condition->value);
                        break;
                    case 'RAW':
                        $dbData->where($condition->columnName);
                        break;
                    default:
                        $dbData->where($condition->columnName, $operator, $condition->value);
                        break;
                }
           }

           if (isset($this->columnList) && !empty($this->columnList))   $dbData->select($this->columnList);
           else                                                         $dbData->select();

           if (!empty($this->order))                                    $dbData->orderBy($this->order);

           $dbData->get();
       }
    }


    /**
     *  customData
     *  Retorna os dados de uma consulta customizada a partir do config-data do relatório
     * 
     *  @param  object      JSON config-data
     * 
     *  @return mixed       object dbdata result | FALSE failure
     * 
     */
    public function customData(object $customData) {
        
        // DEFINE TABELA DE CONSULTA
        $primaryTable   = $customData->table;
        $tablePrefix    = DB::getTablePrefix();

        //$dbData = DB::table($primaryTable);

        // SET COLUMN LIST
        $select = (implode(',', $customData->columns) ?? '*');
        $from   =  $primaryTable."\t\t(nolock)";

        $order  = '';
        if (isset($customData->order) && !empty($customData->order)) {
            $order  = " ORDER BY ".$customData->order;
        }

        $group  = '';
        if (isset($customData->group) && !empty($customData->group)) {
            $group  = " GROUP BY ".implode(',', $customData->group);
        }

        // JOIN TABLES
        if (isset($customData->join)) {
            $rawJoin    = '';
            foreach ($customData->join as $count => $join) {
                $rawJoin   .= ($join->type ?? '')." JOIN ";
                $rawJoin   .= $join->leftTable." ".($join->alias ?? '')."\t\t(nolock) ON ";
                $rawJoin   .= ($join->alias ?? $join->leftTable).'.'.$join->leftColumn.' ';
                $rawJoin   .= ($join->operator ?? ' = ');
                $rawJoin   .= ($join->rightColumnRAW ?? ($join->rightTable ?? $primaryTable).'.'.$join->rightColumn);
                $rawJoin   .= "\n";
            }
        }

        // WHERE
        $whereRaw = " 1 = 1\n ";
        if (isset($customData->filter)) {
            foreach ($customData->filter as $count => $where) {
                if (isset($where->columnName) && !empty($where->columnName)) {
                    $whereRaw   .= "AND\t".$where->columnName;
                    $whereRaw   .= " ".($where->operator ?? '=');
                    $whereRaw   .= " ".$where->value."\n";
                }
            }
        }
        
        // Filter Conditions
        if (!empty($this->conditions))  {

            foreach ($this->conditions as $row => $condition) {
                 $operator    = strtoupper($condition['operator'] ?? '=');

                 switch ($operator) {
                     case 'IN':
                     case 'NOT IN':
                         if (is_array($condition['value']))     $value  = implode(",", $condition['value']);
                         else                                   $value  = $condition['value'];
                         $whereRaw .= "AND\t".$condition['columnName']." ". $operator . " (".$value.")\n";
                         break;
                     case 'RAW':
                         $whereRaw  .= "AND\t".$condition['columnName']."\n";
                         break;
                     default:
                         $whereRaw .= "AND\t".$condition['columnName']."\t".($operator ?? "=")."\t".$condition['value']."\n";
                         break;
                 }
            }
        }


        $SQLquery = "SELECT ".$select."\nFROM ".$from."\n".$rawJoin."\nWHERE ".$whereRaw."\n".$order."\n".$group;
//dd($SQLquery);
        // Retorna os dados da consulta
        return DB::select($SQLquery);
    }


}
