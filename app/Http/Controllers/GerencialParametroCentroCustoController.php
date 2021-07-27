<?php

namespace App\Http\Controllers;

use App\Models\GerencialParametroCentroCusto;
use App\Models\Utils\Utilitarios;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GerencialParametroCentroCustoController extends Controller
{

    protected   $crudTitle = 'Transferência entre Centros de Custo';
    protected   $tableData;
    protected   $orderColumn;
    protected   $model;
    protected   $tableName;

    protected   $utils;

    public function __construct(Request $request)
    {
        $this->utils        = new Utilitarios;

        // Define a ordenação dos dados
        if (!isset($request->columnOrder) || empty($request->columnOrder) ) $request->columnOrder = 'idCentroCustoOrigem';
        $this->orderColumn = $request->columnOrder;

        $this->tableData  = GerencialParametroCentroCusto::orderBy($request->columnOrder)->get();
        $this->model      = app('App\\Models\\GerencialParametroCentroCusto');
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
    public function store(GerencialParametroCentroCusto $gerencialCentroCusto, Request $request)
    {
        // Validação
        $validator = Validator::make($request->all(), $this->model->rules);
        if ($validator->fails()) {
            $validate = $this->utils->validateMessage($validator->errors()->getMessages(), $this->model->rulesMessage);
            return response()->json($validate, 500);
        }

        foreach ($this->model->columnList as $column) {
            $gerencialCentroCusto->$column = $request->$column;
        }

        $gerencialCentroCusto->save();
        $request->session()->flash('message', 'Dados gravados com sucesso!');

        redirect('transferenciaCentroCusto/index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\GerencialParametroCentroCusto  $gerencialParametroCentroCusto
     * @return \Illuminate\Http\Response
     */
    public function show(GerencialParametroCentroCusto $gerencialParametroCentroCusto)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\GerencialParametroCentroCusto  $gerencialParametroCentroCusto
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        // Carrega os dados da tabela
        $this->tableData = GerencialParametroCentroCusto::where('id', $id)->get();

        return view('crudForm', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName, 'id' => $id]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\GerencialParametroCentroCusto  $gerencialParametroCentroCusto
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GerencialParametroCentroCusto $gerencialCentroCusto, $id)
    {
        // Validação
        $validator = Validator::make($request->all(), $this->model->rules);
        if ($validator->fails()) {
            $validate = $this->utils->validateMessage($validator->errors()->getMessages(), $this->model->rulesMessage);
            return response()->json($validate, 500);
        }

        $update = GerencialParametroCentroCusto::find($id);

        foreach ($this->model->columnList as $column) {
            $update->$column = $request->$column;
        }
                    
        $update->save();
        $request->session()->flash('message', 'Dados atualizados com sucesso!');

        redirect('transferenciaCentroCusto/index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GerencialParametroCentroCusto  $gerencialParametroCentroCusto
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $del = GerencialParametroCentroCusto::find($id);
        $del->delete();

        redirect('transferenciaCentroCusto/index');

    }
}
