<?php

use Illuminate\Support\Facades\Route;

use App\Models\GerencialPeriodo;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    $periodoAtivo   = GerencialPeriodo::where('periodoAtivo', 'S')
                                      ->where('periodoSituacao', 'AB')
                                      ->get();
    return view('home', ['periodoAtivo' => $periodoAtivo[0]]);
});

//Route::get('/home', 'HomeController@index')->name('home');
/* Rotas para Edição de Empresas
 *
 *  rotas
 *  empresas.index  
 *          .create     Inclusão
 *          .store      Grava os dados
 *          .show       Exibição / Lista os dados
 *          .edit       Exibe formulário para edição dos dados
 *          .update     Atualiza os dados editados
 *          .destroy    Exclui os dados
 */
/******* CADASTROS *******/
Route::resource('empresas',                 'App\Http\Controllers\GerencialEmpresasController',                 ['names' => 'empresas']);
Route::resource('contaGerencial',           'App\Http\Controllers\GerencialContaGerencialController',           ['names' => 'contaGerencial']);
Route::resource('grupoConta',               'App\Http\Controllers\GerencialGrupoContaController',               ['names' => 'grupoConta']);
Route::resource('subGrupoConta',            'App\Http\Controllers\GerencialSubGrupoContaController',            ['names' => 'subGrupoConta']);
Route::resource('baseCalculo',              'App\Http\Controllers\GerencialBaseCalculoController',              ['names' => 'baseCalculo']);
Route::resource('baseCalculoConta',         'App\Http\Controllers\GerencialBaseCalculoContaController',         ['names' => 'baseCalculoConta']);
Route::resource('centroCusto',              'App\Http\Controllers\GerencialCentroCustoController',              ['names' => 'centroCusto']);
Route::resource('parametroRateio',          'App\Http\Controllers\GerencialParametroRateioController',          ['names' => 'parametroRateio']);
Route::resource('transferenciaEmpresa',     'App\Http\Controllers\GerencialParametroEmpresaController',         ['names' => 'transferenciaEmpresa']);
Route::resource('transferenciaCentroCusto', 'App\Http\Controllers\GerencialParametroCentroCustoController',     ['names' => 'transferenciaCentroCusto']);
Route::resource('tipoLancamento',           'App\Http\Controllers\GerencialTipoLancamentoController',           ['names' => 'tipoLancamento']);
Route::resource('periodo',                  'App\Http\Controllers\GerencialPeriodoController',                  ['names' => 'periodo']);
Route::resource('contaContabil',            'App\Http\Controllers\GerencialContaContabilController',            ['names' => 'contaContabil']);
Route::resource('permissaoUsuario',         'App\Http\Controllers\GerencialUsuarioController',                  ['names' => 'permissaoUsuario']);
Route::resource('regional',                 'App\Http\Controllers\GerencialRegionalController',                 ['names' => 'regional']);
Route::resource('percentualTabela',         'App\Http\Controllers\GerencialTabelaRateioPercentualController',   ['names' => 'percentualTabela']);

/******* EXCEÇÕES *******/
Route::resource('outrasContas', 'App\Http\Controllers\GerencialOutrasContasController', ['names' => 'outrasContas']);
Route::resource('amortizacao',  'App\Http\Controllers\GerencialAmortizacaoController',  ['names' => 'amortizacao']);

/******* PARÂMETROS *******/
Route::resource('tabelaRateio', 'App\Http\Controllers\GerencialTabelaRateioController', ['names' => 'tabelaRateio']);
Route::resource('estorno', 'App\Http\Controllers\GerencialEstornoController', ['names' => 'estorno']);


/******* LANÇAMENTOS *******/
Route::resource('lancamento',   'App\Http\Controllers\GerencialLancamentoController',   ['names' => 'lancamento']);
Route::any('importacsv',   'App\Http\Controllers\GerencialLancamentoController@importacsv')->name('importacsv');

