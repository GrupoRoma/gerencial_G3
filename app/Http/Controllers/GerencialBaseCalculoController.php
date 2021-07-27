<?php

namespace App\Http\Controllers;

use App\Models\GerencialBaseCalculo;
use App\Models\Utils\Utilitarios;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GerencialBaseCalculoController extends Controller
{
    protected $crudTitle = 'Base de Cálculo';
    protected $tableData;
    protected $orderColumn;
    protected $model;
    protected $tableName;

    protected $utils;

    public function __construct(Request $request)
    {

        $this->utils    = new Utilitarios;

        // Define a ordenação dos dados
        if (!isset($request->columnOrder) || empty($request->columnOrder) ) $request->columnOrder = 'descricaoBaseCalculo';
        $this->orderColumn = $request->columnOrder;

        $this->tableData  = GerencialBaseCalculo::orderBy($request->columnOrder)->get();
        $this->model      = app('App\\Models\\GerencialBaseCalculo');
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
    public function store(GerencialBaseCalculo $gerencialBaseCalculo, Request $request)
    {
        // Validação
        $validator = Validator::make($request->all(), $this->model->rules);
        if ($validator->fails()) {
            $validate = $this->utils->validateMessage($validator->errors()->getMessages(), $this->model->rulesMessage);
            return response()->json($validate, 500);
        }

        foreach ($this->model->columnList as $column) {
            $gerencialBaseCalculo->$column = $request->$column;
        }

        $gerencialBaseCalculo->save();
        $request->session()->flash('message', 'Dados gravados com sucesso!');

        //return view('crudView', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName]);
//        return redirect('baseCalculo/index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\GerencialBaseCalculo  $gerencialBaseCalculo
     * @return \Illuminate\Http\Response
     */
    public function show(GerencialBaseCalculo $gerencialBaseCalculo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\GerencialBaseCalculo  $gerencialBaseCalculo
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        // Carrega os dados da tabela
        $this->tableData = GerencialBaseCalculo::where('id', $id)->get();

        return view('crudForm', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName, 'id' => $id]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\GerencialBaseCalculo  $gerencialBaseCalculo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GerencialBaseCalculo $gerencialBaseCalculo, $id)
    {
        // Validação
        $validator = Validator::make($request->all(), $this->model->rules);
        if ($validator->fails()) {
            $validate = $this->utils->validateMessage($validator->errors()->getMessages(), $this->model->rulesMessage);
            return response()->json($validate, 500);
        }

        $update = GerencialBaseCalculo::find($id);

        foreach ($this->model->columnList as $column) {
            $update->$column = $request->$column;
        }

        $update->save();
        $request->session()->flash('message', 'Dados atualizados com sucesso!');

        //return view('crudView', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName]);
//        return redirect('baseCalculo/index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GerencialBaseCalculo  $gerencialBaseCalculo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $del = GerencialBaseCalculo::find($id);
        $del->delete();

        $this->tableData  = GerencialBaseCalculo::orderby('descricaoBaseCalculo')->get();
        
        //return view('crudView', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName]);
        return redirect('baseCalculo/index');
    }
}
