<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Nullable;

class GerencialContaContabil extends Model
{
    //use HasFactory;
    protected $table    = 'gerencialContaContabil';

    protected $guarded  = ['id'];

    public $viewTitle       = 'Conta Contábil x Conta Gerencial';
    public $columnList      = ['idContaGerencial', 
                                'codigoContaContabilERP', 
                                //'contaContabil', 
                                'codigoSubContaERP',
                                'contaContabilAtiva', 
                                'receitaVeiculo',
                                'idCentroCusto'];

    public $columnAlias     = ['idContaGerencial'           => 'Conta Gerencial',
                                'codigoContaContabilERP'    => 'Código da Conta (ERP)',
                                //'contaContabil'             => 'Número da Conta Contábil',
                                'codigoSubContaERP'         => 'Sub-Conta',
                                'contaContabilAtiva'        => 'Conta Contábil Ativa',
                                'receitaVeiculo'            => 'Conta de Receita de Veículos (VN)',
                                'idCentroCusto'             => 'Centro de Custo Associado'];

    public $columnValue     = ['contaContabilAtiva' => ['S' => 'Sim', 'N' => 'Não'],
                               'receitaVeiculo'     => ['S' => 'Sim', 'N' => 'Não']];

    public $customType      = ['contaContabilAtiva' => ['type'      => 'radio',
                                                        'values'    => ['S' => 'Sim', 'N' => 'Não']],
                               'receitaVeiculo'     => ['type'      => 'radio',
                                                        'values'    => ['S' => 'Sim', 'N' => 'Não']]
                              ];

    public $rules  = ['idContaGerencial'            => 'required', 
                        'codigoSubContaERP'         => 'nullable',
                        'contaContabilAtiva'        => 'required', 
                        'receitaVeiculo'            => 'required',
                        'idCentroCusto'             => 'nullable'];
    
    public $rulesMessage    = [ 'idContaGerencial'          => 'CONTA GERENCIAL: Obrigatório',
                                'codigoContaContabilERP'    => 'CÓDIGO DA CONTA (ERP): Obrigatório ou já existe uma associação desta conta contábil com a conta gerencial informada',
                                'contaContabilAtiva'        => 'CONTA CONTABIL ATIVA: Obrigatório',
                                'receitaVeiculo'            => 'CONTA DE RECEITA DE VEÍCULO: Obrigatório'];

    public function vd_gerencialContaGerencial($id) {
        $viewData = GerencialContaGerencial::where('id', $id)->get();

        foreach ($viewData as $row => $data) {
            return $data->codigoContaGerencial.'.'.$data->descricaoContaGerencial;
        }
    }

    public function fk_gerencialContaGerencial($columnValueName = 'id') {
        $fkData = GerencialContaGerencial::orderBy('codigoContaGerencial')->get();

        $formValues = [];
        foreach($fkData as $row => $data) {
            $formValues[] = [$data->{$columnValueName}, $data->codigoContaGerencial.' - '.$data->descricaoContaGerencial];
        }

        return ['options' => $formValues, 'type' => '']; 
    }

    public function vd_gerencialCentroCusto($id) {
        $viewData = GerencialCentroCusto::where('id', $id)->get();

        foreach ($viewData as $row => $data) {
            return $data->siglaCentroCusto.'.'.$data->descricaoCentroCusto;
        }
    }

    public function fk_gerencialCentroCusto($columnValueName = 'id') {
        $fkData = GerencialCentroCusto::orderBy('descricaoCentroCusto')->get();

        $formValues = [];
        foreach($fkData as $row => $data) {
            $formValues[] = [$data->{$columnValueName}, $data->siglaCentroCusto.' - '.$data->descricaoCentroCusto];
        }

        return ['options' => $formValues, 'type' => '']; 
    }

    /*
     *  Formulário com drop das empresas cadastradas no ERP
     */
    public function custom_codigoContaContabilERP($values = NULL, $multi = FALSE) {
        $empresaERP = DB::select("SELECT PlanoConta.PlanoConta_Codigo, PlanoConta.PlanoConta_ID, PlanoConta.PlanoConta_Descricao 
                                  FROM   GrupoRoma_DealernetWF..PlanoConta
                                  WHERE  PlanoConta.Estrutura_Codigo = '5'
                                  --AND    PlanoConta.PlanoConta_TipoContabil IN ('RES','DSP','REC','ATV')
                                  AND    PlanoConta.PlanoConta_Nivel = 5
                                  ORDER BY PlanoConta.PlanoConta_ID");

        $htmlForm = "<select class='form-control' name='codigoContaContabilERP".($multi ? '[]\' multiple' : '\'')." id='codigoContaContabilERP'>";
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

    /*
     *  Formulário com drop das SubContas do Plano de Contas Contábil
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


    /**
     *  saldoContabil
     *  retorna os dados e valor do saldo contábil de uma conta ou mais contas em um período específico
     * 
     *  @param  string      periodo (mm/YYYY)
     *  @param  array       #protected listaEmpresas    (setEmpresas)
     *  @param  array       #protected listaContas      (setContaContabil)
     *  @param  array       #protected listaCentroCusto (setCentroCusto)
     * 
     *  @return mixed       dbData | FALSE: sem saldo contabil
     * 
     */
    public function saldoContabil(string $periodo) {
        $periodo    = str_pad($periodo, 7,"0", STR_PAD_LEFT);
        $periodoMes = substr($periodo,0,2);
        $periodoAno = substr($periodo,3,4);

    }

    /**
     *  Retorna os dados da conta contábil a partir do código da conta no ERP
     * 
     *  @param  integer Código (ID) da conta cobtábil no ERP
     * 
     *  @return object  Database row data || FALSE: se não existir
     * 
     */
    function getContaContabil($codigoERP) {
        $contaERP   = DB::select("  SELECT PlanoConta_Codigo,
                                            PlanoConta_Descricao,
                                            PlanoConta_ID,
                                            PlanoConta_Tipo,
                                            PlanoConta_Nivel,
                                            PlanoConta_Natureza,
                                            PlanoConta_PlanoContaCodPai
                                    FROM GrupoRoma_DealernetWF..PlanoConta
                                    WHERE PlanoConta.PlanoConta_Ativo		= 1
                                    AND   PlanoConta.Estrutura_Codigo		= 5
                                    AND   PlanoConta.PlanoConta_Codigo		= ".$codigoERP);

        if (isset($contaERP[0]))    return $contaERP[0];
        else                        return FALSE;
    }


    /**
     *  validateUnique
     *  Retorna a existência ou não de registro de associação de conta contábil x conta gerencial
     *  evitando a duplicidade de registros
     * 
     *  @param      integer     idContaGerencial
     *  @param      integer     codigoContaContabil
     * 
     *  @return     boolean     (TRUE = Found | FALSE = Not Found)
     */
    public function validateUnique($idContaGerencial, $codigoContaContabil) {
        return GerencialContaContabil::where('idContaGerencial', $idContaGerencial)
                                     ->where('codigoContaContabilERP', $codigoContaContabil)
                                     ->get();
    }
}
