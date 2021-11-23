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
                <span class="fa fa-chart-line"></span> {{ config('app.name', 'Laravel') }}
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

                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" 
                     id="navbarDropdown" role="button" 
                     data-toggle="dropdown" 
                     aria-haspopup="true" aria-expanded="false">
                    <span class="fa fa-file"></span> Cadastros
                  </a>
                  <div class="dropdown-menu sobre" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item" data-nav="{{route('empresas.index')}}">[VAL] Empresa</a>
                    <a class="dropdown-item" data-nav="{{route('baseCalculo.index')}}">[VAL] Base de Cálculo</a>
                    <a class="dropdown-item" data-nav="{{route('baseCalculoConta.index')}}">[VAL] Base de Cálculo - Contas</a>
                    <a class="dropdown-item" data-nav="{{route('centroCusto.index')}}">[VAL] Centro de Custo</a>
                    <a class="dropdown-item" data-nav="{{route('contaGerencial.index')}}">[VAL] Conta Gerencial</a>
                    <a class="dropdown-item" data-nav="{{route('contaContabil.index')}}">[VAL] Conta Gerencial X Conta Contábil</a>
                    <a class="dropdown-item" data-nav="{{route('grupoConta.index')}}">[VAL] Grupo de Conta</a>
                    <a class="dropdown-item" data-nav="{{route('regional.index')}}">[VAL] Regionais</a>
                    <a class="dropdown-item" data-nav="{{route('subGrupoConta.index')}}">[VAL] Sub-Grupo de Conta</a>
                    <a class="dropdown-item" data-nav="{{route('tipoLancamento.index')}}">[VAL] Tipos de Lançamento</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item CRN" data-nav="{{route('permissaoUsuario.index')}}">[CRN] Permissões por Usuário</a>
                  </div>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" 
                       id="navbarDropdown" role="button" 
                       data-toggle="dropdown" 
                       aria-haspopup="true" aria-expanded="false">
                      <span class="fa fa-file-alt"></span> Cadastro Exceções
                    </a>
                    <div class="dropdown-menu sobre" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" data-nav="{{route('outrasContas.index')}}">[VAL] Outras Contas Contábeis</a>
                        <a class="dropdown-item" data-nav="{{route('amortizacao.index')}}">[VAL] Amortizações</a>
                    </div>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" 
                       id="navbarDropdown" role="button" 
                       data-toggle="dropdown" 
                       aria-haspopup="true" aria-expanded="false">
                      <span class="fa fa-file-alt"></span> Cadastro Parâmetros
                    </a>
                    <div class="dropdown-menu sobre" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" data-nav="{{route('estorno.index')}}">[VAL] Estornos</a>
                        <a class="dropdown-item" data-nav="{{route('parametroRateio.index')}}">[VAL] Parâmetros de Rateio</a>
                        <a class="dropdown-item" data-nav="{{route('tabelaRateio.index')}}">[VAL] Tabela de Referência</a>
                        <a class="dropdown-item" data-nav="{{route('transferenciaEmpresa.index')}}">[VAL] Transferência de Empresa</a>
                        <a class="dropdown-item" data-nav="{{route('transferenciaCentroCusto.index')}}">[VAL] Transferência de C.Custo</a>
                    </div>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" 
                       id="navbarDropdown" role="button" 
                       data-toggle="dropdown" 
                       aria-haspopup="true" aria-expanded="false">
                      <span class="fa fa-file-alt"></span> Seleção de Períodos (Sessão)
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
                      <span class="fa fa-file-alt"></span> Lançamentos
                    </a>
                    <div class="dropdown-menu sobre" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" data-nav="{{route('lancamento.index')}}" data-params="idTipoLancamento=6">[VAL] Ajuste Manual</a>
                        {{-- <a class="dropdown-item" data-nav="{{route('lancamento.index')}}" data-params="idTipoLancamento=16">[VAL] Diretoria</a> --}}
                        <a class="dropdown-item" data-nav="{{route('lancamento.index')}}" data-params="idTipoLancamento=21">[VAL] Arquivos Importados [CSV]</a>
                        <a class="dropdown-item" data-nav="{{route('importacsv')}}">[VAL] Importar Arquivo (.csv)</a>
                        {{-- <a class="dropdown-item CRN" data-nav="">[CRN] Justificativas de Variação</a>
                        <a class="dropdown-item CRN" data-nav="">[CRN] Por Lote</a> --}}
                    </div>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" 
                       id="navbarDropdown" role="button" 
                       data-toggle="dropdown" 
                       aria-haspopup="true" aria-expanded="false">
                      <span class="fa fa-file-alt"></span> Processamentos
                    </a>
                    <div class="dropdown-menu sobre" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" data-nav="{{route('importarLancamento')}}">[VAL]  Importar Lançamentos Contábeis</a>
                        <a class="dropdown-item" data-nav="{{route('processarRateios')}}">[VAL]  Parâmetros de Rateio</a>
                        <a class="dropdown-item" data-nav="{{route('rateioLogistica')}}">[VAL]  Rateio da Logística</a>
                    </div>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" 
                       id="navbarDropdown" role="button" 
                       data-toggle="dropdown" 
                       aria-haspopup="true" aria-expanded="false">
                      <span class="fa fa-file-alt"></span> Relatórios
                    </a>
                    <div class="dropdown-menu sobre" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" data-nav="{{route('relatorioGerencial')}}">[VAL] GERENCIAL</a>
                        <div class="dropdown-divider"></div>
                        {{-- <a class="dropdown-item CRN" data-nav="">[CRN] Análise de Variação</a> --}}
                        <a class="dropdown-item CRN" data-nav="">[CRN] Exportação dos Lançamentos</a>
                        <a class="dropdown-item" data-nav="{{route('relatorioLancamentos')}}">[CRN] Lançamentos Gerenciais</a>
                        <a class="dropdown-item CRN" data-nav="">[CRN] TVI</a>
                        <a class="dropdown-item CRN" data-nav="">[CRN] Saldo Gerencial x Contábil</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item [VAL]" data-nav="{{route('gerencialEmpresas')}}">[VAL] Cad. Empresas</a>
                        <a class="dropdown-item [VAL]" data-nav="{{route('gerencialCentroCusto')}}">[VAL] Cad. Centros de Custo</a>
                        <a class="dropdown-item [VAL]" data-nav="{{route('gerencialContaGerencial')}}">[VAL] Cad. Contas Gerenciais</a>
                        <a class="dropdown-item [VAL]" data-nav="{{route('gerencialGrupoConta')}}">[VAL] Cad. Grupos de Conta</a>
                        <a class="dropdown-item [VAL]" data-nav="{{route('gerencialSubGrupoConta')}}">[VAL] Cad. Sub-Grupos de Conta</a>
                        <a class="dropdown-item [VAL]" data-nav="{{route('gerencialRegional')}}">[VAL] Cad. Regionais</a>
                        <a class="dropdown-item [VAL]" data-nav="{{route('gerencialTipoLancamento')}}">[VAL] Cad. Tipos de Lançamento</a>
                        <a class="dropdown-item [VAL]" data-nav="{{route('gerencialOutrasContas')}}">[VAL] Exceções Outras Contas</a>
                        <a class="dropdown-item [VAL]" data-nav="{{route('gerencialContaContabil')}}">[VAL] Conta Gerencial x Conta Contabil</a>
                        <a class="dropdown-item [VAL]" data-nav="{{route('gerencialParametroRateio')}}">[VAL] Parâmetro de Rateio</a>
                    </div>
                </li>

                {{-- <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" 
                       id="navbarDropdown" role="button" 
                       data-toggle="dropdown" 
                       aria-haspopup="true" aria-expanded="false">
                      <span class="fa fa-file-alt"></span> Utilitários
                    </a>
                    <div class="dropdown-menu sobre" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item IMP" data-nav="{{route('importarParametros')}}">[IMP] Importar Parâmetros de Rateio</a>

                    </div>
                </li> --}}
            </div>

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


    <script>
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        $(document).ready(function() { initjQ(); });
        $(document).ajaxComplete(function() { initjQ(); });

        function initjQ() {
            $('[title]').tooltip('hide');

            if($('#showMsg').is(':visible')) {
                $('#messages .modal-title').html($('#showMsg').data('title'));
                $('#messages .modal-body').html($('#showMsg').data('message'));
                $('#messages').modal('show');
            }

            // Navegação por click
            // utiliza a propriedade data-nav para identificar e associar como link da plicação
            $('[data-nav]').unbind( "click" ).on('click', function(event) {
                event.preventDefault();

                // LOCAL - DESENVOLVIMENTO
                if ($(this).data('nav').indexOf('public') > 0)  url = $(this).data('nav');
                else                                            url = $(this).data('nav').replace('gerencial', 'gerencial/public');

                // PRODUÇÃO
                // url = $(this).data('nav');

                urlParams   = '{}';
                urlMethod   = 'GET';

                //if ($(this).data('params') !== undefined) urlParams   = $(this).data('params');
                if ($(this).data('params') !== undefined) urlParams   = $(this).data('params');
                if ($(this).data('method') !== undefined) urlMethod   = $(this).data('method');

                $.ajax({
                        url: url,
                        data: urlParams,
                        method: urlMethod,
                        beforeSend: function() {
                            // Show spinner
                            $('#loadSpinner').removeClass("d-none").addClass("d-flex");
                        },
                        success: function(data, status, xhr) {
                            $('#main-app').html(data);
                        }
                }).done(function() {
                    $('#loadSpinner').removeClass("d-flex").addClass("d-none");;
                });

                $(this).closest('.dropdown-menu').collapse('toggle');
                event.stopPropagation();
            });


            // SUBMIT DOS FORMULÁRIOS CRUD
            $('#gerencial-form').unbind('submit').on('submit', function(event) {
                event.preventDefault();
                event.stopPropagation();

                var redir = false;
                if ($(this).data('redir') !== undefined) {
                    // LOCAL - DESENVOLVIMENTO
                    if ($(this).data('redir').indexOf('public') > 0)  urlRedir = $(this).data('redir');
                    else                                              urlRedir = $(this).data('redir').replace('gerencial', 'gerencial/public');

                    // PRODUÇÃO
                    // urlRedir = $(this).data('redir');

                    if (urlRedir.lastIndexOf('.') > 25) urlRedir = urlRedir.substr(0, urlRedir.lastIndexOf('.'));

                    redir   = true;
                }

                //formData = $(this).serialize();
                $.ajax({
                    data        : new FormData(this),   // formData,
                    url         : $(this).attr('action'),
                    method      : 'POST',       //$(this).attr('method'),
                    contentType : false,
                    processData : false,
                    beforeSend: function() {
                        $('#loadSpinner').removeClass("d-none").addClass("d-flex");
                    },
                    success : function(data, status, xhr) {
                        if (redir) {
                            $('#main-app').load(urlRedir)
                        }
                        else {
                            $('#main-app').html(data);
                        }
                    },
                    error: function(data, status, xhr) {
                        errorMessage = "<ul>";
                        $.each(data.responseJSON, function(k,v){
                            errorMessage += "<li>"+v+"</li>";
                        });

                        errorMessage += "</ul>";

                        $('#messages .modal-title').html('ERRO DE FOMULÁRIO');
                        //$('#messages .modal-body').html(data.responseText);
                        $('#messages .modal-body').html(errorMessage);
                        $('#messages').modal('show');
                        
                        $('#messages .modal-header').addClass("modal-header-error");
                        
                        $('#loadSpinner').removeClass("d-flex").addClass("d-none");
                    }

                }).done(function() {
                    $('#loadSpinner').removeClass("d-flex").addClass("d-none");
                    if ($("#report-selection")) $("#report-selection").collapse('hide');
                });

                event.stopImmediatePropagation();
            });

            // Confirmação de exclusão de dados de formulário
            $('[data-confirm]').unbind( "click" ).on('click', function() {

                // LOCAL - DESENVOLVIMENTO
                if ($(this).data('confirm').indexOf('public') > 0)  url = $(this).data('confirm');
                else                                                url = $(this).data('confirm').replace('gerencial', 'gerencial/public');

                // PRODUÇÃO
                //url = $(this).data('confirm');

                //url += '/destroy';
                
                delInfo     = $(this).data('show');
                urlRedir    = $(this).data('redir');

                $('#delete-confirm').modal('show');
                $('#delete-confirm .data-del').html(delInfo);

                $('#delete-confirm .confirm').click(function(event) {
                    $('#delete-confirm').modal('hide');

                    event.preventDefault();
                    $.ajax({
                        method  : 'DELETE',
                        url     : url,
                        success : function(data, status, xhr) {
                            $('#main-app').load(urlRedir);
                        }
                    });
                });
                
            });

            // Exibe tooltip para todas as tags que tenha a propriedade title
            $('[title]').tooltip();

            // Filtra as linhas de uma tabela de dados com base na string de busca
            $("#tdSearch").unbind( "keyup" ).on("keyup", function() {
                var value = $(this).val().toLowerCase();
                var count = 0;
                $("#tableData tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
                $('.regCount').html($("#tableData tbody tr:visible").length);
            });
        
            /**
            *  RELATÓRIOS TOOLBAR ACTIONS
            */
            $('.report-tool').unbind('click').on('click', function() {
                toolAction  = $(this).data('action');
                
                if ($(this).data('target'))     toolTarget  = $(this).data('target');
                if ($(this).data('content'))    toolContent = $(this).data('content');

                switch (toolAction) {
                    case 'print':
                        print(toolTarget);
                    
                }
            });

            /** 
            *  --------- RELATÓRIO GERENCIAL
            **************************************************************************/
            /** 
            *  Explode os visualização dos lançamentos da conta / centro centro de custo
            */
            $('[data-explode]').unbind('click').on('click', function() {
                rowData = $(this).data('explode');

                // Executa o método para listar o detalhamento da conta
                $.ajax({
                    data    : 'mes='+rowData.mesLancamento+'&ano='+rowData.anoLancamento+'&codigoEmpresa='+rowData.codigoEmpresa+'&codigoContaGerencial='+rowData.codigoContaGerencial,
                    // LOCAL - DESENVOLVIMENTO
                    url     : document.location+'public/detalheConta',

                    // PRODUTÇÃO
                    //url     : document.location+'detalheConta',

                    method  : 'POST',
                    beforeSend: function() {
                        $('#loadSpinner').removeClass("d-none").addClass("d-flex");
                    },
                    success : function(data, status, xhr) {
                        $('#messages .modal-title').html('DETALHAMENTO DE CONTA GERENCIAL');
                        $('#messages .modal-body').html(data);
                        $('#messages .modal-dialog').addClass('modal-dialog-centered');
                        $('#messages .modal-dialog').addClass('modal-lg');
                        $('#messages').modal('show');
                    },
                    error: function(data, status, xhr) {
                        $('#messages .modal-title').html('DETALHAMENTO DE CONTA GERENCIAL');
                        $('#messages .modal-body').html('Ocorreu um erro inesperado.');
                        $('#messages').modal('show');
                        
                        $('#messages .modal-header').addClass("modal-header-error");
                        
                        $('#loadSpinner').removeClass("d-flex").addClass("d-none");
                    }
                }).done(function() {
                    $('#loadSpinner').removeClass("d-flex").addClass("d-none");
                });

            });

            $('.updateFormData').unbind('change').on('change', function() {
                target  = $(this).data('target');
                method  = $(this).data('method');
                value   = $(this).val();

                $.ajax({
                    data    : 'value='+value,
                    // LOCAL - DESENVOLVIMENTO
                    url     : document.location+'public/'+method,

                    // PRODUÇÃO
                    //url     : document.location+method,

                    method  : 'GET',
                    beforeSend: function() {
                        $('#loadSpinner').removeClass("d-none").addClass("d-flex");
                    },
                    success : function(data, status, xhr) {
                        $(target).html(data);
                    },
                    error: function(data, status, xhr) {
                        $(target).html('ERRO NA ATUALIZAÇÃO DOS DADOS.');
                    }
                }).done(function() {
                    $('#loadSpinner').removeClass("d-flex").addClass("d-none");
                });

            });
        }

        function print(target) {
            var wPrint = window.open();

            printContent  = '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">';

            // LOCAL - DESENVOLVIMENTO
            printContent += '<link href="{{ asset('public/css/app.css') }}" rel="stylesheet">';
            printContent += '<link href="{{ asset('public/css/reports.css') }}" rel="stylesheet">';

            // PRODUÇÃO
            //printContent += '<link href="{{ asset('css/app.css') }}" rel="stylesheet">';
            //printContent += '<link href="{{ asset('css/reports.css') }}" rel="stylesheet">';

            printContent += $(target).html();

            wPrint.document.open();
            wPrint.document.write(printContent);
            wPrint.document.close();

            setInterval(function() { wPrint.close(); }, 2000);

            setTimeout(function() { wPrint.window.print(); }, 1000);

        }

    </script>
</body>
</html>
