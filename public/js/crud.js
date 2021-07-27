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
    
    var centrosCusto    = [];
    var percentuais     = [];

    $('#addPercent').unbind('click').on('click', function() {

        // Identifica os valores informados (centro de custo e percentual)
        codigoCentroCusto       = $('#add-idCentroCusto').val();
        descricaoCentroCusto    = $('#add-idCentroCusto').find(':selected').text();
        percentual              = $('#add-percentual').val();

        if ($('#add-percentual').val() !== undefined && Number($('#add-percentual').val()) > 0) {
            // Transforma os valores já selecionados em objeto (Json)
            if (typeof $('#centroCustoPerc').val() !== 'object')  addTo = JSON.parse($('#centroCustoPerc').val());
            else                                                  addTo = $('#centroCustoPerc').val();

            // Valida o percentual para fechar em no máximo 100%
            total = 0;
            $.each(addTo, function(idx, val) {
                total += Number(val);
            });

            if ((total + Number(percentual)) > 100) {
                $('#messages .modal-title').html('ERRO !');
                $('#messages .modal-body').html('Os valores percentuais informados excedem 100%, favor verificar.');
                $('#messages').modal('show');
                $('#messages .modal-header').addClass("modal-header-error");
            }
            else {
                // Remove a seleção anterior do centro de custo e substitui
                if(addTo[codigoCentroCusto] !== undefined)  $('#PERC_'+codigoCentroCusto).remove();

                // Inclui o centro de custo selecionado
                addTo[codigoCentroCusto] = percentual;


                // Atualiza o valor do campo hidden no formulário
                $('#centroCustoPerc').val(JSON.stringify(addTo));

                // Inclui visual do centro de custo e percentual à lista
                dataAppend = '<div class="row border-bottom border-light pt-2" id="PERC_'+codigoCentroCusto+'"> <div class="col-5">'+codigoCentroCusto+' - '+descricaoCentroCusto+'</div> <div class="col-5">'+percentual+'%</div> <div class="col-2"> <button type="button" class="btn btn-danger btn-sm" onClick="delPercent('+codigoCentroCusto+')"> <span class="fa fa-trash fa-lg"></span> </button> </div> </div>';
                $('#listaCC').append(dataAppend); 
            }
        }
        else {
            $('#messages .modal-title').html('ERRO !');
            $('#messages .modal-body').html('Informe o percentual para o centro de custo selecionado.');
            $('#messages').modal('show');
            $('#messages .modal-header').addClass("modal-header-error");   
        }

    });

} //-- END crudjQ

function delPercent(indexDel) {
    if (typeof $('#centroCustoPerc').val() !== 'object')  delItem = JSON.parse($('#centroCustoPerc').val());
    else                                                  delItem = $('#centroCustoPerc').val();

    delItem[indexDel] = '[DELETE]';

    $('#centroCustoPerc').val(JSON.stringify(delItem));

    $('#PERC_'+indexDel).remove();

}