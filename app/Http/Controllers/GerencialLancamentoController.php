<?php

namespace App\Http\Controllers;

use App\Models\GerencialCentroCusto;
use App\Models\GerencialContaGerencial;
use App\Models\GerencialLancamento;
use App\Models\Utils\Utilitarios;
use App\Models\GerencialEmpresas;
use App\Models\GerencialTipoLancamento;
use App\Models\GerencialPeriodo;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GerencialLancamentoController extends Controller
{
    protected   $crudTitle = 'Lançamento Gerencial';
    protected   $tableData;
    protected   $orderColumn;
    protected   $model;
    protected   $tableName;
    protected   $errors = [];
    
    protected   $utils;
    protected   $lancamentoGerencial;
    protected   $tipoLancamento;
    protected   $periodo;

    public function __construct(Request $request)
    {
        $this->utils                = new Utilitarios;
        $this->lancamentoGerencial  = new GerencialLancamento;
        $this->periodo              = new GerencialPeriodo;
        $this->tipoLancamento       = new GerencialTipoLancamento;
        
        $this->model      = app('App\\Models\\GerencialLancamento');
        $this->tableName  = $this->model->getTable();

        $periodoCorrente            = $this->periodo->current();

        // Define a ordenação dos dados
        if (!isset($request->columnOrder) || empty($request->columnOrder) ) $request->columnOrder = 'anoLancamento, mesLancamento, created_at';
        $this->orderColumn = $request->columnOrder;

        if (isset($request->idTipoLancamento) && !empty($request->idTipoLancamento)) {
            $lancamentoTipo             = $this->tipoLancamento->getTipoLancamento($request->idTipoLancamento);
            $this->tableData  = GerencialLancamento::where('idTipoLancamento', $request->idTipoLancamento)
                                                    ->where('anoLancamento', $periodoCorrente->ano)
                                                    ->where('mesLancamento', $periodoCorrente->mes)
                                                    ->orderByRaw($this->orderColumn)
                                                    ->get();

            $this->model->viewSubTitle = $lancamentoTipo->descricaoTipoLancamento;
        }
        else {
            $this->tableData  = GerencialLancamento::where('anoLancamento', $periodoCorrente->ano)
                                                    ->where('mesLancamento', $periodoCorrente->mes)
                                                    ->orderByRaw($request->columnOrder)
                                                    ->get();
        }

        
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
    public function store(GerencialLancamento $gerencialLancamento, Request $request)
    {
        // Validação
        $validator = Validator::make($request->all(), $this->model->rules);
        if ($validator->fails()) {
            $validate = $this->utils->validateMessage($validator->errors()->getMessages(), $this->model->rulesMessage);
            return response()->json($validate, 500);
        }

        // Corrige o valor do lançamento de acordo com a
        // natureza de crédito ou débito
        $multiplicador = 1;
        if ($request->creditoDebito == 'DEB' && $request->valorLancamento > 0 ||
            $request->creditoDebito == 'CRD' && $request->valorLancamento < 0) {
            $multiplicador = -1;
        }

//        if ($request->creditoDebito == 'DEB')  $multiplicador = -1;

        $lancamentoAjuste[] = [ 'anoLancamento'         => $request->anoLancamento,
                                'mesLancamento'         => $request->mesLancamento,
                                'codigoContaContabil'   => $request->codigoContaContabil,
                                'idEmpresa'             => $request->idEmpresa,
                                'centroCusto'           => $request->centroCusto,
                                'idContaGerencial'      => $request->idContaGerencial,
                                'creditoDebito'         => $request->creditoDebito,
                                'valorLancamento'       => $request->valorLancamento * $multiplicador,
                                'idTipoLancamento'      => 6,       // [M] AJUSTES MANUAL
                                'historicoLancamento'   => '[M - AJUSTE MANUAL] '.$request->historicoLancamento];

/*        foreach ($this->model->columnList as $column) {
            $gerencialLancamento->$column = $request->$column;
        }
        // Tipo de Lancamento = 6 [M] Manual
        $gerencialLancamento->idTipoLancamento = 6;
        $gerencialLancamento->idUsuario = 1;

        // Verifica se o valor está de acordo com o tipo Crédito ou Débito
        if ($gerencialLancamento->creditoDebito == 'DEB' && $gerencialLancamento->valorLancamento > 0 ||
            $gerencialLancamento->creditoDebito == 'CRD' && $gerencialLancamento->valorLancamento < 0) {
            $gerencialLancamento->valorLancamento *= -1;
        }

        $gerencialLancamento->save();
        $request->session()->flash('message', 'Dados gravados com sucesso!');
*/

        $this->lancamentoGerencial->gravaLancamento($lancamentoAjuste);

        //return view('crudView', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName]);
        return redirect()->route('lancamento.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\GerencialLancamento  $gerencialLancamento
     * @return \Illuminate\Http\Response
     */
    public function show(GerencialLancamento $gerencialLancamento)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\GerencialLancamento  $gerencialLancamento
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        // Carrega os dados da tabela
        $this->tableData = GerencialLancamento::where('id', $id)->get();

        return view('crudForm', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName, 'id' => $id]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\GerencialLancamento  $gerencialLancamento
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GerencialLancamento $gerencialLancamento, $id)
    {
        // Validação
        $validator = Validator::make($request->all(), $this->model->rules);
        if ($validator->fails()) {
            $validate = $this->utils->validateMessage($validator->errors()->getMessages(), $this->model->rulesMessage);
            return response()->json($validate, 500);
        }

        $update = GerencialLancamento::find($id);

        foreach ($this->model->columnList as $column) {
            $update->$column = $request->$column;
        }

        if ($update->creditoDebito == 'DEB' && $update->valorLancamento > 0 ||
            $update->creditoDebito == 'CRD' && $update->valorLancamento < 0) {
            $update->valorLancamento *= -1;
        }

        $update->save();
        $request->session()->flash('message', 'Dados atualizados com sucesso!');

//        return view('crudView', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName]);
        redirect('lancamento.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GerencialLancamento  $gerencialLancamento
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $del = GerencialLancamento::find($id);
        $del->delete();

        $this->tableData  = GerencialLancamento::orderby('descricaoParametro')->get();
        
        //return view('crudView', ['tableData' => $this->tableData, 'model' => $this->model, 'tableName' => $this->tableName]);
        redirect('lancamento.index');
    }

    /**
     *  Importa lançamentos gerenciais a partir de um arquivo CSV
     * 
     *  @param  Request     $request
     * 
     */
    public function importacsv(Request $request) {

        // Verifica se foi selecionado um arquivo para importação
        if($request->hasFile('arquivocsv')){

            if ($request->file('arquivocsv')->isValid()) {
                $extension = $request->arquivocsv->getClientOriginalExtension();
                
                // Nome do arquivo que será gavado
                $fileNameToStore= 'lancamentos_'.date('dmYHis').'.'.$extension;
                
                // Grava o arquivo na pasta destino
                $path = $request->arquivocsv->storeAs('public/log_importacao', $fileNameToStore);
                
                if (!$processamento = $this->processaCSV($fileNameToStore)) {
                    return view('processamento.validacao', ['errors' => $this->errors]);
                }
                else return ("<span id='showMsg' data-title='IMPORTAÇÃO DE LANÇAMENTOS EM ARQUIVO CSV'
                                data-message='Importação realizada com sucesso'></span>");
            }
            else {
                return "Arquivo Inválido";
            }
        }
        // Carrega o formulario para seleção do arquivo
        else {
            return view('layouts.layoutImportaCSV');
        }
        
    }

    /**
     *  Processa e grava os lancamentos do arquivo CSV
     * 
     *  LAYOUT DO ARRAY
     *      LINHA 0 = CABEÇALHO (desprezar)
     * 
     *      [0]     = Informações sobre o lançamento
     *      [1]     = Nome da Empresa (igual ao cadastro do Gerencial 3)
     *      [2]     = Sigla do centro de custo (Igual ao cadastro do Gerencial 3)
     *      [3]     = Valor do lancamento
     *      [4]     = Conta Gerencial para débito
     *      [5]     = Conta Gerencial para Crédito
     *      [6]     = Histórico
     *      [7]     = Identifica se é um lançamento de reversão (S/N)
     *      [8]     = Mês do lançamento
     *      [9]     = Ano do lançamento
     * 
     */
    public function processaCSV(String $csvFile) {

        $numeroLote     = ($this->lancamentoGerencial->getLoteLancamento() ?? 1);

        $csvHandle      = fopen(storage_path('app/public/log_importacao/').$csvFile, 'r');
        $lancamentos    = [];

        $countRow   = 0;

        while (($rowData = fgetcsv($csvHandle,0,';')) !== FALSE) {
            $rowData = array_map('utf8_encode', $rowData);

            $rowError   = FALSE;

            // Ignora a primeira linha (Cabeçalho)
            if ($countRow > 0) {
                // Processo somente se o valor estiver preenchido e for diferente de 0 (zero)
                if (!empty($rowData[3]) && $rowData[3] <> 0) {

                    $valorLancamento    = floatval(str_replace(',', '.', str_replace('.', '', $rowData[3])));

                    // Identifica o código da empresa pela nome
                    $empresa   = GerencialEmpresas::where('nomeAlternativo', $rowData[1])->get();
                    if ($empresa->count() == 0) {
                        $this->errors[] = ['errorTitle'    => 'EMPRESA',
                                            'error'         => 'A empresa '.strtoupper($rowData[1]).' não foi localizada no banco de dados. [Linha: '.$countRow.']'];
                        $rowError   = TRUE;
                    }

                    // Identifica o código do centro de custo
                    $centroCusto   = GerencialCentroCusto::where('siglaCentroCusto', $rowData[2])->get();
                    if ($centroCusto->count() == 0) {
                        $this->errors[] = ['errorTitle'    => 'CENTRO DE CUSTO',
                                            'error'         => 'O centro de custo '.strtoupper($rowData[2]).' não foi localizado no banco de dados. [Linha: '.$countRow.']'];
                        
                        $rowError   = TRUE;
                    }

                    // Identifica o código da empresa pela nome
                    $codigoConta    = (!empty($rowData[4]) ? $rowData[4] : $rowData[5]);
                    $creditoDebito  = (!empty($rowData[4]) ? 'DEB' : 'CRD');
                    $contaGerencial   = GerencialContaGerencial::where('codigoContaGerencial', str_pad($codigoConta, 5, '0', STR_PAD_LEFT))->get();
                    if ($contaGerencial->count() == 0) {
                        $this->errors[] = ['errorTitle'    => 'CONTA GERENCIAL',
                                            'error'         => 'A conta gerencial '.str_pad($codigoConta, 5, '0', STR_PAD_LEFT).' não foi localizada no banco de dados. [Linha: '.$countRow.']'];
                                    
                        $rowError   = TRUE;
                    }

                    if (!$rowError) {
                        if ($rowData[7] == 'S')     $historico = GerencialTipoLancamento::find(10);
                        else                        $historico = GerencialTipoLancamento::find(21);

                        $lancamentos[]    = [ 'anoLancamento'           => $rowData[9],
                                                'mesLancamento'         => $rowData[8],
                                                'codigoContaContabil'   => NULL,
                                                'idEmpresa'             => $empresa[0]->id,
                                                'centroCusto'           => $centroCusto[0]->id,
                                                'idContaGerencial'      => $contaGerencial[0]->id,
                                                'creditoDebito'         => $creditoDebito,
                                                'valorLancamento'       => ($creditoDebito == 'DEB' ? $valorLancamento * -1 : $valorLancamento),
                                                'idTipoLancamento'      => ($rowData[7] == 'S' ? 10 : 21),       // [V] Reversão ou [CSV] Importado CSV
                                                'historicoLancamento'   => $historico->historicoTipoLancamento.' ['.$csvFile.']'.' - '.utf8_encode($rowData[6]),
                                                'numeroLote'            => $numeroLote];
                   }
                }
            }

            $countRow ++;
        }

        // Exibe os erros encontrados
        if (count($this->errors) > 0)   return FALSE;

        // Grava os lançamentos de estorno
        return $this->lancamentoGerencial->gravaLancamento($lancamentos);

    }
}
