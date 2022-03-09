@inject('permissoes',   'App\Models\GerencialUsuario')
@inject('menu',         'App\Models\Utils\Utilitarios' )

@php
    $validUser      = $permissoes->setUserPerms();
    $menuOptions    = $menu->getMenuOptions();
@endphp

<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }}</title>

    <!-- Scripts -- >
    <script src="{{ asset('js/app.js') }}" defer></script> -->

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito&display=swap" rel="stylesheet">
    <!-- <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet"> -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Nunito:ital,wght@0,200;0,300;0,400;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <!-- Bootstrap 4.5 -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.14.0/css/all.css" integrity="sha384-HzLeBuhoNPvSl5KYnjx0BT+WB0QEEqLprO+NBkkk5gbc67FTaL7XIGa2w1L0Xbgc" crossorigin="anonymous">

    <!-- Styles ---- LOCAL ------ -->
        <!--// APP -->
    <link href="{{ asset('public/css/gerencial.css') }}" rel="stylesheet">
        <!--// REPORTS -->
    <link href="{{ asset('public/css/reports.css') }}" rel="stylesheet">

    <!-- Styles ----- PRODUÇÃO ----- -- >
        <!--// APP -- >
        <link href="{{ asset('css/gerencial.css') }}" rel="stylesheet">
        <!--// REPORTS -- >
        <link href="{{ asset('css/reports.css') }}" rel="stylesheet">
-->
</head>

<body>
    <div id="app">
        <nav class="navbar navbar-expand-lg navbar-dark bg-orange">
            <a class="navbar-brand" href="{{ url('/') }}">
                <span class="fa fa-chart-line"></span> GER 3.0 {{-- {{ config('app.name', 'GER 3.0') }} --}}
             </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
            </button>
          
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
              <ul class="navbar-nav mr-auto">
                <!--<li class="nav-item active">
                  <a class="nav-link" href="#">Home <span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="#">Link</a>
                </li> -->

                {{-- MONTA O MENU DA APLICAÇÃO --}}
                @foreach ($menuOptions as $menuGroup => $options)
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" 
                           id="navbarDropdown" role="button" 
                           data-toggle="dropdown" 
                           aria-haspopup="true" aria-expanded="false">
                          {{$menuGroup}}
                        </a>
                        <div class="dropdown-menu sobre" aria-labelledby="navbarDropdown">
                            @foreach ($options as $optionLable => $option)
                                <a  class="dropdown-item" 
                                    data-nav="{{route($option['name'].(!empty($option['class']) ? '.'.$option['class'] : ''))}}" 
                                    {{(!empty($option['param']) ? 'data-params="'.$option['param'].'"' : '')}}>
                                    
                                    {{$optionLable}}
                                </a>
                            @endforeach
                        </div>
                      </li>
                @endforeach


