<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GerencialEmpresas extends Model
{
    protected $table        = 'gerencialEmpresas';
    protected $guarded      = ['id', 'idUsuario_created'];
//    protected $connection   = 'sqlsrv';

    public $viewTitle       = 'Empresas';
    public $columnList      = ['codigoEmpresaERP',
                                'codigoRegional',
                                'nomeAlternativo', 
                                'codigoEmpresaDP',
                                'codigoFilialDP',
                                'areaFisica' ,
                                'vagasEstimadas',
                                'manobristas',
                                'validaIntegracaoContabil', 
                                'validaLoteContabil', 
                                'rateioAdmLocal', 
                                'rateioLogistica',
                                'empresaAtiva'];

    public $columnAlias     = ['codigoEmpresaERP'           => 'Código no ERP (Workflow)',
                                'codigoRegional'            => 'Regional',
                                'nomeAlternativo'           => 'Nome',
                                'codigoEmpresaDP'           => 'Empresa no DP (Rubi)',
                                'codigoFilialDP'            => 'Filial do DP (Rubi)',
                                'empresaAtiva'              => 'Ativa',
                                'areaFisica'                => 'Área (m2)',
                                'vagasEstimadas'            => 'Número da Vagas',
                                'manobristas'               => 'Número de Manobristas',
                                'validaIntegracaoContabil'  => 'Validar Int. Contábil',
                                'validaLoteContabil'        => 'Validar Lote Contábil',
                                'rateioAdmLocal'            => 'Rateio ADM Local',
                                'rateioLogistica'           => 'Rateio Logística'];

    public $columnValue     = ['empresaAtiva'               => ['S' => 'Sim', 'N' => 'Não'],
                                'validaIntegracaoContabil'  => ['S' => 'Sim', 'N' => 'Não'],
                                'validaLoteContabil'        => ['S' => 'Sim', 'N' => 'Não'],
                                'rateioAdmLocal'            => ['S' => 'Sim', 'N' => 'Não'],
                                'rateioLogistica'           => ['S' => 'Sim', 'N' => 'Não']];
    public $customType      = ['validaIntegracaoContabil'   => ['type'      => 'radio',
                                                                'values'    => ['S' => 'Sim', 'N' => 'Não']],
                               'validaLoteContabil'         => ['type'      => 'radio',
                                                                'values'    => ['S' => 'Sim', 'N' => 'Não']],
                               'rateioAdmLocal'             => ['type'      => 'radio',
                                                                'values'    => ['S' => 'Sim', 'N' => 'Não']],
                               'rateioLogistica'            => ['type'      => 'radio',
                                                                'values'    => ['S' => 'Sim', 'N' => 'Não']],
                               'empresaAtiva'               => ['type'      => 'radio',
                                                                'values'    => ['S' => 'Sim', 'N' => 'Não']]
                              ];
    public $rules  = ['codigoEmpresaERP'           => 'required|max:99999',
                      'codigoRegional'              => 'required',
                        'nomeAlternativo'           => 'required',
                        'codigoEmpresaDP'           => 'required',
                        'codigoFilialDP'            => 'nullable',
                        'empresaAtiva'              => 'nullable',
                        'areaFisica'                => 'nullable',
                        'vagasEstimadas'            => 'nullable',
                        'manobristas'               => 'nullable',
                        'validaIntegracaoContabil'  => 'nullable', 
                        'validaLoteContabil'        => 'nullable', 
                        'rateioAdmLocal'            => 'nullable', 
                        'rateioLogistica'           => 'nullable',
                        'codigoEmpresaDP'           => 'nullable',
                        'codigoFilialDP'            => 'nullable'];

    public $rulesMessage    = [ 'codigoEmpresaERP'          => 'CÓDIGO NO ERP (Workflow): Obrigatório',
                                'codigoRegional'            => 'REGIONAL: Obrigatório',
                                'nomeAlternativo'           => 'NOME: Obrigatório'
                              ];

    /**
     * Retona a regional associada
     */
    public function regional() {
        return $this->hasOne(GerencialRegional::class);
    }


    public function fk_gerencialRegional($columnValueName = 'id') {
        $fkData = GerencialRegional::orderBy('descricaoRegional')->get();

        $formValues[] = ['', '--- selecione uma regional ---'];
        foreach($fkData as $row => $data) {
            $formValues[] = [$data->{$columnValueName}, $data->id.' - '.$data->descricaoRegional];
        }

        return ['options' => $formValues, 'type' => '']; 
    }

    public function vd_gerencialRegional($values) {
        $viewData = GerencialRegional::whereIn('id', explode(',', $values))->get();

        foreach ($viewData as $row => $data) {
             return $data->descricaoRegional;
        }
    }

    /*
     *  Formulário com drop das empresas cadastradas no ERP
     */
    public function custom_codigoEmpresaERP($values = NULL, $multi = FALSE) {
        $empresaERP = DB::select('SELECT Empresa_Codigo, Empresa_Nome 
                                 FROM GrupoRoma_DealernetWF..Empresa
                                 WHERE Empresa_Ativo = 1
                                 ORDER BY Empresa_Nome', [1]);

        $htmlForm = "<select class='form-control' name='codigoEmpresaERP".($multi ? '[]\' multiple' : '\'')." id='codigoEmpresaERP'>";
        if (!$multi) $htmlForm .= "<option></option>";

        $values = explode(',', $values);
        foreach ($empresaERP as $row => $data) {
            $htmlForm .= "<option value='".$data->Empresa_Codigo."' ".(in_array($data->Empresa_Codigo, $values) ? 'selected' : '').">".
                            $data->Empresa_Codigo.'. '.$data->Empresa_Nome.
                         "</option>";
        }
        $htmlForm .= "</select>";

        return $htmlForm.($multi ? "<small class='form-text text-muted'><b>CTRL+Click</b> para selecionar mais de uma opção</small>" : "");
    }

    /*
     *  Drop das empresas cadastradas no DP (Ruby)
     */
     public function custom_codigoEmpresaDP($values = NULL, $multi = FALSE) {
        $empresaDP  = DB::select('SELECT codigoEmpresa  = r030emp.numemp,
                                         nomeEmpresa    = r030emp.nomemp,
                                         apelidoEmpresa = r030emp.nomemp
                                 FROM gama..r030emp     (nolock)
                                 ORDER BY apelidoEmpresa');

        $htmlForm  = "<select class='form-control updateFormData' name='codigoEmpresaDP".($multi ? '[]\' multiple' : '\'')." id='codigoEmpresaDP' ";
        $htmlForm .= "data-target='#dropFilialDP' data-method='filialDP' ";
        $htmlForm .= ">";

        if (!$multi) $htmlForm .= "<option></option>";

        $values = explode(',', $values);
        foreach ($empresaDP as $row => $data) {
            $htmlForm .= "<option value='".$data->codigoEmpresa."' ".(in_array($data->codigoEmpresa, $values) ? 'selected' : '').">".
                            $data->apelidoEmpresa.'( '.$data->codigoEmpresa.' )'.
                         "</option>";
        }
        $htmlForm .= "</select>";

        return $htmlForm.($multi ? "<small class='form-text text-muted'><b>CTRL+Click</b> para selecionar mais de uma opção</small>" : "");
    } 
    
    /*
     *  Drop das empresas cadastradas no DP (Ruby)
     */
     public function custom_codigoFilialDP($values = NULL, $multi = FALSE) {
        $empresaDP = DB::select('SELECT codigoEmpresa  = r030fil.numemp,
                                         codigoFilial   = r030fil.codfil,
                                         nomeFilial     = r030fil.nomfil
                                 FROM gama..r030fil     (nolock)
                                 '.(!empty($values) ? 'WHERE r030fil.numemp = '.$values : '').'
                                 ORDER BY nomeFilial');

        $htmlForm    = "<div id='dropFilialDP'>";
        $htmlForm   .= "<select class='form-control' name='codigoFilialDP".($multi ? '[]\' multiple' : '\'')." id='codigoFilialDP'>";
        if (!$multi) $htmlForm .= "<option></option>";

        $values = explode(',', $values);
        foreach ($empresaDP as $row => $data) {
            $htmlForm .= "<option value='".$data->codigoFilial."' ".(in_array($data->codigoFilial, $values) ? 'selected' : '').">".
                            $data->nomeFilial.'( '.$data->codigoFilial.' )'.
                         "</option>";
        }
        $htmlForm .= "</select>";
        $htmlForm .= "</div>";

        return $htmlForm.($multi ? "<small class='form-text text-muted'><b>CTRL+Click</b> para selecionar mais de uma opção</small>" : "");
    } 

    public function getEmpresa($codigo) {
        $dbData = $this->where('gerencialEmpresas.id', $codigo)
                       ->get();
        return $dbData[0] ?? FALSE;
    }

    public function getEmpresaERP($codigoERP) {
        $dbData = $this->where('gerencialEmpresas.codigoEmpresaERP', $codigoERP)
                       ->get();
        return $dbData[0] ?? FALSE;
    }
    
}
