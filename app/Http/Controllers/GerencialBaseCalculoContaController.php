<?php

namespace App\Http\Controllers;

use App\Models\GerencialBaseCalculoConta;
use App\Models\Utils\Utilitarios;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GerencialBaseCalculoContaController extends Controller
{
    protected $crudTitle = 'Base de Cálculo (Contas)';
    protected $tableData;
    protected $orderColumn;
    protected $model;
    protected $tableName;

    protected $utils;

    public function __construct(Request $request)
    {
        $this->utils          = new Utilitarios;

        // Define a ordenação dos dados
        if (!isset($request->columnOrder) || empty($request->columnOrder) ) $request->columnOrder = 'idBaseCalculo';
        $this->orderColumn = $request->columnOrder;

        $this->tableData  = GerencialBaseCalculoConta::orderby($request->columnOrder)->get();
        $this->model      = app('App\\Models\\GerencialBaseCalculoConta');
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
    public function store(GerencialBaseCalculoConta $gerencialBaseCalculoConta, Request $request)
    {
        // Validação
        $validator = Validator::make($request->all(), $this->model->rules);
        if ($validator->fails()) {
            $validate = $this->utils->validateMessage($validator->errors()->getMessages(), $this->model->rulesMessage);
           return response()->json($validate, 500);
        }

        // Processa os valores do formulário para inclusão
        foreach ($this->model->columnList as $column) {
            $gerencialBaseCalculoConta->$column = $request->$column;
        }

        $gerencialBaseCalculoConta->save();
        $request->session()->flash('message', 'Dados gravados com sucesso!');

        //redirect('baseCalculoContas.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\GerencialBaseCalculoConta  $gerencialBaseCalculoContaConta
     * @return \Illuminate\Http\Response
     */
    public function show(GerencialBaseCalculoConta $gerencialBaseCalculoContaConta)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\GerencialBaseCalculoConta  $gerencialBaseCalculoContaConta
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        // Carrega os dados da tabela
        $this->tableData = GerencialBaseCalculoConta::where('id', $id)->get();

        return view('crudForm', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName, 'id' => $id]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\GerencialBaseCalculoConta  $gerencialBaseCalculoContaConta
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GerencialBaseCalculoConta $gerencialBaseCalculoConta, $id)
    {
        $validator = Validator::make($request->all(), $this->model->rules);

        if ($validator->fails()) {
            $validate = $this->utils->validateMessage($validator->errors()->getMessages(), $this->model->rulesMessage);
           return response()->json($validate, 500);
        }

        $update = GerencialBaseCalculoConta::find($id);

        foreach ($this->model->columnList as $column) {
            $update->$column = $request->$column;
        }

        $update->save();
        $request->session()->flash('message', 'Dados atualizados com sucesso!');

        //return view('crudView', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName]);
//        return redirect('baseCalculoConta/index');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GerencialBaseCalculoConta  $gerencialBaseCalculoContaConta
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $del = GerencialBaseCalculoConta::find($id);
        $del->delete();

        $this->tableData  = GerencialBaseCalculoConta::orderby('idBaseCalculo')->get();
        
        //return view('crudView', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName]);
        //return redirect('baseCalculoConta/index');
    }
}
