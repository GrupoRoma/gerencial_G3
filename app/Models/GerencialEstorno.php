<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GerencialEstorno extends Model
{
    use HasFactory;

    protected   $table      = 'gerencialEstornos';
    protected   $guarded    = ['id'];

    public $viewTitle       = 'Estornos';

    public $columnsGrid     = ['idContaGerencial', 'codigoContaContabil', 'idCentroCusto', 'estornoAtivo'];
    public $columnList      = ['idContaGerencial', 'codigoContaContabil', 'idCentroCusto', 'estornoAtivo', 'justificativa'];
    public $columnAlias     = ['idContaGerencial'           => 'Conta Gerencial',
                                'codigoContaContabil'       => 'Conta Contábil',
                                'idCentroCusto'             => 'Centro de Custo',
                                'estornoAtivo'              => 'Ativo',
                                'justificativa'             => 'Justificativa'];
    public $columnValue     = ['estornoAtivo'               => ['S' => 'Sim', 'N' => 'Não']];
    public $customType      = ['estornoAtivo'               => ['type'      => 'radio', 'values'    => ['S' => 'Sim', 'N' => 'Não']]];

    public $rules  = ['idContaGerencial'        => 'required_without_all:codigoContaContabil,idCentroCusto',
                      'codigoContaContabil'     => 'required_without_all:idContaGerencial,idCentroCusto',
                      'idCentroCusto'           => 'required_without_all:idContaGerencial,codigoContaContabil',
                      'estornoAtivo'            => 'required',
                      'justificativa'           => 'nullable'];

    public $rulesMessage    = [ 'idContaGerencial'      => 'Informe: CONTA GERENCIAL <b>OU</b> CONTA CONTÁBIL <b>OU</b> CENTRO DE CUSTO',
                                'codigoContaContabil'   => 'Informe: CONTA GERENCIAL <b>OU</b> CONTA CONTÁBIL <b>OU</b> CENTRO DE CUSTO',
                                'idCentroCusto'         => 'Informe: CONTA GERENCIAL <b>OU</b> CONTA CONTÁBIL <b>OU</b> CENTRO DE CUSTO',
                                'estornoAtivo'          => 'Informe se o parãmetro de estorno está ativo ou não'
                              ];
    
 
    public function vd_gerencialContaGerencial($id) {
        $viewData = GerencialContaGerencial::where('id', $id)->get();

        foreach ($viewData as $row => $data) {
            return $data->descricaoContaGerencial;
        }
    }

    public function vd_idContaGerencial($values) {
        $viewData = GerencialContaGerencial::whereIn('id', explode(',', $values))->get();

        $listData = '';
        foreach ($viewData as $row => $data) {
             $listData .= (!empty($listData) ? '<br>' : '').$data->codigoContaGerencial.' - '.$data->descricaoContaGerencial;
        }

        return $listData;
    }

    public function vd_gerencialCentroCusto($values) {
        $viewData = GerencialCentroCusto::whereIn('id', explode(',', $values))->get();

        $listData = '';
        foreach ($viewData as $row => $data) {
             $listData .= (!empty($listData) ? '<br>' : '').$data->siglaCentroCusto.' - '.$data->descricaoCentroCusto;
        }

        return $listData;
    }

    public function vd_codigoContaContabil($values) {
        $listData = '';
        if (!empty($values)) {
            $viewData   = DB::select("SELECT PlanoConta.PlanoConta_Codigo, PlanoConta.PlanoConta_ID, PlanoConta.PlanoConta_Descricao 
                                    FROM   GrupoRoma_DealernetWF..PlanoConta
                                    WHERE  PlanoConta.Estrutura_Codigo = '5'
                                    AND    PlanoConta.PlanoConta_Codigo in ($values)
                                    ORDER BY PlanoConta.PlanoConta_ID");

            foreach ($viewData as $row => $data) {
                $listData .= (!empty($listData) ? '<br>' : '').$data->PlanoConta_ID.' - '.$data->PlanoConta_Descricao;
            }
        }

        return $listData;
    }

    public function vd_codigoSubContaERP($values) {
        $listData = '';
        if (!empty($values)) {
            $viewData   = DB::select("SELECT SubConta.SubConta_Codigo, SubConta.SubConta_ID, SubConta.SubConta_Descricao 
                                    FROM   GrupoRoma_DealernetWF..SubConta
                                    WHERE  SubConta.Estrutura_Codigo = '5'
                                    AND    SubConta.SubConta_Codigo in ($values)
                                    ORDER BY SubConta.SubConta_ID");

            foreach ($viewData as $row => $data) {
                $listData .= (!empty($listData) ? '<br>' : '').$data->SubConta_ID.' - '.$data->SubConta_Descricao;
            }
        }
        return $listData;
    }

    public function fk_gerencialContaGerencial($columnValueName = 'id') {
        $fkData = GerencialContaGerencial::orderBy('codigoContaGerencial')->get();

        $formValues[] = ['', '--- selecione uma conta gerencial ---'];
        foreach($fkData as $row => $data) {
            $formValues[] = [$data->{$columnValueName}, $data->codigoContaGerencial.' - '.$data->descricaoContaGerencial];
        }

        return ['options' => $formValues, 'type' => '']; 
    }

    public function fk_gerencialCentroCusto($columnValueName = 'id') {
        $fkData = GerencialCentroCusto::orderBy('siglaCentroCusto')->get();

        $formValues[] = ['', '--- selecione um centro de custo ---'];
        foreach($fkData as $row => $data) {
            $formValues[] = [$data->{$columnValueName}, $data->siglaCentroCusto.' - '.$data->descricaoCentroCusto];
        }

        return ['options' => $formValues, 'type' => '']; 
    }

    /**
     *  custom form
     *  codigoContaContabil
     * 
     *  Retorna o código do formulário HTML para a coluna codigoContaContabil
     * 
     *  @param  mixed   values  : NULL
     *  @param  boolean multi   : Multiple selecte | FALSE
     * 
     *  @return string  HTML
     */
    public function custom_codigoContaContabil($values = NULL, $multi = FALSE) {
        $empresaERP = DB::select("SELECT PlanoConta.PlanoConta_Codigo, PlanoConta.PlanoConta_ID, PlanoConta.PlanoConta_Descricao 
                                  FROM   GrupoRoma_DealernetWF..PlanoConta
                                  WHERE  PlanoConta.Estrutura_Codigo = '5'
                                  --AND    PlanoConta.PlanoConta_TipoContabil IN ('RES','DSP','REC','ATV')
                                  AND    PlanoConta.PlanoConta_Nivel = 5
                                  ORDER BY PlanoConta.PlanoConta_ID");

        $htmlForm = "<select class='form-control' name='codigoContaContabil".($multi ? '[]\' multiple' : '\'')." id='codigoContaContabil'>";
        if (!$multi) $htmlForm .= "<option value=''>--- selecione uma Conta do Plano de Contas Contábil ---</option>";

        $values = explode(',', $values);
        foreach ($empresaERP as $row => $data) {
            $htmlForm .= "<option value='".$data->PlanoConta_Codigo."' ".(in_array($data->PlanoConta_Codigo, $values) ? 'selected' : '').">".
                            $data->PlanoConta_ID.'. '.$data->PlanoConta_Descricao.
                         "</option>";
        }
        $htmlForm .= "</select>";

        return $htmlForm.($multi ? "<small class='form-text text-muted'><b>CTRL+Click</b> para selecionar mais de uma opção</small>" : "");
    }

    /**
     *  custom form
     *  codigoSubContaERP
     * 
     *  Retorna o código do formulário HTML para a coluna codigoSubContaERP
     * 
     *  @param  mixed   values  : NULL
     *  @param  boolean multi   : Multiple selecte | FALSE
     * 
     *  @return string  HTML
     */
    public function custom_codigoSubContaERP($values = NULL, $multi = FALSE) {
        $empresaERP = DB::select("SELECT codigoSubConta     = SubConta.SubConta_Codigo,
                                         idSubConta         = SubConta.SubConta_ID,
                                         descricaoSubConta  = SubConta.SubConta_Descricao
                                  FROM   GrupoRoma_DealernetWF..SubConta
                                  WHERE  SubConta.Estrutura_Codigo = '5'
                                  AND    SubConta.TipoSubConta_Codigo not in(1,3)
                                  ORDER BY descricaoSubConta");

        $htmlForm = "<select class='form-control' name='codigoSubContaERP".($multi ? '[]\' multiple' : '\'')." id='codigoSubContaERP'>";
        if (!$multi) $htmlForm .= "<option value=''> --- </option>";

        $values = explode(',', $values);
        foreach ($empresaERP as $row => $data) {
            $htmlForm .= "<option value='".$data->codigoSubConta."' ".(in_array($data->codigoSubConta, $values) ? 'selected' : '').">".$data->descricaoSubConta." [".$data->idSubConta."]</option>";
        }
        $htmlForm .= "</select>";

        return $htmlForm.($multi ? "<small class='form-text text-muted'><b>CTRL+Click</b> para selecionar mais de uma opção</small>" : "");
    }
}
