<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GerencialUsuario extends Model
{
    use HasFactory;

    protected $table    = 'gerencialUsuarios';
    protected $guarded  = ['id'];

    public $viewTitle       = 'Permissões de Usuário';
    public $columnList      = ['idUsuario',
                                'tipoUsuarioGerencial',
                                'empresasAcesso', 
                                'centrosCustoAcesso', 
                                'contaGerencialAcesso', 
                                'gerencialTVI', 
                                'permissaoAtiva'];

    public $columnAlias     = ['idUsuario'                  => 'Usuário',
                                'tipoUsuarioGerencial'      => 'Tipo de Usuário',
                                'empresasAcesso'            => 'Empresas com Acesso',
                                'centrosCustoAcesso'        => 'Centros de Custo com Acesso',
                                'contaGerencialAcesso'      => 'Contas Gerenciais com Acesso',
                                'gerencialTVI'              => 'Permite registrar TVI',
                                'permissaoAtiva'            => 'Permissao de Acesso Ativa'];

    public $columnValue     = ['gerencialTVI'               => ['TVI' => '[TVI] Registro TVI', 'GER' => '[GER] Acesso ao Gerencial', 'AMB' => '[AMB] Registro de TVI e Acesso ao Gerencial'],
                                'permissaoAtiva'            => ['S' => 'Sim', 'N' => 'Não'],
                                'tipoUsuarioGerencial'      => ['OPE' => 'OPERADOR', 'GST' => 'GESTOR']];

    public $customType      = ['gerencialTVI'               => ['type'      => 'radio',
                                                                'values'    => ['TVI' => '[TVI] Registro TVI', 'GER' => '[GER] Acesso ao Gerencial', 'AMB' => '[AMB] Registro de TVI e Acesso ao Gerencial']],
                                'permissaoAtiva'            => ['type'      => 'radio',
                                                                'values'    => ['S' => 'Sim', 'N' => 'Não']],
                                'tipoUsuarioGerencial'      => ['type'      => 'radio',
                                                                'values'    => ['OPE' => 'OPERADOR', 'GST' => 'GESTOR']]
                              ];

    public $rules           = ['idUsuario'              => 'required', 
                                'tipoUsuarioGerencial'  => 'required', 
                                'empresasAcesso'        => 'nullable', 
                                'centrosCustoAcesso'    => 'nullable', 
                                'contaGerencialAcesso'  => 'nullable',
                                'gerencialTVI'          => 'nullable',
                                'permissaoAtiva'        => 'required'];

    public $rulesMessage    = [ 'idUsuario'             => 'Selecione o usuário',
                                'tipoUsuarioGerencial'  => 'Selecione o tipo de usuário',
                                'permissaoAtiva'        => 'Informe se a permissão está ativa ou não'
                              ];


    public function fk_users($columnValueName = 'id') {
        $fkData = DB::select("SELECT * FROM GAMA..users ORDER BY name");

//        $fkData = User::orderBy('name')->get();

        $formValues = [];
        foreach($fkData as $row => $data) {
            $formValues[] = [$data->{$columnValueName}, $data->name];
        }

        return ['options' => $formValues, 'type' => '']; 
    }

    /*
     *  Formulário com drop de empresas para seleção multipla (default)
     */
    public function custom_empresasAcesso($values = NULL, $multi = TRUE) {
        $empresas = GerencialEmpresas::where('empresaAtiva', 'S')->orderBy('nomeAlternativo')->get();

        $htmlForm = "<select name='empresasAcesso".($multi ? '[]\' multiple' : '')." id='empresasAcesso' class='form-control'>";
        if (!$multi) $htmlForm .= "<option>--- selecione uma empresa ---</option>";

        $values = explode(',', $values);

        foreach ($empresas as $row => $data) {
            $htmlForm .= "<option value='".$data->id."' ".(in_array($data->id, $values) ? 'selected' : '').">".
                            $data->nomeAlternativo.
                         "</option>";
        }

        $htmlForm .= "</select>";

        return $htmlForm.($multi ? "<small class='form-text text-muted'><b>CTRL+Click</b> para selecionar mais de uma opção</small>" : "");
    }

    /*
     *  Formulário com drop de centros de custo para seleção multipla (default)
     */
    public function custom_centrosCustoAcesso($values = NULL, $multi = TRUE) {
        $centrosCuto = GerencialCentroCusto::where('centroCustoAtivo', 'S')->orderBy('siglaCentroCusto')->get();
        $selecteds = null;

        $htmlForm = "<select class='form-control' name='centrosCustoAcesso".($multi ? '[]\' multiple' : '')." id='centrosCustoAcesso'>";
        if (!$multi) $htmlForm .= "<option>--- selecione um centro de custo ---</option>";

        $values = explode(',', $values);
        foreach ($centrosCuto as $row => $data) {
            $htmlForm .= "<option value='".$data->id."' ".(in_array($data->id, $values) ? 'selected' : '').">".
                            $data->siglaCentroCusto.' - '.$data->descricaoCentroCusto.
                         "</option>";
        }

        $htmlForm .= "</select>";

        return $htmlForm.($multi ? "<small class='form-text text-muted'><b>CTRL+Click</b> para selecionar mais de uma opção</small>" : "");
    }

    /*
     *  Formulário com drop de contas gerenciais para seleção multipla (default)
     */
    public function custom_contaGerencialAcesso($values = NULL, $multi = TRUE) {
        $contaGerencial = GerencialContaGerencial::where('contaGerencialAtiva', 'S')->orderBy('codigoContaGerencial')->get();
        $selecteds = null;

        $htmlForm = "<select class='form-control' name='contaGerencialAcesso".($multi ? '[]\' multiple' : '')." id='contaGerencialAcesso'>";
        if (!$multi) $htmlForm .= "<option>--- selecione uma conta gerencial ---</option>";

        $values = explode(',', $values);

        foreach ($contaGerencial as $row => $data) {
            $htmlForm .= "<option value='".$data->id."' ".(in_array($data->id, $values) ? 'selected' : '').">".
                            $data->codigoContaGerencial.' - '.$data->descricaoContaGerencial.
                         "</option>";
        }

        $htmlForm .= "</select>";

        
        return $htmlForm.($multi ? "<small class='form-text text-muted'><b>CTRL+Click</b> para selecionar mais de uma opção</small>" : ""); 
    }

    public function vd_users($id) {
        $viewData   = DB::select("SELECT * FROM GAMA..users WHERE id = ".$id);

        //$viewData = User::where('id', $id)->get();

        foreach ($viewData as $row => $data) {
            return $data->name;
        }
    }

    /**
     * Carrega e define as permissões do usuário logado na sessão
     * 
     */
    public function setUserPerms() 
    {
        $dbData     = GerencialUsuario::where('idUsuario', session('userID'))->get();

        if (count($dbData) == 0) return FALSE;

        foreach($dbData as $row => $userData) {
            $sessionData    = [ '_GER_empresasAcesso'        => ($userData->tipoUsuarioGerencial !== 'OPE' ? explode(',', $userData->empresasAcesso) : ''),
                                '_GER_centrosCustoAcesso'    => ($userData->tipoUsuarioGerencial !== 'OPE' ? explode(',', $userData->centrosCustoAcesso) : ''),
                                '_GER_contaGerencialAcesso'  => ($userData->tipoUsuarioGerencial !== 'OPE' ? explode(',', $userData->contaGerencialAcesso) : ''),
                                '_GER_tipoUsuarioGerencial'  => $userData->tipoUsuarioGerencial,
                                '_GER_TVI'                   => $userData->gerencialTVI
                              ];
            session()->put($sessionData);
        }

        if (session('_GER_empresasAcesso') !== NULL)    return TRUE;
        else                                            return FALSE;

    }
}
