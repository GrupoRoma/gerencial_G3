<div class="container">
        <div class="card">
                <div class="card-header text-white {{(isset($success) && $success ? 'bg-info' : 'bg-danger')}} text-center">
                        <h3>
                                @if (isset($success) && $success)  PROCESSAMENTO FINALIZADO
                                @else                              ERROS AO PROCESSAR LANÇAMENTOS GERENCIAIS
                                @endif
                        </h3>
                </div>
                <div class="card-body">
                        @foreach ($errors as $errorList)
                                <h5 class="{{(isset($success) && $success ? 'text-info' : 'text-danger')}} mt-3">{!!$errorList['errorTitle']!!}</h5>
                                {!!$errorList['error']!!}
                        @endforeach
                </div>
                @if (isset($success) && !$success)
                        <div class="card-footer text-danger text-center">VERIFIQUE OS ERROS APRESENTADOS E TENTE NOVAMENTE!</div>    
                @endif

        </div>
</div>