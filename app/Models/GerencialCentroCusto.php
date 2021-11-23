<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GerencialCentroCusto extends Model
{
    protected $table      = 'gerencialCentroCusto';
    protected $guarded    = ['id'];

    public $viewTitle       = 'Centro de Custo';
    public $columnList      = ['codigoCentroCustoERP', 
                                'descricaoCentroCusto', 
                                'siglaCentroCusto', 
                                'centroCustoVendas', 
                                'centroCustoPosVendas', 
                                'analiseVertical',
                                'ordemExibicao',
                                'centroCustoAtivo'];

    public $columnAlias     = ['codigoCentroCustoERP'       => 'Código C.Custo (Dealernet)',
                                'descricaoCentroCusto'      => 'Descrição do C. de Custo',
                                'siglaCentroCusto'          => 'Sigla do C. de Custo',
                                'centroCustoVendas'         => 'C.Custo de Vendas',
                                'centroCustoPosVendas'      => 'C.Custo de Pós-Vendas',
                                'analiseVertical'           => 'Análise Vertical',
                                'ordemExibicao'             => 'Ordem de Exibição',
                                'centroCustoAtivo'          => 'C. de Custo ativo'];

    public $columnValue     = ['centroCustoVendas'          => ['S' => 'Sim', 'N' => 'Não'],
                                'centroCustoPosVendas'      => ['S' => 'Sim', 'N' => 'Não'],
                                'analiseVertical'           => ['S' => 'Sim', 'N' => 'Não'],
                                'centroCustoAtivo'          => ['S' => 'Sim', 'N' => 'Não']];

    public $customType      = ['centroCustoVendas'          => ['type'      => 'radio',
                                                                'values'    => ['S' => 'Sim', 'N' => 'Não']],
                               'centroCustoPosVendas'       => ['type'      => 'radio',
                                                                'values'    => ['S' => 'Sim', 'N' => 'Não']],
                               'analiseVertical'            => ['type'      => 'radio',
                                                                'values'    => ['S' => 'Sim', 'N' => 'Não']],
                               'centroCustoAtivo'           => ['type'      => 'radio',
                                                                'values'    => ['S' => 'Sim', 'N' => 'Não']]
                              ];

    public $rules  = ['codigoCentroCustoERP'        => 'required', 
                        'descricaoCentroCusto'      => 'nullable', 
                        'siglaCentroCusto'          => 'nullable', 
                        'centroCustoVendas'         => 'required', 
                        'centroCustoPosVendas'      => 'required',
                        'analiseVertical'           => 'required',
                        'ordemExibicao'             => 'required',
                        'centroCustoAtivo'          => 'required'];
    
    public $rulesMessage    = [ 'codigoCentroCustoERP'  => 'CÓDIGO C.CUSTO: Obrigatório',
                                'centroCustoVendas'     => 'C.CUSTO DE VENDAS: Obrigatório',
                                'centroCustoPosVendas'  => 'C.CUSTO DE PÓS-VENDAS: Obrigatório',
                                'analiseVertical'       => 'ANÁLISE VERTICAL: Obrigatório',
                                'ordemExibicao'         => 'ORDEM DE EXIBIÇÃO: Obrigatório',
                                'centroCustoAtivo'      => 'C. DE CUSTO ATIVO: Obrigatório'];

    /*
     *  Lista dos centros de resultado do ERp Workflow (Centros de Custo)
     * 
     */
    public function custom_codigoCentroCustoERP($values = NULL, $multi = FALSE) {
        $centroCustoERP = DB::select("  SELECT CentroResultado.CentroResultado_Codigo, 
                                                CentroResultado.CentroResultado_Descricao 
                                        FROM   GrupoRoma_DealernetWF..CentroResultado (nolock)
                                        WHERE  CentroResultado.Estrutura_Codigo = 5
                                        AND    CentroResultado.CentroResultado_Ativo = 1
                                        ORDER BY CentroResultado.CentroResultado_Descricao");

        $htmlForm = "<select class='form-control' name='codigoCentroCustoERP".($multi ? '[]\' multiple' : '\'')." id='codigoCentroCustoERP'>";
        if (!$multi) $htmlForm .= "<option>--- selecione um Centro de Custo ---</option>";

        $values = explode(',', $values);
        foreach ($centroCustoERP as $row => $data) {
            $htmlForm .= "<option value='".$data->CentroResultado_Codigo."' ".(in_array($data->CentroResultado_Codigo, $values) ? 'selected' : '').">".
                            $data->CentroResultado_Descricao.
                         "</option>";
        }
        $htmlForm .= "</select>";

        return $htmlForm.($multi ? "<small class='form-text text-muted'><b>CTRL+Click</b> para selecionar mais de uma opção</small>" : "");
    }


    public function getCodigoCentroCusto($siglaCC, $origem = NULL) {
        $dbData = $this->where('gerencialCentroCusto.siglaCentroCusto', $siglaCC)
                       ->get();

        if ($origem == 'ERP')   $returnFiled = 'codigoCentroCustoERP';
        else                    $returnFiled = 'id';

        return $dbData[0]->$returnFiled;
    }

    /**
     *  Retorna os dados do centro de custo a partir do código do centro de custo no ERP (Centro de Resultado)
     * 
     *  @param  integer codigoERP
     * 
     *  @return object  dbDataRow
     */
    public function getCentroCustoCodigoERP($codigoERP) {
        $dbData = $this->where('codigoCentroCustoERP', $codigoERP)
                       ->get();
        
        return $dbData[0] ?? FALSE;
    }

}