{{--                 <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" 
                     id="navbarDropdown" role="button" 
                     data-toggle="dropdown" 
                     aria-haspopup="true" aria-expanded="false">
                    Cadastros
                  </a>
                  <div class="dropdown-menu sobre" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item" data-nav="{{route('empresas.index')}}">Empresa</a>
                    <a class="dropdown-item" data-nav="{{route('baseCalculo.index')}}">Base de Cálculo</a>
                    <a class="dropdown-item" data-nav="{{route('baseCalculoConta.index')}}">Base de Cálculo - Contas</a>
                    <a class="dropdown-item" data-nav="{{route('centroCusto.index')}}">Centro de Custo</a>
                    <a class="dropdown-item" data-nav="{{route('contaGerencial.index')}}">Conta Gerencial</a>
                    <a class="dropdown-item" data-nav="{{route('contaContabil.index')}}">Conta Gerencial X Conta Contábil</a>
                    <a class="dropdown-item" data-nav="{{route('grupoConta.index')}}">Grupo de Conta</a>
                    <a class="dropdown-item" data-nav="{{route('regional.index')}}">Regionais</a>
                    <a class="dropdown-item" data-nav="{{route('subGrupoConta.index')}}">Sub-Grupo de Conta</a>
                    <a class="dropdown-item" data-nav="{{route('tipoLancamento.index')}}">Tipos de Lançamento</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" data-nav="{{route('permissaoUsuario.index')}}">Permissões por Usuário</a>
                  </div>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" 
                       id="navbarDropdown" role="button" 
                       data-toggle="dropdown" 
                       aria-haspopup="true" aria-expanded="false">
                      Cadastro Exceções
                    </a>
                    <div class="dropdown-menu sobre" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" data-nav="{{route('outrasContas.index')}}">Outras Contas Contábeis</a>
                        <a class="dropdown-item" data-nav="{{route('amortizacao.index')}}">Amortizações</a>
                    </div>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" 
                       id="navbarDropdown" role="button" 
                       data-toggle="dropdown" 
                       aria-haspopup="true" aria-expanded="false">
                      Cadastro Parâmetros
                    </a>
                    <div class="dropdown-menu sobre" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" data-nav="{{route('estorno.index')}}">Estornos</a>
                        <a class="dropdown-item" data-nav="{{route('parametroRateio.index')}}">Parâmetros de Rateio</a>
                        <a class="dropdown-item" data-nav="{{route('tabelaRateio.index')}}">Tabela de Referência</a>
                        <a class="dropdown-item" data-nav="{{route('transferenciaEmpresa.index')}}">Transferência de Empresa</a>
                        <a class="dropdown-item" data-nav="{{route('transferenciaCentroCusto.index')}}">Transferência de C.Custo</a>
                    </div>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" 
                       id="navbarDropdown" role="button" 
                       data-toggle="dropdown" 
                       aria-haspopup="true" aria-expanded="false">
                      Períodos (Sessão)
                    </a>
                    <div class="dropdown-menu sobre" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" data-nav="{{route('periodo.index')}}">Períodos</a>
                      <div class="dropdown-divider"></div>
                      <a class="dropdown-item CRN" href="#">[CRN] Encerrar Período</a>
                    </div>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" 
                       id="navbarDropdown" role="button" 
                       data-toggle="dropdown" 
                       aria-haspopup="true" aria-expanded="false">
                      Lançamentos
                    </a>
                    <div class="dropdown-menu sobre" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" data-nav="{{route('lancamento.index')}}" data-params="idTipoLancamento=6">Ajuste Manual</a>
                        {{-- <a class="dropdown-item" data-nav="{{route('lancamento.index')}}" data-params="idTipoLancamento=16">Diretoria</a> -- }}
                        <a class="dropdown-item" data-nav="{{route('lancamento.index')}}" data-params="idTipoLancamento=21">Arquivos Importados [CSV]</a>
                        <a class="dropdown-item" data-nav="{{route('importacsv')}}">Importar Arquivo [CSV]</a>
                        {{-- <a class="dropdown-item CRN" data-nav="">[CRN] Justificativas de Variação</a>
                        <a class="dropdown-item CRN" data-nav="">[CRN] Por Lote</a> -- }}
                    </div>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" 
                       id="navbarDropdown" role="button" 
                       data-toggle="dropdown" 
                       aria-haspopup="true" aria-expanded="false">
                      Processamentos
                    </a>
                    <div class="dropdown-menu sobre" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" data-nav="{{route('importarLancamento')}}"> Importar Lançamentos Contábeis</a>
                        <a class="dropdown-item" data-nav="{{route('processarRateios')}}"> Parâmetros de Rateio</a>
                        <a class="dropdown-item" data-nav="{{route('lancamentoTVI')}}"> Processar TVI's</a>
                        <a class="dropdown-item" data-nav="{{route('rateioLogistica')}}"> Rateio da Logística</a>
                    </div>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" 
                       id="navbarDropdown" role="button" 
                       data-toggle="dropdown" 
                       aria-haspopup="true" aria-expanded="false">
                      Relatórios
                    </a>
                    <div class="dropdown-menu sobre" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" data-nav="{{route('relatorioGerencial')}}">GERENCIAL</a>
                        <div class="dropdown-divider"></div>
                        {{-- <a class="dropdown-item CRN" data-nav="">[CRN] Análise de Variação</a> -- }}
                        {{-- <a class="dropdown-item CRN" data-nav="">[CRN] Exportação dos Lançamentos</a> -- }}
                        <a class="dropdown-item" data-nav="{{route('relatorioLancamentos')}}">Lançamentos Gerenciais</a>
                        <a class="dropdown-item" data-nav="{{route('reportArquivosCSV')}}">Lotes importados (CSV)</a>
                        {{-- <a class="dropdown-item CRN" data-nav="">[CRN] Saldo Gerencial x Contábil</a> -- }}
                        <a class="dropdown-item" data-nav="{{route('reportTVI')}}">TVI</a>


                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item [VAL]" data-nav="{{route('gerencialCentroCusto')}}">Cad. Centros de Custo</a>
                        <a class="dropdown-item [VAL]" data-nav="{{route('gerencialContaGerencial')}}">Cad. Contas Gerenciais</a>
                        <a class="dropdown-item [VAL]" data-nav="{{route('gerencialEmpresas')}}">Cad. Empresas</a>
                        <a class="dropdown-item [VAL]" data-nav="{{route('gerencialGrupoConta')}}">Cad. Grupos de Conta</a>
                        <a class="dropdown-item [VAL]" data-nav="{{route('gerencialRegional')}}">Cad. Regionais</a>
                        <a class="dropdown-item [VAL]" data-nav="{{route('gerencialSubGrupoConta')}}">Cad. Sub-Grupos de Conta</a>
                        <a class="dropdown-item [VAL]" data-nav="{{route('gerencialTipoLancamento')}}">Cad. Tipos de Lançamento</a>
                        <a class="dropdown-item [VAL]" data-nav="{{route('gerencialContaContabil')}}">Conta Gerencial x Conta Contabil</a>
                        <a class="dropdown-item [VAL]" data-nav="{{route('gerencialOutrasContas')}}">Exceções Outras Contas</a>
                        <a class="dropdown-item [VAL]" data-nav="{{route('gerencialParametroRateio')}}">Parâmetro de Rateio</a>
                        <a class="dropdown-item [VAL]" data-nav="{{route('reportContaContabil')}}">Plano de Contas Contábil</a>
                    </div>
                </li>
 --}}
                {{-- <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" 
                       id="navbarDropdown" role="button" 
                       data-toggle="dropdown" 
                       aria-haspopup="true" aria-expanded="false">
                      Utilitários
                    </a>
                    <div class="dropdown-menu sobre" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item IMP" data-nav="{{route('importarParametros')}}">[IMP] Importar Parâmetros de Rateio</a>

                    </div>
                </li> --}}
            </div>

            {{-- ÍCONE PARA LOGIN / LOGOUT --}}
            <div class="pull-right">
                @if(session('loged'))
                    {{session('nome')}} [{{session('_GER_tipoUsuarioGerencial')}}]
                    <span class="fa fa-user-slash mr-5 ml-2" title='Logout' id="logout" data-action="{{route('logout')}}" data-redir="{{env('APP_URL')}}"></span>
                @endif

            </div>


            {{-- EXIBE O PERÍODO ABERTO --}}
            @if (isset($periodoAtivo->periodoMes)) 
                <div class="pull-right">
                    <h3 class="tw-8">
                        <span class="periodo-text">{{$periodoAtivo->periodoMes}}/{{$periodoAtivo->periodoAno}}</span>
                        <small class="ts-5">(
                            @switch($periodoAtivo->periodoSituacao)
                                @case('FC')
                                    PB
                                    @break
                                @case('LC')
                                    LG
                                    @break
                                @default
                                {{$periodoAtivo->periodoSituacao}}
                            @endswitch
                            )</small>
                    </h3>
                </div>
            @endif
          </nav>

        <main id="main-app" class="main-app">
            @yield('content')
        </main>
    </div>


    {{-- Modal para exibição de mensagens --}}
    <div class="modal fade" id="messages" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title"></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            </div>
            <div class="modal-body">
                <!--// Mensagem //-->
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-warning" data-dismiss="modal">Ok</button>
            </div>
        </div>
        </div>
    </div>
  

    {{-- LOAD SPINNER --}}
    <div id="loadSpinner" class="d-none align-items-center justify-content-center">
        <div class="bg-light text-center p-4 border border-secondary rounded shadow">
            <span class="fa fa-radiation-alt fa-spin fa-5x text-orange"></span>
            <h4 class="tw-7 mt-2 text-gray">AGUARDE!</h4>
            <small>processando sua solicitação</small>
        </div>
    </div>

    <!-- bootstrap 4.5 -->
    <script src="https://code.jquery.com/jquery-3.5.1.js" integrity="sha256-QWo7LDvxbWT2tbbQ97B53yJnYU3WhH/C8ycbRAkjPDc=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>

    <!-- JS ---- LOCAL ------ -->
    <script src="./public/js/gerencial.js" defer crossorigin="anonymous"></script>

    <!-- JS ----- PRODUÇÃO ----- -- >
    <script src="{{ asset('js/gerencial.js') }}"></script>
-->

<script>
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    $(document).ready(function() { initjQ(); });
    $(document).ajaxComplete(function() { initjQ(); });
</script>

@php
    // Carrega as permissões de acesso do usuário logado e registra na sessão
    if (!$validUser) {
        echo "<span id='showMsg' data-title='USUÁRIO NÃO CADASTRADO'
                    data-message='O usuário ".session('nome').", não possui permissões definidas para acesso o Gerencial.'></span>";
        
        session()->flush();

        echo "<script>
                setTimeout(() => { window.location = '".env('APP_URL')."'; }, 5000);
            </script>";
    }
@endphp

</body>
</html>
