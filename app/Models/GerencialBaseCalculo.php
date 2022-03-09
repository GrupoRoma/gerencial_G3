<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Type\Integer;

class GerencialBaseCalculo extends Model
{
    protected $table    = 'gerencialBaseCalculo';

    protected $guarded  = ['id', 'idUsuario_created'];

    public $viewTitle       = 'Base de Cálculo';
    public $columnList      = ['descricaoBaseCalculo', 
                                'valorManual', 
                                'baseCalculoAtiva'];

    public $columnAlias     = ['descricaoBaseCalculo'       => 'Descrição da Base de Cálculo',
                                'valorManual'               => 'Valor Informado Manualmente',
                                'baseCalculoAtiva'          => 'Base de Cálculo Ativa'];

    public $columnValue     = ['valorManual'                => ['S' => 'Sim', 'N' => 'Não'],
                                'baseCalculoAtiva'          => ['S' => 'Sim', 'N' => 'Não']];

    public $customType      = ['baseCalculoAtiva'           => ['type'      => 'radio',
                                                                'values'    => ['S' => 'Sim', 'N' => 'Não']],
                                'valorManual'               => ['type'      => 'radio',
                                                                'values'    => ['S' => 'Sim', 'N' => 'Não']]
                              ];

    public $rules           = [ 'descricaoBaseCalculo'    => 'required', 
                                'valorManual'           => 'nullable', 
                                'baseCalculoAtiva'      => 'required'];

    public $rulesMessage    = [ 'descricaoBaseCalculo'  => 'DESCRIÇÃO DA BASE DE CÁLCULO: Obrigatório',
                                'baseCalculoAtiva'      => 'BASE DE CÁLCULO ATIVA: Obrigatório'];

    // Valores das base de cálculo por base, empresa e centro de custo no seguinte modelo:
    // valoresBaseCalculo['NOME DA BASE DE CALCULO'] = ['TOTAL'         => valorTotalBaseCalculo,
    //                                                  'EMPRESA'       => ['CODIGO DA EMPRESA'         => valorTotalEmpresa
    //                                                                      'CODIGO CENTRO DE CUSTO'    => ValorTotalEmpresaCentroCusto],
    //                                                  'PESO'          => ['EMPRESA'           => percentualPesoEmpresa,
    //                                                                      'CCUSTO_EMPRESA'    => percentualPesoCCustoEmpresa  -- 'PERCENTUAL PESO DO CENTRO DE CUSTO x EMPRESA',
    //                                                                      'CCUSTO_TOTAL'      => percentualPesoCCustoTotal    -- 'PERCENTUAL PESO DO CENTRO DE CUSTO x TOTAL']
    //                                                  ]
    public  $valoresBaseCalculo = [];
    public  $errors;

    public function gerencialBaseCalculoContas() {
        return $this->hasMany('App\Models\GerencialBaseCalculoContas');
    }

