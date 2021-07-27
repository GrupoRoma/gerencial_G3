<?php

namespace App\Http\Controllers;

use App\Models\GerencialEstorno;
use App\Models\Utils\Utilitarios;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GerencialEstornoController extends Controller
{

    protected   $crudTitle = 'Estornos';
    protected   $tableData;
    protected   $orderColumn;
    protected   $model;
    protected   $tableName;

    protected   $utils;

    public function __construct(Request $request)
    {
        $this->utils        = new Utilitarios;

        // Define a ordenação dos dados
        if (!isset($request->columnOrder) || empty($request->columnOrder) ) $request->columnOrder = 'idContaGerencial';
        $this->orderColumn = $request->columnOrder;

        $this->tableData  = GerencialEstorno::orderBy($request->columnOrder)->get();
        $this->model      = app('App\\Models\\GerencialEstorno');
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
    public function store(GerencialEstorno $gerencialEstorno, Request $request)
    {
        // Validação
        $validator = Validator::make($request->all(), $this->model->rules);
        if ($validator->fails()) {
            $validate = $this->utils->validateMessage($validator->errors()->getMessages(), $this->model->rulesMessage);
            return response()->json($validate, 500);
        }

        foreach ($this->model->columnList as $column) {
            $gerencialEstorno->$column = $request->$column;
        }

        $gerencialEstorno->save();
        $request->session()->flash('message', 'Dados gravados com sucesso!');

        redirect('estorno.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\GerencialEstorno  $gerencialEstorno
     * @return \Illuminate\Http\Response
     */
    public function show(GerencialEstorno $gerencialEstorno)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\GerencialEstorno  $gerencialEstorno
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        // Carrega os dados da tabela
        $this->tableData = GerencialEstorno::where('id', $id)->get();

        return view('crudForm', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName, 'id' => $id]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\GerencialEstorno  $gerencialEstorno
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GerencialEstorno $gerencialEstorno, $id)
    {
        // Validação
        $validator = Validator::make($request->all(), $this->model->rules);
        if ($validator->fails()) {
            $validate = $this->utils->validateMessage($validator->errors()->getMessages(), $this->model->rulesMessage);
            return response()->json($validate, 500);
        }

        $update = GerencialEstorno::find($id);

        foreach ($this->model->columnList as $column) {
            $update->$column = $request->$column;
        }
                    
        $update->save();
        $request->session()->flash('message', 'Dados atualizados com sucesso!');

        //return view('crudView', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName]);
        redirect('estorno.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GerencialEstorno  $gerencialEstorno
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $del = GerencialEstorno::find($id);
        $del->delete();

        $this->tableData  = GerencialEstorno::orderby('idContaGerencial')->get();
        
        return view('crudView', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName]);

    }
}
