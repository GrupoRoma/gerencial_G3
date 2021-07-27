<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\GerencialGrupoConta;
use Illuminate\Support\Facades\DB;

class GerencialContaGerencial extends Model
{
    use HasFactory;

    protected $table    = 'gerencialContaGerencial';

    protected $guarded  = ['id', 'idUsuario_created', 'rateioAdmCentral'];

    public $viewTitle       = 'Conta Gerencial';
    public $columnList      = ['codigoContaGerencial', 
                                'descricaoContaGerencial', 
                                'infoContaGerencial', 
                                'idGrupoConta', 
                                'analiseVariacao', 
                                'percentualVariacaoMaximo',
                                'valorVariacaoMaximo',
                                'rateioLogistica',
                                'quadricula',
                                'acumuladora',
                                'valoresVeiculo',
                                'contaGerencialAtiva'];

    public $columnAlias     = ['codigoContaGerencial'       => 'Código',
                                'descricaoContaGerencial'   => 'Descrição da Conta',
                                'infoContaGerencial'        => 'Informações Detalhadas',
                                'idGrupoConta'              => 'Grupo de Conta',
                                'analiseVariacao'           => 'Análise de Variação',
                                'percentualVariacaoMaximo'  => '% Máx. de Variação',
                                'valorVariacaoMaximo'       => '$ Máx. de Variação',
                                'rateioLogistica'           => 'Recebe Rateio do C.Custo de Logística',
                                'rateioAdmCentral'          => 'Recebe Rateio de ADM Central',
                                'valoresVeiculo'            => 'Recebe Valores de Veículos de',
                                'quadricula'                => 'Conta Gerencial da Quadrícula',
                                'acumuladora'               => 'Conta Acumuladora (controle de saldo)',
                                'contaGerencialAtiva'       => 'Conta Ativa'];

    public $columnValue     = ['contaGerencialAtiva'        => ['S' => 'Sim', 'N' => 'Não'],
                                /*'valoresVeiculo'            => ['RCD'   => 'Receita e/ou Devolução de venda',
                                                                'DSC'   => 'Desconto',
                                                                'CST'   => 'Custo',
                                                                'PIS'   => 'PIS',
                                                                'CFS'   => 'COFINS',
                                                                'ICM'   => 'ICMS',
                                                                'BEP'   => 'Bônus Empresa',
                                                                'BFB'   => 'Bônus Fábrica',
''  => '']*/];

    public $customType      = ['analiseVariacao'            => ['type'      => 'radio',
                                                                'values'    => ['S' => 'Sim', 'N' => 'Não']],
                               'contaGerencialAtiva'        => ['type'      => 'radio',
                                                                'values'    => ['S' => 'Sim', 'N' => 'Não']],
                               'valoresVeiculo'             => ['type'      => 'checkbox',
                                                                'values'    => ['BEP'   => 'Bônus Empresa',
                                                                                'BFB'   => 'Bônus Fábrica',
                                                                                'CFS'   => 'COFINS',
                                                                                'CST'   => 'Custo',
                                                                                'DSC'   => 'Desconto',
                                                                                'HBK'   => 'Hold Back',
                                                                                'ICM'   => 'ICMS',
                                                                                'PIS'   => 'PIS',
                                                                                'RCD'   => 'Receita e/ou Devolução de venda']],
                               'quadricula'                 => ['type'      => 'radio',
                                                                'values'    => ['S' => 'Sim', 'N' => 'Não']],
                               'rateioLogistica'            => ['type'      => 'radio',
                                                                'values'    => ['S' => 'Sim', 'N' => 'Não']],
                               'acumuladora'                => ['type'      => 'radio',
                                                                'values'    => ['S' => 'Sim', 'N' => 'Não']]
                              ];

    public $rules  = ['codigoContaGerencial'        => 'required|max:99999', 
                        'descricaoContaGerencial'   => 'required', 
                        'infoContaGerencial'        => 'nullable', 
                        'idGrupoConta'              => 'nullable', 
                        'analiseVariacao'           => 'required', 
                        'percentualVariacao'        => 'nullable',
                        'valorVariacaoMaximo'       => 'nullable',
                        'quadricula'                => 'required',
                        'acumuladora'               => 'required',
                        'contaGerencialAtiva'       => 'required'];

    public $rulesMessage    = [ 'codigoContaGerencial'      => 'CÓDIGO DA CONTA GERENCIAL: Obrigatório',
                                'descricaoContaGerencial'   => 'DESCRIÇÃO DA CONTA GERENCIAL: Obrigatório',
                                'analiseVariacao'           => 'ANÁLISE DE VARIAÇÃO: Obrigatório',
                                'quadricula'                => 'CONTA GERENCIAL DA QUADRÍCULA: Obrigatório',
                                'acumuladora'               => 'CONTA ACUMULADORA (controle de salo): Obrigatório',
                                'contaGerencialAtiva'       => 'CONTA ATIVA: Obrigatório'

                            ];
    
    public $fkValue     = ['idGrupoConta'       => 'descricaoGrupoConta'];
    
    public $infoContaGer;

    /**
     * Retona o Grupo de Conta associado à Conta Gerencial
     * 
     */
    public function gerencialGrupoConta() {
        return $this->HasOne('App\Models\GerencialGrupoConta');
    }


    public function vd_gerencialGrupoConta($id) {
        $viewData = GerencialGrupoConta::where('id', $id)->get();

        foreach ($viewData as $row => $data) {
            return $data->descricaoGrupoConta;
        }
    }

