
<div class="filtro-relatorio border border-secondary p-3">
        <h3 class="text-center">IMPORTAR LANÇAMENTOS DE ARQUIVO CSV</h3>

        <div class="container-fluid text-black mb-3">
                <h5 class="tw-7">LAYOUT DO ARQUIVO .CSV *</h5>
                <div class="row ts-8 border-dark border-bottom text-black">
                        <div class="col border-dark border-right">INFO</div>
                        <div class="col-2 border-dark border-right">EMPRESA *</div>
                        <div class="col border-dark border-right">C.CUSTO *</div>
                        <div class="col border-dark border-right">VALOR</div>
                        <div class="col border-dark border-right">CONTA GER. (DEB) *</div>
                        <div class="col border-dark border-right">CONTA GER. (CRD) *</div>
                        <div class="col-2 border-dark border-right">HISTÓRICO</div>
                        <div class="col border-dark border-right">REVERSÃO (S/N)</div>
                        <div class="col border-dark border-right">MÊS (MM)</div>
                        <div class="col">ANO (YYYY)</div>
                </div>
                <div class="row ts-8 border-dark border-bottom">
                        <div class="col">--</div>
                        <div class="col-2">CITROEN CID INDUSTRIAL</div>
                        <div class="col">ADMC</div>
                        <div class="col">123,99</div>
                        <div class="col">03001</div>
                        <div class="col">03005</div>
                        <div class="col-2">Histórico para identificação do lançamento (se necessário)</div>
                        <div class="col">N</div>
                        <div class="col">07</div>
                        <div class="col">2021</div>
                </div>
                
                <small>* Os dados chaves (EMPRESA, C.CUSTO, CONTA GER. (DEV) e CONTA GER. (CRD)), devem , obrigatóriamente, estar IDÊNTICAS ao cadastrado no Gerencial.</small>
        </div>

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