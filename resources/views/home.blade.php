@extends('layouts.appGerencial')

@section('content')

{{-- <div class="card-deck">
    <div class="card" style="background-color: rgba(255,255,255, .5)">
        <div class="card-header bg-danger text-white tw-8">FUNCIONALIDADES DISPONÍVEIS PARA TESTES / VALIDAÇÃO</div>
        <div class="card-body">
            <ul>
                <li>CADASTROS <small class="text-orange"><b>[FINALIZADO | VALIDAR]</b></small></li>
                <li>EXCEÇÕES <small class="text-orange"><b>[FINALIZADO | VALIDAR]</b></small></li>
                <li>LANÇAMENTOS <small><b>[EM ANDAMENTO]</b></small></li>
                <li>PARÂMETROS <small class="text-orange"><b>[FINALIZADO | VALIDAR]</b></small></li>
                <li>PROCESSAMENTOS <small class="text-orange"><b>[FINALIZADO | VALIDAR]</b></small></li>
                <li>RELATÓRIOS  <small><b>[EM ANDAMENTO - VALIDAR EM PARALELO]</b></small></li>
                <li><b class="text-orange"><span class="fa fa-exclamation-triangle fa-lg"></span> ESTORNOS <small>[FINALIZADO | VALIDAR]</b></small></li>
                <li><b class="text-orange"><span class="fa fa-exclamation-triangle fa-lg"></span> RATEIO LOGÍSTICA <small>[FINALIZADO | VALIDAR]</b></small></li>
                <li><b class="text-orange"><span class="fa fa-exclamation-triangle fa-lg"></span> LANÇAMENTOS > IMPORTAR ARQUIVO (.csv) <small>[FINALIZADO | VALIDAR]</b></small></li>
            </ul>

            <ul>
                <li><b class="text-orange"><span class="fa fa-exclamation-triangle fa-lg"></span> RETORNO DE MENSAGENS DE ERRO NOS CADASTROS <small class="text-orange">VALIDAR</b></small></li>
            </ul>
        </div>
        <div class="card-footer">
            <small>
            [VAL] VALIDAÇÃO | [DES] DESENVOLVIMENTO | [CRN] NO CRONOGRAMA | [IMP] IMPORTAÇÃO DE DADOS G2
             </small>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-secondary text-white">COMENTÁRIOS</div>
        <div class="card-body">
            <ul>
                <li><small>[MENU]</small><b> PARÂMETROS</b><small class="text-orange"><b>VALIDAR</b></small>
                    <dd>As transferências dos valores entre Empresas e Centros de Custo ocorrerão toda vez que um lançamento for gravado no banco de dados, e registrando no histórico do lançamento.</dd>
                    <dd>Incluída a opção ESTORNOS para parametrização dos estornos a serem realizados</dd>
                </li>

                <li><span class="fa fa-exclamation-triangle fa-lg"></span> <small>[MENU]</small><b> PROCESSAMENTOS</b><small class="text-orange"><b>VALIDAR</b></small>
                    <dd>Incluída a opção RATEIO LOGÍSTICA [validar]</dd>
                </li>
            </ul>
        </div>
    </div>
</div> --}}

<div class="flex-center full-height" style='z-index:-1000;'>
    <h1><span class="fa fa-chart-line"></span> {{config('app.name')}}</h1>
</div>


{{-- @dd(session()->all()); --}}
</div>
@endsection
