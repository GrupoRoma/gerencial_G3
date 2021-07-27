<?php

namespace App\Http\Controllers;

use App\Models\GerencialOutrasContas;
use App\Models\Utils\Utilitarios;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GerencialOutrasContasController extends Controller
{

    protected   $crudTitle = 'Outras Contas Contábeis';
    protected   $tableData;
    protected   $orderColumn;
    protected   $model;
    protected   $tableName;

    protected   $utils;
    
    public function __construct(Request $request)
    {
        $this->utils        = new Utilitarios;

        // Define a ordenação dos dados
        if (!isset($request->columnOrder) || empty($request->columnOrder) ) $request->columnOrder = 'historicoPadrao';
        $this->orderColumn = $request->columnOrder;

        $this->tableData  = GerencialOutrasContas::orderBy($request->columnOrder)->get();
        $this->model      = app('App\\Models\\GerencialOutrasContas');
        $this->tableName  = $this->model->getTable();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('crudView', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName]);
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
    public function store(GerencialOutrasContas $outrasContas, Request $request)
    {
        // Validação
        $validator = Validator::make($request->all(), $this->model->rules);
        if ($validator->fails()) {
            $validate = $this->utils->validateMessage($validator->errors()->getMessages(), $this->model->rulesMessage);
            return response()->json($validate, 500);
        }

        foreach ($this->model->columnList as $column) {
            $outrasContas->$column = trim($request->$column);
        }
        $outrasContas->destino = '{"empresaDestino":'.$request->empresaDestino.', "proporcaoDestino":'.$request->proporcaoDestino.', "centroCustoDestino":'.$request->centroCustoDestino.'}';

        $outrasContas->save();
        $request->session()->flash('message', 'Dados gravados com sucesso!');

        //return view('crudView', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName]);
        redirect('outraContas.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\GerencialOutrasContas  $gerencialOutrasContas
     * @return \Illuminate\Http\Response
     */
    public function show(GerencialOutrasContas $gerencialOutrasContas)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\GerencialOutrasContas  $gerencialOutrasContas
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        // Carrega os dados da tabela
        $this->tableData = GerencialOutrasContas::where('id', $id)->get();

        return view('crudForm', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName, 'id' => $id]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\GerencialOutrasContas  $gerencialOutrasContas
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GerencialOutrasContas $outrasContas, $id)
    {
        // Validação
        $validator = Validator::make($request->all(), $this->model->rules);
        if ($validator->fails()) {
            $validate = $this->utils->validateMessage($validator->errors()->getMessages(), $this->model->rulesMessage);
            return response()->json($validate, 500);
        }

        $update = GerencialOutrasContas::find($id);

        foreach ($this->model->columnList as $column) {
            $update->$column = trim($request->$column);
        }
        $update->destino = '{"empresaDestino":'.$request->empresaDestino.', "proporcaoDestino":'.$request->proporcaoDestino.', "centroCustoDestino":'.$request->centroCustoDestino.'}';

        $update->save();
        $request->session()->flash('message', 'Dados atualizados com sucesso!');

        //return view('crudView', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName]);
        redirect('outrasContas.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GerencialOutrasContas  $gerencialOutrasContas
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $del = GerencialOutrasContas::find($id);
        $del->delete();

        $this->tableData  = GerencialOutrasContas::get();
        
        //return view('crudView', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName]);
        redirect('outrasContas.index');
    }
}
