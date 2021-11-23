<?php

namespace App\Http\Controllers;

use App\Models\GerencialTabelaRateioPercentual;
use Illuminate\Http\Request;

class GerencialTabelaRateioPercentualController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\GerencialTabelaRateioPercentual  $gerencialTabelaRateioPercentual
     * @return \Illuminate\Http\Response
     */
    public function show(GerencialTabelaRateioPercentual $gerencialTabelaRateioPercentual)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\GerencialTabelaRateioPercentual  $gerencialTabelaRateioPercentual
     * @return \Illuminate\Http\Response
     */
    public function edit(GerencialTabelaRateioPercentual $gerencialTabelaRateioPercentual)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\GerencialTabelaRateioPercentual  $gerencialTabelaRateioPercentual
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GerencialTabelaRateioPercentual $gerencialTabelaRateioPercentual)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GerencialTabelaRateioPercentual  $gerencialTabelaRateioPercentual
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $del = GerencialTabelaRateioPercentual::find($id);
        $del->delete();

        $msg = '';
        if (!GerencialTabelaRateioPercentual::find($id)) {
            $msg = "<span id='showMsg' data-title='EXCLUSÃO DE PERCENTUAL DA TABELA DE RATEIO'
                    data-message='Percentual excluído com sucesso!'></span>";
            $codeReturn = 200;
        }
        else {
            $msg = "<span id='showMsg' data-title='EXCLUSÃO DE PERCENTUAL DA TABELA DE RATEIO'
                    data-message='Ocorreu um erro na tentativa de excluir o percentual, tente novamente! <br>Persistindo o erro, entre contato com o adminsitrador do sistema. '></span>";
            $codeReturn = 500;
        }

        return response()->json(['msg' => $msg], $codeReturn);

    }
}
