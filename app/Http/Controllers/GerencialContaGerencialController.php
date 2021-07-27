<?php

namespace App\Http\Controllers;

use App\Models\GerencialContaGerencial;
use App\Models\Utils\Utilitarios;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GerencialContaGerencialController extends Controller
{
    protected   $crudTitle = 'Contas Gerenciais';
    protected   $tableData;
    protected   $orderColumn;
    protected   $model;
    protected   $tableName;

    protected   $utils;

    public function __construct(Request $request)
    {
        $this->utils        = new Utilitarios;

        // Define a ordenação dos dados
        if (!isset($request->columnOrder) || empty($request->columnOrder) ) $request->columnOrder = 'codigoContaGerencial';
        $this->orderColumn = $request->columnOrder;

        $this->tableData  = GerencialContaGerencial::orderby($request->columnOrder)->paginate(15);
        $this->model      = app('App\\Models\\GerencialContaGerencial');
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
    public function store(GerencialContaGerencial $gerencialContaGerencial, Request $request)
    {
        // Validação
        $validator = Validator::make($request->all(), $this->model->rules);
        if ($validator->fails()) {
            $validate = $this->utils->validateMessage($validator->errors()->getMessages(), $this->model->rulesMessage);
            return response()->json($validate, 500);
        }

        foreach ($this->model->columnList as $column) {
            switch ($column) {
                case 'valoresVeiculo':
                    if (!empty($request->column))   $gerencialContaGerencial->$column =  implode(',', $request->$column);
                    break;
                default:
                    $gerencialContaGerencial->$column = $request->$column;
                    break;
            }
        }
   
        $gerencialContaGerencial->save();
        $request->session()->flash('message', 'Dados gravados com sucesso!');

        //return view('crudView', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName]);
        redirect('contaGerencial.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\GerencialContaGerencial  $gerencialContaGerencial
     * @return \Illuminate\Http\Response
     */
    public function show(GerencialContaGerencial $gerencialContaGerencial)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\GerencialContaGerencial  $gerencialContaGerencial
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        // Carrega os dados da tabela
        $this->tableData = GerencialContaGerencial::where('id', $id)->get();

        return view('crudForm', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName, 'id' => $id]);

    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\GerencialContaGerencial  $gerencialContaGerencial
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GerencialContaGerencial $gerencialContaGerencial, $id)
    {
        // Validação
        $validator = Validator::make($request->all(), $this->model->rules);
        if ($validator->fails()) {
            $validate = $this->utils->validateMessage($validator->errors()->getMessages(), $this->model->rulesMessage);
            return response()->json($validate, 500);
        }

        $update = GerencialContaGerencial::find($id);

        foreach ($this->model->columnList as $column) {
            switch ($column) {
                case 'valoresVeiculo':
                    if (count( (array) $request->$column)>0)    $update->$column =  implode(',', (array) $request->$column);
                    else                                        $update->$column = NULL;
                    break;
                default:
                    $update->$column = $request->$column;
                    break;
            }
        }

        $update->save();
        $request->session()->flash('message', 'Dados atualizados com sucesso!');

        //return view('crudView', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName]);
        redirect('contaGerencial.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GerencialContaGerencial  $gerencialContaGerencial
     * @return \Illuminate\Http\Response
     */
     public function destroy(Request $request, $id)
    {
        $del = GerencialContaGerencial::find($id);
        $del->delete();

        $this->tableData  = GerencialContaGerencial::orderby('codigoContaGerencial')->get();
        
        //return view('crudView', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName]);
        redirect('contaGerencial.index');
    }
}