/******* IMPORTAÇÃO DE LANÇAMENTOS CONTÁBEIS *******/
Route::get('importarLancamento',    'App\Http\Controllers\Processos\ImportarContabilidadeController@index')->name('importarLancamento');
Route::any('processarImportacao',  'App\Http\Controllers\Processos\ImportarContabilidadeController@processaImportacaoContabil')->name('importacaoContabil');


/******* PROCESSAMENTOS *******/
Route::get('processarRateios', 'App\Http\Controllers\Processos\ParametroRateioController@index')->name('processarRateios');
Route::get('processarParametros', 'App\Http\Controllers\Processos\ParametroRateioController@processarParametros')->name('processarParametros');
Route::get('rateioLogistica', 'App\Http\Controllers\Processos\ParametroRateioController@indexLogistica')->name('rateioLogistica');
Route::get('processarRateioLogistica', 'App\Http\Controllers\Processos\ParametroRateioController@rateioLogistica')->name('processarRateioLogistica');

/******* RELATÓRIO GERENCIAL *******/
Route::any('relatorioGerencial',        'App\Http\Controllers\Relatorios\RelatorioGerencialController@index')->name('relatorioGerencial');
Route::any('relatorioGerencial_build', 'App\Http\Controllers\Relatorios\RelatorioGerencialController@build')->name('relatorioGerencial_build');
Route::any('relatorioGerencial_show',   'App\Http\Controllers\Relatorios\RelatorioGerencialController@generateReport')->name('relatorioGerencial_show');
Route::any('detalheConta',             'App\Http\Controllers\Relatorios\RelatorioGerencialController@detalhamentoContaGerencial')->name('detalheConta');

Route::any('relatorioLancamentos',              'App\Http\Controllers\Relatorios\RelatoriosGerenciais@lancamentosGerenciais')->name('relatorioLancamentos');

Route::prefix('relatorio')->group(function() {
    Route::any('/Empresas','App\Http\Controllers\Relatorios\RelatoriosGerenciais@cadastro')->name('gerencialEmpresas');
    Route::any('/CentrosCusto','App\Http\Controllers\Relatorios\RelatoriosGerenciais@cadastro')->name('gerencialCentroCusto');
    Route::any('/ContaGerencial','App\Http\Controllers\Relatorios\RelatoriosGerenciais@cadastro')->name('gerencialContaGerencial');
    Route::any('/GrupoConta','App\Http\Controllers\Relatorios\RelatoriosGerenciais@cadastro')->name('gerencialGrupoConta');
    Route::any('/Regionais','App\Http\Controllers\Relatorios\RelatoriosGerenciais@cadastro')->name('gerencialRegional');
    Route::any('/SubGrupo','App\Http\Controllers\Relatorios\RelatoriosGerenciais@cadastro')->name('gerencialSubGrupoConta');
    Route::any('/TipoLancamento','App\Http\Controllers\Relatorios\RelatoriosGerenciais@cadastro')->name('gerencialTipoLancamento');
    Route::any('/OutrasContas','App\Http\Controllers\Relatorios\RelatoriosGerenciais@cadastro')->name('gerencialOutrasContas');
    Route::any('/ContaContabil','App\Http\Controllers\Relatorios\RelatoriosGerenciais@cadastro')->name('gerencialContaContabil');
    Route::any('/ParametroRateio','App\Http\Controllers\Relatorios\RelatoriosGerenciais@cadastro')->name('gerencialParametroRateio');
});



/******* UTILITÁRIOS *******/
Route::get('importarParametros','App\Http\Controllers\Utils\UtilsController@importarParametros')->name('importarParametros');
Route::any('csvExport',         'App\Http\Controllers\Utils\UtilsController@csvExport')->name('csvExport');
Route::get('formError',         'App\Http\Controllers\Utils\UtilsController@formError')->name('formError');
Route::get('filialDP',          'App\Http\Controllers\Utils\UtilsController@filialDP')->name('filialDP');

//Auth::routes();