    /**
     *  calculaBases
     *  Processa e calcula as bases de cálculo cadastradas
     * 
     *  @param  string  Mês dos Lançamentos para cálculo
     *  @param  string  Ano Lancamento
     *  @param  string Código da empresa ex: 1,2,3,4,...
     *  @param  string  Código do centro de custo ex: 1,2,3,4,...
     * 
     *  @return array
     */
    public function calculaBases(String $mesLancamento, String $anoLancamento, String $idEmpresa = NULL, String $idCentroCusto = NULL) {

        if (empty($mesLancamento) || empty($anoLancamento)) {
            $this->errors[] = ['errorTitle' => '<small>[log]</small> BASES DE CÁLCULO', 'error'   => 'Não foi informado período'];
            return FALSE;
        }

        $addConditions  = '';
        if (!empty($idEmpresa))     $addConditions  .= "AND   G3_gerencialLancamentos.idEmpresa     IN (".$idEmpresa.")";
        if (!empty($idCentroCusto)) $addConditions  .= "AND   G3_gerencialLancamentos.centroCusto   IN (".$idCentroCusto.")";

        $dbData = DB::select("SELECT baseCalculo		= G3_gerencialBaseCalculo.descricaoBaseCalculo,
                                     codigoBaseCalculo  = G3_gerencialBaseCalculo.id,
                                     codigoEmpresa		= G3_gerencialLancamentos.idEmpresa,
                                     Empresa			= G3_gerencialEmpresas.nomeAlternativo,
                                     contaGerencial     = G3_gerencialLancamentos.idContaGerencial,
                                     codigoCentroCusto	= G3_gerencialLancamentos.centroCusto,
                                     centroCusto		= G3_gerencialCentroCusto.descricaoCentroCusto,
                                     valorLancamento	= SUM(G3_gerencialLancamentos.valorLancamento)
                              FROM GAMA..G3_gerencialLancamentos			(nolock)
                              JOIN GAMA..G3_gerencialBaseCalculoContas	(nolock) ON G3_gerencialBaseCalculoContas.idContaGerencial		= G3_gerencialLancamentos.idContaGerencial
                              JOIN GAMA..G3_gerencialBaseCalculo			(nolock) ON G3_gerencialBaseCalculo.id							= G3_gerencialBaseCalculoContas.idBaseCalculo
                              JOIN GAMA..G3_gerencialEmpresas			(nolock) ON G3_gerencialEmpresas.id								= G3_gerencialLancamentos.idEmpresa
                              JOIN GAMA..G3_gerencialCentroCusto			(nolock) ON G3_gerencialCentroCusto.id							= G3_gerencialLancamentos.centroCusto
                              WHERE G3_gerencialBaseCalculo.baseCalculoAtiva    = 'S'
                              AND   G3_gerencialEmpresas.empresaAtiva		    = 'S'
                              AND   G3_gerencialCentroCusto.centroCustoAtivo	= 'S'
                              AND   G3_gerencialLancamentos.mesLancamento	    = $mesLancamento
                              AND   G3_gerencialLancamentos.anoLancamento	    = $anoLancamento
                              AND   G3_gerencialLancamentos.idTipoLancamento    <> 7

                              $addConditions

                              GROUP BY G3_gerencialBaseCalculo.descricaoBaseCalculo,
                                       G3_gerencialBaseCalculo.id, 
                                       G3_gerencialLancamentos.idEmpresa,
                                       G3_gerencialLancamentos.idContaGerencial,
                                       G3_gerencialEmpresas.nomeAlternativo,
                                       G3_gerencialLancamentos.centroCusto,
                                       G3_gerencialCentroCusto.descricaoCentroCusto
                              ORDER BY baseCalculo, codigoEmpresa, contaGerencial, codigoCentroCusto");

        if (count($dbData) == 0) {
            $this->errors[] = ['errorTitle' => '<small>[log]</small> BASE DE CÁLCULO', 'error'   => 'Não foram encontrados lançamentos no período informado'];
            return FALSE;
        }
        else {
            $calculo = [];
             foreach($dbData as $row => $data) {
                if (!isset($calculo[$data->codigoBaseCalculo]['VALOR_TOTAL'])) {
                    $calculo[$data->codigoBaseCalculo]['VALOR_TOTAL'] = 0;
                }
                if (!isset($calculo[$data->codigoBaseCalculo]['EMPRESA'][$data->codigoEmpresa])) {
                    $calculo[$data->codigoBaseCalculo]['EMPRESA'][$data->codigoEmpresa]['VALOR_BASE'] = 0;
                }

                if (!isset($calculo[$data->codigoBaseCalculo]['EMPRESA'][$data->codigoEmpresa]['CENTRO_CUSTO'][$data->codigoCentroCusto])) {
                    $calculo[$data->codigoBaseCalculo]['EMPRESA'][$data->codigoEmpresa]['CENTRO_CUSTO'][$data->codigoCentroCusto]['VALOR_BASE'] = 0;
                }

                $calculo[$data->codigoBaseCalculo]['VALOR_TOTAL']                                                                   += $data->valorLancamento;
                $calculo[$data->codigoBaseCalculo]['EMPRESA'][$data->codigoEmpresa]['VALOR_BASE']                                   += $data->valorLancamento;
                $calculo[$data->codigoBaseCalculo]['EMPRESA'][$data->codigoEmpresa]['CENTRO_CUSTO'][$data->codigoCentroCusto]['VALOR_BASE']    += $data->valorLancamento;
            }

            //  calcula o fator/peso por empresa
            //  VALOR BASE
            //  VALOR DOS CENTROS CUSTOS (detalhe)
            //  PESO / FATOR DA EMPRESA EM RELAÇÃO AO TOTAL DA BASE DE CÁLCULO
            foreach ($calculo as $base => $val) {
                $valorTotalBase = $val['VALOR_TOTAL'];
                foreach ($val['EMPRESA'] as $codigoEmpresa => $valEmpresa ) {
                    if ($valorTotalBase <> 0) {
                        $calculo[$base]['EMPRESA'][$codigoEmpresa]['PESO'] = $valEmpresa['VALOR_BASE'] / $valorTotalBase;
                    }
                    else {
                        $calculo[$base]['EMPRESA'][$codigoEmpresa]['PESO'] = $valEmpresa['VALOR_BASE'];
                    }

                    // (detalhe)
                    // Calcula os valores dos centros de custos que possuem lançamentos
                    // VALOR BASE DO CENTRO DE CUSTO
                    // PESO / FATOR EM RELAÇÃO À EMPRESA
                    // PESO / FATOR EM RELAÇÃO DO TOTAL DA BASE DE CÁLCULO
                    foreach ($valEmpresa['CENTRO_CUSTO'] as $codigoCcusto => $valCcusto) {

                        if ($valEmpresa['VALOR_BASE'] <> 0) {
                            $calculo[$base]['EMPRESA'][$codigoEmpresa]['CENTRO_CUSTO'][$codigoCcusto]['PESO_EMPRESA']    = $valCcusto['VALOR_BASE'] / $valEmpresa['VALOR_BASE'];
                        }
                        else {
                            $calculo[$base]['EMPRESA'][$codigoEmpresa]['CENTRO_CUSTO'][$codigoCcusto]['PESO_EMPRESA']    = $valCcusto['VALOR_BASE'];
                        }

                        if ($valorTotalBase <> 0) {
                            $calculo[$base]['EMPRESA'][$codigoEmpresa]['CENTRO_CUSTO'][$codigoCcusto]['PESO_TOTAL']      = $valCcusto['VALOR_BASE'] / $valorTotalBase;
                        }
                        else {
                            $calculo[$base]['EMPRESA'][$codigoEmpresa]['CENTRO_CUSTO'][$codigoCcusto]['PESO_TOTAL']      = $valCcusto['VALOR_BASE'];
                        }
                    }
                }
            } 

            return $calculo;
        }


    }

}
