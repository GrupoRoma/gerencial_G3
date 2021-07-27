<?php

namespace App\Http\Controllers;

use App\Models\GerencialParametroRateio;
use App\Models\Utils\Utilitarios;
use Hamcrest\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GerencialParametroRateioController extends Controller
{
    protected   $crudTitle = 'Parâmetros de Rateio';
    protected   $tableData;
    protected   $orderColumn;
    protected   $model;
    protected   $tableName;

    protected   $utils;

    public function __construct(Request $request)
    {
        $this->utils        = new Utilitarios;

        // Define a ordenação dos dados
        if (!isset($request->columnOrder) || empty($request->columnOrder) ) $request->columnOrder = 'descricaoParametro';
        $this->orderColumn = $request->columnOrder;
        
        $this->tableData  = GerencialParametroRateio::orderby($request->columnOrder)->paginate(10);  //->get();
        $this->model      = app('App\\Models\\GerencialParametroRateio');
        $this->tableName  = $this->model->getTable();
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
    public function store(GerencialParametroRateio $gerencialParametroRateio, Request $request)
    {
        // Validação
        $validator = Validator::make($request->all(), $this->model->rules);
        if ($validator->fails()) {
            $validate = $this->utils->validateMessage($validator->errors()->getMessages(), $this->model->rulesMessage);
            return response()->json($validate, 500);
        }

        foreach ($this->model->columnList as $column) {
            switch ($column) {
                case 'codigoEmpresaOrigem':
                case 'codigoEmpresaDestino':
                case 'codigoContaGerencialOrigem':
                case 'codigoContaGerencialDestino':
                case 'codigoCentroCustoOrigem':
                case 'codigoCentroCustoDestino':
                    $request->$column = implode(',', (array) $request->$column);
                    break;
            }
            $gerencialParametroRateio->$column = $request->$column;
        }

        $gerencialParametroRateio->save();
        $request->session()->flash('message', 'Dados gravados com sucesso!');

        //return view('crudView', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName]);
        redirect('parametroRateio/index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\GerencialParametroRateio  $gerencialParametroRateio
     * @return \Illuminate\Http\Response
     */
    public function show(GerencialParametroRateio $gerencialParametroRateio)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\GerencialParametroRateio  $gerencialParametroRateio
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        // Carrega os dados da tabela
        $this->tableData = GerencialParametroRateio::where('id', $id)->get();

        return view('crudForm', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName, 'id' => $id]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\GerencialParametroRateio  $gerencialParametroRateio
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validação
        $validator = Validator::make($request->all(), $this->model->rules);
        if ($validator->fails()) {
            $validate = $this->utils->validateMessage($validator->errors()->getMessages(), $this->model->rulesMessage);
            return response()->json($validate, 500);
        }

        $update = GerencialParametroRateio::find($id);

#echo $id;
#dd($request);

        foreach ($this->model->columnList as $column) {
            switch ($column) {
                case 'codigoEmpresaOrigem':
                case 'codigoEmpresaDestino':
                case 'codigoContaGerencialOrigem':
                case 'codigoContaGerencialDestino':
                case 'codigoCentroCustoOrigem':
                case 'codigoCentroCustoDestino':
                    $request->$column = implode(',', (array) $request->$column);
                    break;
            }
            $update->$column = $request->$column;
        }

        $update->save();
        $request->session()->flash('message', 'Dados atualizados com sucesso!');

        //return view('crudView', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName]);
        redirect('parametroRateio.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GerencialParametroRateio  $gerencialParametroRateio
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $del = GerencialParametroRateio::find($id);
        $del->delete();

        $this->tableData  = GerencialParametroRateio::orderby('descricaoParametro')->get();
        
        //return view('crudView', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName]);
        redirect('parametroRateio/index');
    }
}
