<?php

namespace App\Http\Controllers;

use App\Models\GerencialRegional;
use App\Models\Utils\Utilitarios;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GerencialRegionalController extends Controller
{
    protected $crudTitle = 'Regionais';
    protected $tableData;
    protected $orderColumn;
    protected $model;
    protected $tableName;

    protected   $utils;

    public function __construct(Request $request)
    {
        $this->utils                = new Utilitarios;
        
        // Define a ordenação dos dados
        if (!isset($request->columnOrder) || empty($request->columnOrder) ) $request->columnOrder = 'descricaoRegional';
        $this->orderColumn = $request->columnOrder;

        $this->tableData  = GerencialRegional::orderby($request->columnOrder)->get();
        $this->model      = app('App\\Models\\GerencialRegional');
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
    public function store(GerencialRegional $gerencialRegional, Request $request)
    {

        // Validação
        $validator = Validator::make($request->all(), $this->model->rules);
        if ($validator->fails()) {
            $validate = $this->utils->validateMessage($validator->errors()->getMessages(), $this->model->rulesMessage);
            return response()->json($validate, 500);
        }
/* 
        // Validação
        $validator = Validator::make($request->all(), $this->model->rules);

        if ($validator->fails()) {
            return redirect('regional.create')
                        ->withErrors($validator)
                        ->withInput();
        } */

        foreach ($this->model->columnList as $column) {
            if ($column == 'codigoVendasExternasERP') {
                if (!empty($request->column)) $gerencialRegional->$column = implode(',', (array) $request->$column);
                else    $gerencialRegional->$column = implode(',', (array) $request->$column);
            }
            else        $gerencialRegional->$column = $request->$column;
        }

        $gerencialRegional->save();
        redirect('regional.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\GerencialRegional  $gerencialRegional
     * @return \Illuminate\Http\Response
     */
    public function show(GerencialRegional $gerencialRegional)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\GerencialRegional  $gerencialRegional
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        // Carrega os dados da tabela
        $this->tableData = GerencialRegional::where('id', $id)->get();

        return view('crudForm', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName, 'id' => $id]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\GerencialRegional  $gerencialRegional
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GerencialRegional $gerencialRegional, $id)
    {
        $validator = Validator::make($request->all(), $this->model->rules);

        if ($validator->fails()) {
            return redirect('periodo/edit')
                        ->withErrors($validator)
                        ->withInput();
        }
        else {
            $update = GerencialRegional::find($id);

            foreach ($this->model->columnList as $column) {
                if ($column == 'codigoVendasExternasERP')   $update->$column = implode(',', $request->$column);
                else                                        $update->$column = $request->$column;
            }

            $update->save();
            $request->session()->flash('message', 'Dados atualizados com sucesso!');
    
            redirect('regional.index');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GerencialRegional  $gerencialRegional
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $del = GerencialRegional::find($id);
        $del->delete();

        $this->tableData  = GerencialRegional::orderby('descricaoRegional')->get();
        
        redirect('regional.index');
    }
}
