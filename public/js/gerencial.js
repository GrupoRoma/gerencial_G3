function initjQ() {
    $('[title]').tooltip('hide');

    if($('#showMsg').is(':visible')) {
        $('#messages .modal-title').html($('#showMsg').data('title'));
        $('#messages .modal-body').html($('#showMsg').data('message'));
        $('#messages').modal('show');
    }

    // LOGOUT
     $('#logout').off('click').on('click', function(event) {
        event.preventDefault();

        redir   = $(this).data('redir');
        
        // LOCAL - DESENVOLVIMENTO
        if ($(this).data('action').indexOf('public') > 0)   action = $(this).data('action');
        else                                                action = $(this).data('action').replace('gerencial', 'gerencial/public');

        $('#main-app').load(action);

        window.location.replace(redir);
    });

    // Navegação por click
    // utiliza a propriedade data-nav para identificar e associar como link da plicação
    $('[data-nav]').unbind( "click" ).on('click', function(event) {
        event.preventDefault();

        // LOCAL - DESENVOLVIMENTO
        if ($(this).data('nav').indexOf('public') > 0)  url = $(this).data('nav');
        else                                            url = $(this).data('nav').replace('gerencial', 'gerencial/public');

        // PRODUÇÃO
        //url = $(this).data('nav');

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
            //urlRedir = $(this).data('redir');

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
        rowData     = $(this).data('explode');
        regional    = $(this).data('regional');

        // Executa o método para listar o detalhamento da conta
        $.ajax({
            data    : 'mes='+rowData.mesLancamento+'&ano='+rowData.anoLancamento+'&codigoEmpresa='+rowData.codigoEmpresa+'&codigoContaGerencial='+rowData.codigoContaGerencial+'&regional='+regional.regional+'&codigoRegional='+regional.codigoRegional,
            // LOCAL - DESENVOLVIMENTO
            url     : document.location+'public/detalheConta',

            // PRODUÇÃO
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
    printContent += '<link href="{{ asset('+"public/css/app.css"+') }}" rel="stylesheet">';
    printContent += '<link href="{{ asset('+"public/css/reports.css"+') }}" rel="stylesheet">';

    // PRODUÇÃO
    printContent += '<link href="{{ asset('+"css/app.css"+') }}" rel="stylesheet">';
    printContent += '<link href="{{ asset('+"css/reports.css"+') }}" rel="stylesheet">';

    printContent += $(target).html();

    wPrint.document.open();
    wPrint.document.write(printContent);
    wPrint.document.close();

    setInterval(function() { wPrint.close(); }, 2000);

    setTimeout(function() { wPrint.window.print(); }, 1000);

}
