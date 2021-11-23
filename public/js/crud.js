/**
 *  CRUD.JS
 *  Auxiliar para as telas de CRUD
 */


$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

$(document).ready(function() { crudjQ(); });
$(document).ajaxComplete(function() { crudjQ(); });

function crudjQ() {
    
    var addTo           = {};
    var centrosCusto    = [];
    var percentuais     = [];

    $('#addPercent').unbind('click').on('click', function() {

        // Identifica os valores informados (centro de custo e percentual)
        codigoEmpresa           = $('#add-idEmpresa').val();
        nomeEmpresa             = $('#add-idEmpresa').find(':selected').text();
        codigoCentroCusto       = $('#add-idCentroCusto').val();
        descricaoCentroCusto    = $('#add-idCentroCusto').find(':selected').text();
        percentual              = $('#add-percentual').val();

        if ($('#add-percentual').val() !== undefined && Number($('#add-percentual').val()) > 0) {
            // Transforma os valores já selecionados em objeto (Json)
            if (typeof $('#centroCustoPerc').val() !== 'object')  addTo = JSON.parse($('#centroCustoPerc').val());
            else                                                  addTo = $('#centroCustoPerc').val();

            // Valida o percentual para fechar em no máximo 100%
/*             total = 0;
            $.each(addTo, function(idx, val) {
                total += Number(val);
            });

            if ((total + Number(percentual)) > 100) {
                $('#messages .modal-title').html('ERRO !');
                $('#messages .modal-body').html('Os valores percentuais informados excedem 100%, favor verificar.');
                $('#messages').modal('show');
                $('#messages .modal-header').addClass("modal-header-error");
            }
            else { */
                // Remove a seleção anterior do centro de custo e substitui
                if (addTo[codigoEmpresa] === undefined)  addTo[codigoEmpresa]  = {};
                if(addTo[codigoEmpresa][codigoCentroCusto] !== undefined)  $('#PERC_'+codigoEmpresa+'_'+codigoCentroCusto).remove();

                // Inclui o centro de custo selecionado
                addTo[codigoEmpresa][codigoCentroCusto] = percentual;


                // Atualiza o valor do campo hidden no formulário
                $('#centroCustoPerc').val(JSON.stringify(addTo));

                // Inclui visual do centro de custo e percentual à lista
                $('<div>', {class: 'row border-bottom border-light pt-2',
                            id: 'PERC_'+codigoEmpresa+'_'+codigoCentroCusto}).appendTo('#listaCC');
                    $('<div>', {class: 'col-4',
                                html: codigoEmpresa+' - '+nomeEmpresa }).appendTo('#PERC_'+codigoEmpresa+'_'+codigoCentroCusto);
                    $('<div>', {class: 'col-4',
                                html: codigoCentroCusto+' - '+descricaoCentroCusto}).appendTo('#PERC_'+codigoEmpresa+'_'+codigoCentroCusto);
                    $('<div>', {class: 'col-3 text-right',
                                html: percentual+'%'}).appendTo('#PERC_'+codigoEmpresa+'_'+codigoCentroCusto);
                    $('<div>', {class: 'col-1 btn-delete'}).appendTo('#PERC_'+codigoEmpresa+'_'+codigoCentroCusto);

                    $('<button>', {type: 'button',
                                   class: 'btn btn-danger btn-sm',
                                   onClick: "delPercent('"+codigoEmpresa+"_"+codigoCentroCusto+"')"}).appendTo('#PERC_'+codigoEmpresa+'_'+codigoCentroCusto+' .btn-delete');
                    $('<span>', { class: "fa fa-trash fa-lg"}).appendTo('#PERC_'+codigoEmpresa+'_'+codigoCentroCusto+' .btn');

/*                 dataAppend = '<div class="row border-bottom border-light pt-2" id="PERC_'+codigoEmpresa+'_'+codigoCentroCusto+'"> 
                            <div class="col-4">'+codigoEmpresa+' - '+nomeEmpresa+'</div>
                             <div class="col-4">'+codigoCentroCusto+' - '+descricaoCentroCusto+'</div>
                             <div class="col-3">'+percentual+'%</div>
                             
                             <div class="col-1"> <button type="button" class="btn btn-danger btn-sm" onClick="delPercent('+codigoEmpresa+'_'+codigoCentroCusto+')"> <span class="fa fa-trash fa-lg"></span> </button> </div> </div>';
                $('#listaCC').append(dataAppend); 
 *///            }
        }
        else {
            $('#messages .modal-title').html('ERRO !');
            $('#messages .modal-body').html('Informe o percentual para o centro de custo selecionado.');
            $('#messages').modal('show');
            $('#messages .modal-header').addClass("modal-header-error");   
        }

    });

} //-- END crudjQ

function delPercent(indexDel, id) {
    if (typeof $('#centroCustoPerc').val() !== 'object')  delItem = JSON.parse($('#centroCustoPerc').val());
    else                                                  delItem = $('#centroCustoPerc').val();

    index = indexDel.split('_');

    delItem[index[0]][index[1]] = '[DELETE]';
    $('#centroCustoPerc').val(JSON.stringify(delItem));
    $('#PERC_'+indexDel).remove();

}