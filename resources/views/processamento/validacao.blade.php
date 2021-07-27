<div class="container">
        <div class="card">
                <div class="card-header text-white bg-danger text-center"><h3>ERROS AO PROCESSAR LANÇAMENTOS GERENCIAIS</h3></div>
                <div class="card-body">
                        @foreach ($errors as $errorList)
                                <h5 class="text-danger mt-3">{!!$errorList['errorTitle']!!}</h5>
                                {!!$errorList['error']!!}
                        @endforeach
                </div>
                <div class="card-footer text-danger text-center">VERIFIQUE OS ERROS APRESENTADOS E TENTE NOVAMENTE!</div>
        </div>
</div>