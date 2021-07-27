<?php

namespace App\Http\Controllers;

use App\Models\GerencialUsuario;
use Illuminate\Http\Request;

class GerencialUsuarioController extends Controller
{
    protected $crudTitle = 'Permissões de Usuários';
    protected $tableData;
    protected $orderColumn;
    protected $model;
    protected $tableName;

    public function __construct(Request $request)
    {
        // Define a ordenação dos dados
        if (!isset($request->columnOrder) || empty($request->columnOrder) ) $request->columnOrder = 'empresasAcesso';
        $this->orderColumn = $request->columnOrder;

        $this->tableData  = GerencialUsuario::orderBy($request->columnOrder)->get();
        $this->model      = app('App\\Models\\GerencialUsuario');
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
        // MUDAR A VIEW PARA UMA VIEW PERSONALIZADA
        return view('crudForm', ['tableData' => '', 'model' => $this->model, 'tableName' => $this->tableName, 'id' => '']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(GerencialUsuario $gerencialUsuario, Request $request)
    {

        foreach ($this->model->columnList as $column) {
            switch ($column) {
                case 'empresasAcesso':
                case 'centrosCustoAcesso':
                case 'contaGerencialAcesso':
                    $request->$column = implode(',', $request->$column);
                    break;
            }
            $gerencialUsuario->$column = $request->$column;
        }

        $gerencialUsuario->save();
        $request->session()->flash('message', 'Dados gravados com sucesso!');

        return view('crudView', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\GerencialUsuario  $gerencialUsuario
     * @return \Illuminate\Http\Response
     */
    public function show(GerencialUsuario $gerencialUsuario)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\GerencialUsuario  $gerencialUsuario
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        // Carrega os dados da tabela
        $this->tableData = GerencialUsuario::where('id', $id)->get();

        //MUDAR PARA UMA VIEW PERSONALIZADA
        return view('crudForm', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName, 'id' => $id]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\GerencialUsuario  $gerencialUsuario
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GerencialUsuario $gerencialUsuario, $id)
    {
/*        $validator = Validator::make($request->all(), $this->model->rules);

        if ($validator->fails()) {
            return redirect('parametroRateio/edit')
                        ->withErrors($validator)
                        ->withInput();
        }
*/

        $update = GerencialUsuario::find($id);

        foreach ($this->model->columnList as $column) {
            switch ($column) {
                case 'empresasAcesso':
                case 'centrosCustoAcesso':
                case 'contaGerencialAcesso':
                    $request->$column = implode(',', $request->$column);
                    break;
            }
            $update->$column = $request->$column;
        }

        $update->save();
        $request->session()->flash('message', 'Dados atualizados com sucesso!');

        return view('crudView', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName]);
  
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GerencialUsuario  $gerencialUsuario
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $del = GerencialUsuario::find($id);
        $del->delete();

        $this->tableData  = GerencialUsuario::orderby('descricaoParametro')->get();
        
        return view('crudView', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName]);
    }
}
