<?php

namespace App\Http\Controllers;

use App\Models\GerencialContaContabil;
use App\Models\Utils\Utilitarios;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GerencialContaContabilController extends Controller
{
    protected $crudTitle = 'Conta Contábil x Conta Gerencial';
    protected $tableData;
    protected $orderColumn;
    protected $model;
    protected $tableName;
    protected $contaContabil;

    protected $utils;

    public function __construct(Request $request)
    {
        $this->utils        = new Utilitarios;

        // Define a ordenação dos dados
        if (!isset($request->columnOrder) || empty($request->columnOrder) ) $request->columnOrder = 'contaContabil';
        $this->orderColumn = $request->columnOrder;

        $this->tableData    = GerencialContaContabil::orderBy($request->columnOrder)->paginate(15);
        $this->model        = app('App\\Models\\GerencialContaContabil');
        $this->tableName    = $this->model->getTable();

        $this->contaContabil= new GerencialContaContabil;
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
    public function store(GerencialContaContabil $gerencialContaContabil, Request $request)
    {
        // Validação
        $validator = Validator::make($request->all(), $this->model->rules);
        if ($validator->fails()) {
            $validate = $this->utils->validateMessage($validator->errors()->getMessages(), $this->model->rulesMessage);
            return response()->json($validate, 500);
        }

        if ($this->contaContabil->validateUnique($request->idContaGerencial, $request->codigoContaContabilERP)) {
            return response()->json(['Já existe cadastro para a associação para a Conta Contábil e Conta Gerencial informados!'], 500);
        }

        foreach ($this->model->columnList as $column) {
            $gerencialContaContabil->$column = $request->$column;
        }

        $codigoERP  = $this->contaContabil->getContaContabil($request->codigoContaContabilERP);
        $gerencialContaContabil->contaContabil = $codigoERP->PlanoConta_ID;

        $gerencialContaContabil->save();
        $request->session()->flash('message', 'Dados gravados com sucesso!');

        //return view('crudView', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName]);

        return redirect('contaContabil/index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\GerencialContaContabil  $gerencialContaContabil
     * @return \Illuminate\Http\Response
     */
    public function show(GerencialContaContabil $gerencialContaContabil)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\GerencialContaContabil  $gerencialContaContabil
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        // Carrega os dados da tabela
        $this->tableData = GerencialContaContabil::where('id', $id)->get();

        return view('crudForm', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName, 'id' => $id]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\GerencialContaContabil  $gerencialContaContabil
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

        $update = GerencialContaContabil::find($id);
        foreach ($this->model->columnList as $column) {
            $update->$column = $request->$column;
        }

        $codigoERP  = $this->contaContabil->getContaContabil($request->codigoContaContabilERP);
        $update->contaContabil = $codigoERP->PlanoConta_ID;

        $update->save();
        $request->session()->flash('message', 'Dados atualizados com sucesso!');

        //return view('crudView', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName]);
//        return redirect('contaContabil/index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GerencialContaContabil  $gerencialContaContabil
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $del = GerencialContaContabil::find($id);
        $del->delete();

        $this->tableData  = GerencialContaContabil::orderby('contaContabil')->get();
        
        //return view('crudView', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName]);
        return redirect('contaContabil/index');
    }
}