    public function fk_gerencialGrupoConta($columnValueName = 'id') {
        $fkData = GerencialGrupoConta::orderBy('codigoGrupoConta')->get();

        $formValues = [];
        foreach($fkData as $row => $data) {
            $formValues[] = [$data->{$columnValueName}, $data->codigoGrupoConta.' - '.$data->descricaoGrupoConta];
        }

        return ['options' => $formValues, 'type' => '']; 
    }

    /**
     *  getContasVeiculo
     *  Retorna as contas contábil e gerencial relacionadas à Receita, Custo, Impostos e Bônus de Veículos
     * 
     *  @return array       dbData
     * 
     */
    public function getContasVeiculos() {

        $dbData = DB::select("SELECT codigoContaGerencial	= G3_gerencialContaGerencial.id,
                                     contaGerencial		    = G3_gerencialContaGerencial.codigoContaGerencial,
                                     descricaoConta		    = G3_gerencialContaGerencial.descricaoContaGerencial,
                                     grupoContaGerencial	= G3_gerencialContaGerencial.idGrupoConta,
                                     tipoContaVeiculo		= G3_gerencialContaGerencial.valoresVeiculo,
                                     codigoContaContabil	= G3_gerencialContaContabil.codigoContaContabilERP,
                                     contaContabil		    = G3_gerencialContaContabil.contaContabil,
                                     receitaVeiculo		    = G3_gerencialContaContabil.receitaVeiculo,
                                     codigoCentroCusto	    = G3_gerencialCentroCusto.id,
                                     codigoCentroCustoERP	= G3_gerencialCentroCusto.codigoCentroCustoERP,
                                     descricaoCentroCusto   = G3_gerencialCentroCusto.descricaoCentroCusto,
                                     siglaCentroCusto		= G3_gerencialCentroCusto.siglaCentroCusto
                              FROM GAMA..G3_gerencialContaGerencial	    (nolock)
                              JOIN GAMA..G3_gerencialContaContabil	    (nolock) ON G3_gerencialContaContabil.idContaGerencial	= G3_gerencialContaGerencial.id
                              LEFT JOIN GAMA..G3_gerencialCentroCusto	(nolock) ON G3_gerencialCentroCusto.id					= G3_gerencialContaContabil.idCentroCusto
                              WHERE G3_gerencialContaGerencial.valoresVeiculo       IS NOT NULL
                              AND   G3_gerencialContaGerencial.contaGerencialAtiva  = 'S'
                              AND   G3_gerencialContaContabil.contaContabilAtiva    = 'S'
                              AND   G3_gerencialCentroCusto.centroCustoAtivo        = 'S'");
        
        if (count($dbData) == 0) return FALSE;

        $contas = [];
        foreach ($dbData as $row => $data) {
            $tipoConta = explode(',', $data->tipoContaVeiculo);

            foreach($tipoConta as $tipo) {
                $contas[$data->codigoCentroCusto][] = ['codigoContaGerencial'     => $data->codigoContaGerencial,
                                                       'contaGerencial'           => $data->contaGerencial,
                                                       'descricaoConta'           => $data->descricaoConta,
                                                       'codigoGrupoConta'         => $data->grupoContaGerencial,
                                                       'tipoContaVeiculo'         => $tipo,
                                                       'codigoContaContabil'      => $data->codigoContaContabil,
                                                       'contaContabil'            => $data->contaContabil,
                                                       'receitaVeiculo'           => $data->receitaVeiculo,
                                                       'codigoCentroCustoERP'     => $data->codigoCentroCustoERP,
                                                       'descricaoCentroCusto'     => $data->descricaoCentroCusto,
                                                       'siglaCentroCusto'         => $data->siglaCentroCusto];
            }
        }

        return $contas;
    }   //-- getContasVeiculos --//

    /**
     *  contaGerencialVeiculos
     *  retorna o código (ID) da conta gerencial identificada como
     *  sendo de valores de veículos (RCD: RECEITA / DEVOLUÇÃO, CST: CUSTO, ICM: ICMS,
     *                                PIS: PIS, CFS: COFINS, HBK: HOLD BACK, BEP: BÔNUS EMPRESA, BFB: BÔNUS FÁBRICA)
     * 
     *  @param  string  Tipo de conta
     * 
     *  @return mixed   integer => ID | boolean 
     * 
     */
    public function contaGerencialVeiculos($tipoConta) {
        $dbData = $this->whereNotNull('gerencialContaGerencial.valoresVeiculo')
                       ->where('gerencialContaGerencial.contaGerencialAtiva', 'S')
                       ->where('gerencialContaGerencial.valoresVeiculo', 'like', '%'.$tipoConta.'%')
                       ->get();
       
        return $dbData[0]->id ?? FALSE;
    }

    /**
     *  Retorna o id da conta gerencial a partir do código da conta
     * 
     *  @param  string  Código da conta [99999]
     * 
     *  @return integer ID da conta gerencial
     */
    public function getId($codigoConta) {
        $dbData = $this->where('codigoContaGerencial', $codigoConta)->get();

        return  $dbData[0]->id;
    }

    /**
    *  Retorna as informações detalhadas da conta para exibição
    */
    public function getInfoContaGerencial() {
        $dbData = DB::table('gerencialContaGerencial')
                    ->where('contaGerencialAtiva', 'S')
                    ->get();

        //$dbData     = $this->where('contaGerencialAtiva', 'S')->get();
        foreach ($dbData as $row => $contaGerencialData) {
            $index = stringMask($contaGerencialData->codigoContaGerencial, '##.###').' - '.$contaGerencialData->descricaoContaGerencial;

            $this->infoContaGer[$index] = $contaGerencialData->infoContaGerencial;
        }
    }
}

