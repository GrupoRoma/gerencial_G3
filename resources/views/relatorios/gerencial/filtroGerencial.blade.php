@inject('periodo', 'App\Models\GerencialPeriodo');
@php
    $mesAno = $periodo->current();
@endphp
<script>
        $('#porEmpresa, #compEmpresa').click(function() {
                $('#codigoEmpresa').attr('disabled',     false);
                $('#codigoRegional').attr('disabled',    true);
                $('#codigoCentroCusto').attr('disabled', false);
        })

        $('#porRegional, #compRegional').click(function() {
                $('#codigoEmpresa').attr('disabled',     true);
                $('#codigoRegional').attr('disabled',    false);
                $('#codigoCentroCusto').attr('disabled', false);
        })

        $('#compMensal').click(function() {
                $('#codigoEmpresa').attr('disabled',     false);
                $('#codigoRegional').attr('disabled',    true);
                $('#codigoCentroCusto').attr('disabled', true);
        })

</script>

<div class="filtro-relatorio border border-secondary p-3">
        <h3 class="text-center">RELATÓRIO GERENCIAL - CRITÉRIOS DE EXIBIÇÃO</h3>
        <form id="gerencial-form" 
                method="POST"
                action="{{route(Route::currentRouteName().'_build')}}">
                {{-- Token --}}
                @csrf

                <input class="form-control" type="text" name="periodo" id="periodo" maxlength="7" placeholder="Período MM/YYYY" value="{{$mesAno->MESANO}}">

                <div class="form-group row pt-2">
                        <div class="col-sm-2 col-form-label">LAYOUT</div>
                        <div class="col-sm-10">
                                <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" name="layoutRelatorio" id="porEmpresa" value='empresa' class="custom-control-input" checked> 
                                        <label for="porEmpresa" class="custom-control-label"> Empresa</label>
                                </div>
                                <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" name="layoutRelatorio" id="porRegional" value='regional' class="custom-control-input"> 
                                        <label for="porRegional" class="custom-control-label"> Regional</label>
                                </div>
                                <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" name="layoutRelatorio" id="compMensal" value='comparativoMensal' class="custom-control-input"> 
                                        <label for="compMensal" class="custom-control-label"> Comparativo Mensal</label>
                                </div>
                                <br>
                                <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" name="layoutRelatorio" id="compEmpresa" value='comparativoEmpresa' class="custom-control-input"> 
                                        <label for="compEmpresa" class="custom-control-label"> Comparativo C.Custo Empresa</label>
                                </div>
                                <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" name="layoutRelatorio" id="compRegional" value='comparativoRegional' class="custom-control-input"> 
                                        <label for="compRegional" class="custom-control-label"> Comparativo C.Custo Regional</label>
                                </div>
                        </div>
                </div>

                <div class="row">
                        <div class="col">
                                <div class="form-group">
                                        <label for="codigoEmpresa">EMPRESA</label>
                                        <select class="custom-select" name="codigoEmpresa[]" id="codigoEmpresa" multiple size="5">
                                                @foreach ($empresas as $row => $data)
                                                        <option value="{{$data->id}}">{{$data->nomeAlternativo}}</option>
                                                @endforeach
                                        </select>
                                </div>
                        </div>
                        <div class="col">
                                <div class="form-group">
                                        <label for="codigoRegional">REGIONAL</label>
                                        <select class="custom-select" name="codigoRegional[]" id="codigoRegional" multiple size="5" disabled>
                                                @foreach ($regionais as $row => $data)
                                                        <option value="{{$data->id}}">{{$data->descricaoRegional}}</option>
                                                @endforeach
                                        </select>
                                </div>
                        </div>

                        <div class="col">
                                <div class="form-group">
                                        <label for="codigoCentroCusto">CENTRO DE CUSTO</label>
                                        <select class="custom-select" name="codigoCentroCusto[]" id="codigoCentroCusto" multiple size="5">
                                                @foreach ($centroCusto as $row => $data)
                                                        <option value="{{$data->id}}">{{$data->descricaoCentroCusto}}</option>
                                                @endforeach
                                        </select>
                                </div>
                        </div>
                </div>
                

                <div class="form-group pt-2">
                        <div class="custom-control custom-checkbox custom-control-inline">
                                <input type="checkbox" name="extras" id="extras" value='1' class="custom-control-input" checked> 
                                <label for="extras" class="custom-control-label">Lançamentos Extras</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                                <input type="checkbox" name="acumulado" id="acumulado" value='1' class="custom-control-input"> 
                                <label for="acumulado" class="custom-control-label">Acumulado</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                                <input type="checkbox" name="decimal" id="decimal" value='1' class="custom-control-input"> 
                                <label for="decimal" class="custom-control-label">Casas Decimais</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                                <input type="checkbox" name="consolidado" id="consolidado" value='1' class="custom-control-input"> 
                                <label for="consolidado" class="custom-control-label">Consolidado</label>
                        </div>
                </div>

                <div class="text-center mt-5">
                        <button type="submit" class="btn btn-orange btn-large">GERAR RELATÓRIO</button>
                </div>
        </form>
</div>
