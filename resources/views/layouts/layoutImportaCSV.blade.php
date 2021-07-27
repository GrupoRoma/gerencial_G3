
<div class="filtro-relatorio border border-secondary p-3">
        <h3 class="text-center">IMPORTAR LANÇAMENTOS DE ARQUIVO CSV</h3>

        <form id="gerencial-form" action="{{route('importacsv')}}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="custom-file">
                <input type="file" name="arquivocsv" id="file-form" class="custom-file-inputs">
                <label for="file-form" class="custom-file-label">Selecione o arquivo para importação</label>
            </div>

            <div class="text-center mt-5">
                    <button type="submit" class="btn btn-orange btn-large">
                            <span class="fa fa-cogs fa-lg"></span> Enviar e Processar arquvio
                    </button>
            </div>
                
        </form>
</div>