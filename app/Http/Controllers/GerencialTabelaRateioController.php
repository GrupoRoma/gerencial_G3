<?php

namespace App\Http\Controllers;

use App\Models\GerencialTabelaRateio;
use App\Models\GerencialTabelaRateioPercentual;
use App\Models\Utils\Utilitarios;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GerencialTabelaRateioController extends Controller
{
    protected   $crudTitle = 'Tabela de Rateio';
    protected   $tableData;
    protected   $orderColumn;
    protected   $tabelaPercentual; 
    protected   $model;
    protected   $tableName;

    protected   $utils;

    public function __construct(Request $request)
    {
        $this->utils        = new Utilitarios;

        // Define a ordenação dos dados
        if (!isset($request->columnOrder) || empty($request->columnOrder) ) $request->columnOrder = 'descricao';
        $this->orderColumn = $request->columnOrder;

        $this->tableData  = GerencialTabelaRateio::orderBy($request->columnOrder)->get();
        $this->model      = app('App\\Models\\GerencialTabelaRateio');
        $this->tableName  = $this->model->getTable();

        $this->tabelaPercentual = new GerencialTabelaRateioPercentual;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('crudView', ['tableData'    => $this->tableData, 
                                 'model'        => $this->model, 
                                 'tableName'    => $this->tableName,
                                 'orderColumn'  => $this->orderColumn]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('crudForm', ['tableData' => '', 'model' => $this->model, 'tableName' => $this->tableName, 'id' => '']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response    
     */
    public function store(GerencialTabelaRateio $gerencialTabela, Request $request)
    {
        // Validação
        $validator = Validator::make($request->all(), $this->model->rules);
        if ($validator->fails()) {
            $validate = $this->utils->validateMessage($validator->errors()->getMessages(), $this->model->rulesMessage);
            return response()->json($validate, 500);
        }

        foreach ($this->model->columnList as $column) {
            //ignora os dados de centro de custo e percentual associados
            if($column != 'centroCustoPerc')    $gerencialTabela->$column = $request->$column;
        }

        // Grava os dados da Tabela
        $gerencialTabela->save();
        
        // Grava os centros de custos e percentuais associados
        $centroCustoPercentuais = json_decode($request->centroCustoPerc);
        $percentuais            = [];
        foreach ($centroCustoPercentuais as $codigoEmpresa => $centrosCusto) {
            foreach ($centrosCusto as $codigoCentroCusto => $percentual) {
                $dataPercentual = new GerencialTabelaRateioPercentual();
                $dataPercentual->idTabela       = $gerencialTabela->id;
                $dataPercentual->idEmpresa      = $codigoEmpresa;
                $dataPercentual->idCentroCusto  = $codigoCentroCusto;
                $dataPercentual->percentual     = $percentual;
    
                $percentuais[]  = $dataPercentual;
            }
        }

/*         foreach ($centroCustoPercentuais as $codigoCentroCusto => $percentual) {
            $dataPercentual = new GerencialTabelaRateioPercentual();
            $dataPercentual->idTabela       = $gerencialTabela->id;
            $dataPercentual->idCentroCusto  = $codigoCentroCusto;
            $dataPercentual->percentual     = $percentual;

            $percentuais[]  = $dataPercentual;
        }
 */
        $gerencialTabela->gerencialTabelaRateioPercentual()->saveMany($percentuais);

        $request->session()->flash('message', 'Dados gravados com sucesso!');

        return redirect('tabelaRateio/index');
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BaseModels\GerencialTabelaRateio  $GerencialTabelaRateio
     * @return \Illuminate\Http\Response
     */
    public function show(GerencialTabelaRateio $GerencialTabela)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BaseModels\GerencialTabelaRateio  $GerencialTabelaRateio
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        // Carrega os dados da tabela
        $this->tableData = GerencialTabelaRateio::where('id', $id)->get();

        return view('crudForm', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName, 'id' => $id]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BaseModels\GerencialTabelaRateio  $GerencialTabelaRateio
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GerencialTabelaRateio $gerencialTabela, $id)
    {
        // Validação
        $validator = Validator::make($request->all(), $this->model->rules);
        if ($validator->fails()) {
            $validate = $this->utils->validateMessage($validator->errors()->getMessages(), $this->model->rulesMessage);
            return response()->json($validate, 500);
        }

        $update = GerencialTabelaRateio::find($id);

        foreach ($this->model->columnList as $column) {
            //ignora os dados de centro de custo e percentual associados
            if($column != 'centroCustoPerc')    $update->$column = $request->$column;
        }

        // Grava os dados da Tabela
        $update->save();
        
        // Grava os centros de custos e percentuais associados
        $this->tabelaPercentual->updatePercentuals($id, json_decode($request->centroCustoPerc));

        redirect('tabelaRateio.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BaseModels\GerencialTabelaRateio  $GerencialTabelaRateio
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $del = GerencialTabelaRateio::find($id);
        $del->delete();

        $this->tableData  = GerencialTabelaRateio::orderby('nomeAlternativo')->get();
        
        redirect('tabelaRateio.index');
    }
}
