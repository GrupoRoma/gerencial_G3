<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GerencialRegional extends Model
{
    use HasFactory;

    protected $table    = 'gerencialRegional';
    protected $guarded      = ['id'];

    public $viewTitle       = 'Regionais';
    public $columnList      = ['descricaoRegional', 
                                'codigoEmpresaVendaExterna', 
                                'codigoVendasExternasERP', 
                                'codigoEmpresaVeiculosUsados',
                                'codigoEmpresaRateioLogistica' ,
                                'tipoTituloBonusFabrica', 
                                'codigoRegionalAntigo'];

    public $columnAlias     = ['descricaoRegional'              => 'Regional',
                                'codigoEmpresaVendaExterna'     => 'Alocar Vendas Externas em ',
                                'codigoVendasExternasERP'       => 'Código de vendedores VE',
                                'codigoEmpresaVeiculosUsados'   => 'Alocar Venda de VU em',
                                'codigoEmpresaRateioLogistica'  => 'Alocar Rateio da Logística em',
                                'tipoTituloBonusFabrica'        => 'Tipo Título Bônus Fábrica',
                                'codigoRegionalAntigo'          => 'Antigo Código da Regional'];

    public $columnValue     = [];
    public $customType      = [];

    public $rules           = ['descricaoRegional'              => 'required', 
                                'codigoEmpresaVendaExterna'     => 'required', 
                                'codigoVendasExternasERP'       => 'nullable', 
                                'codigoEmpresaVeiculosUsados'   => 'nullable',
                                'codigoEmpresaRateioLogistica'  => 'nullable',
                                'tipoTituloBonusFabrica'        => 'required', 
                                'codigoRegionalAntigo'          => 'nullable'];

    public $rulesMessage    = ['descricaoRegional'              => 'REGIONAL: Obrigatório', 
                                'codigoEmpresaVendaExterna'     => 'ALOCAR VENDAS EXTERNAS EN: Obrigatório', 
                                'tipoTituloBonusFabrica'        => 'TIPO DE TÍTULO BÕNUS FÁBRICA: Obrigatório'];

    public function vd_gerencialEmpresas($id) {
        $viewData = GerencialEmpresas::where('id', $id)->get();

        foreach ($viewData as $row => $data) {
            return $data->nomeAlternativo;
        }
    }

    public function fk_gerencialEmpresas($columnValueName = 'id') {
        $fkData = GerencialEmpresas::orderBy('nomeAlternativo')->get();

        $formValues = [];
        foreach($fkData as $row => $data) {
            $formValues[] = [$data->{$columnValueName}, $data->nomeAlternativo];
        }

        return ['options' => $formValues, 'type' => '']; 
    }
    
/*    public function custom_codigoEmpresaVendaExterna($values = NULL, $multi = FALSE) {
        $customData = GerencialEmpresas::where('gerencialEmpresas.empresaAtiva', 'S')
                                       ->orderBy('gerencialEmpresas.nomeAlternativo')
                                       ->get();
        
        $htmlForm = "<select class='form-control' name='codigoEmpresaVendaExterna".($multi ? '[]\' multiple' : '\'')." id='codigoEmpresaVendaExterna'>";
        if (!$multi) $htmlForm .= "<option></option>";

        $values = explode(',', $values);
        foreach ($customData as $row => $data) {
            $htmlForm .= "<option value='".$data->id."' ".(in_array($data->id, $values) ? 'selected' : '').">".
                            $data->nomeAlternativo.
                         "</option>";
        }
        $htmlForm .= "</select>";

        return $htmlForm.($multi ? "<small class='form-text text-muted'><b>CTRL+Click</b> para selecionar mais de uma opção</small>" : "");
    }   //-- custom_codigoEmpresaVendaExterna --//

    public function custom_codigoEmpresaVeiculosUsados($values = NULL, $multi = FALSE) {
        $customData = GerencialEmpresas::where('gerencialEmpresas.empresaAtiva', 'S')
                                       ->orderBy('gerencialEmpresas.nomeAlternativo')
                                       ->get();
        
        $htmlForm = "<select class='form-control' name='codigoEmpresaVeiculosUsados".($multi ? '[]\' multiple' : '\'')." id='codigoEmpresaVeiculosUsados'>";
        if (!$multi) $htmlForm .= "<option>--- selecione uma Empresa ---</option>";

        $values = explode(',', $values);
        foreach ($customData as $row => $data) {
            $htmlForm .= "<option value='".$data->id."' ".(in_array($data->id, $values) ? 'selected' : '').">".
                            $data->nomeAlternativo.
                         "</option>";
        }
        $htmlForm .= "</select>";

        return $htmlForm.($multi ? "<small class='form-text text-muted'><b>CTRL+Click</b> para selecionar mais de uma opção</small>" : "");
    }   //-- custom_codigoEmpresaVeiculosUsados --//
*/
    public function custom_codigoVendasExternasERP($values = NULL, $multi = TRUE) {
        $customData = DB::select("SELECT codigoUsuario	= Usuario_Codigo,
                                         nomeUsuario	= Usuario_Nome
                                  FROM GrupoRoma_DealernetWF..Usuario	(nolock)
                                  WHERE Usuario_Ativo = 1
                                  ORDER BY usuario_Nome");
        
        $htmlForm = "<select class='form-control' name='codigoVendasExternasERP".($multi ? '[]\' multiple' : '\'')." id='codigoVendasExternasERP'>";
        if (!$multi) $htmlForm .= "<option></option>";

        $values = explode(',', $values);
        foreach ($customData as $row => $data) {
            $htmlForm .= "<option value='".$data->codigoUsuario."' ".(in_array($data->codigoUsuario, $values) ? 'selected' : '').">".
                            $data->nomeUsuario.
                         "</option>";
        }
        $htmlForm .= "</select>";

        return $htmlForm.($multi ? "<small class='form-text text-muted'><b>CTRL+Click</b> para selecionar mais de uma opção</small>" : "");
    }   //-- custom_codigoVendasExternasERP --//

    public function custom_tipoTituloBonusFabrica($values = NULL, $multi = FALSE) {
        $customData = DB::select("SELECT codigoTipoTitulo	= TipoTitulo.TipoTitulo_Codigo,
                                         descricaoTitulo	= TipoTitulo.TipoTitulo_Descricao
                                  FROM GrupoRoma_DealernetWF..TipoTitulo	(nolock)
                                  WHERE TipoTitulo.TipoTitulo_Ativo = 1 
                                  AND   TipoTitulo.TipoTitulo_ExigeIdentificacaoVeiculo = 1 
                                  AND   TipoTitulo.TipoTitulo_PermissaoUso = 'R'
                                  ORDER BY TipoTitulo.TipoTitulo_Descricao");
        
        $htmlForm = "<select class='form-control' name='tipoTituloBonusFabrica".($multi ? '[]\' multiple' : '\'')." id='tipoTituloBonusFabrica'>";
        if (!$multi) $htmlForm .= "<option></option>";

        $values = explode(',', $values);
        foreach ($customData as $row => $data) {
            $htmlForm .= "<option value='".$data->codigoTipoTitulo."' ".(in_array($data->codigoTipoTitulo, $values) ? 'selected' : '').">".
                            $data->descricaoTitulo.
                         "</option>";
        }
        $htmlForm .= "</select>";

        return $htmlForm.($multi ? "<small class='form-text text-muted'><b>CTRL+Click</b> para selecionar mais de uma opção</small>" : "");
    }   //-- custom_tipoTituloBonusFabrica --//

    public function custom_codigoRegionalAntigo($values = NULL, $multi = FALSE) {
        $customData = DB::select("SELECT codigoRegional	    = SGA_REGIONAIS.COD_REG,
                                         descricaoRegional	= SGA_REGIONAIS.DESCRICAO
                                  FROM GAMA..SGA_REGIONAIS	(nolock)
                                  ORDER BY descricaoRegional");
        
        $htmlForm = "<select class='form-control' name='codigoRegionalAntigo".($multi ? '[]\' multiple' : '\'')." id='codigoRegionalAntigo'>";
        if (!$multi) $htmlForm .= "<option value=''></option>";

        $values = explode(',', $values);
        foreach ($customData as $row => $data) {
            $htmlForm .= "<option value='".$data->codigoRegional."' ".(in_array($data->codigoRegional, $values) ? 'selected' : '').">".
                            $data->descricaoRegional.
                         " [G2]</option>";
        }
        $htmlForm .= "</select>";

        return $htmlForm.($multi ? "<small class='form-text text-muted'><b>CTRL+Click</b> para selecionar mais de uma opção</small>" : "");
    }   //-- custom_tipoTituloBonusFabrica --//

}
