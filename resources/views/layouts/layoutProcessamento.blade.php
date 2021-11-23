@php
//    $routeName = substr(Route::currentRouteName(),0,strrpos(Route::currentRouteName(),'.'));
        $gerencialModel = app('App\\Models\\GerencialRegional');
@endphp

<div class="filtro-relatorio border border-secondary p-3">
        <h3 class="text-center">{{$tituloPagina}}</h3>

        <form id="gerencial-form" action="{{route($action)}}" method="POST">
                @csrf
                <h4>PERÍODO ATIVO | {{$mes}}/{{$ano}}]</h4>

                <div class="form-group row pt-2">
                        <div class="col-sm-3 col-form-label text-right">Mês de Refrência</div>
                        <div class="col-sm-9">
                                <select name="mesReferencia" id="mesReferencia" class="form-control">
                                        <option value="">--- mês de referência ---</option>
                                        <option value="01" {{(isset($mes) && $mes == '01' ? 'selected' : '')}}>Janeiro</option>
                                        <option value="02" {{(isset($mes) && $mes == '02' ? 'selected' : '')}}>Fevereiro</option>
                                        <option value="03" {{(isset($mes) && $mes == '03' ? 'selected' : '')}}>Março</option>
                                        <option value="04" {{(isset($mes) && $mes == '04' ? 'selected' : '')}}>Abril</option>
                                        <option value="05" {{(isset($mes) && $mes == '05' ? 'selected' : '')}}>Maio</option>
                                        <option value="06" {{(isset($mes) && $mes == '06' ? 'selected' : '')}}>Junho</option>
                                        <option value="07" {{(isset($mes) && $mes == '07' ? 'selected' : '')}}>Julho</option>
                                        <option value="08" {{(isset($mes) && $mes == '08' ? 'selected' : '')}}>Agosto</option>
                                        <option value="09" {{(isset($mes) && $mes == '09' ? 'selected' : '')}}>Setembro</option>
                                        <option value="10" {{(isset($mes) && $mes == '10' ? 'selected' : '')}}>Outubro</option>
                                        <option value="11" {{(isset($mes) && $mes == '11' ? 'selected' : '')}}>Novembro</option>
                                        <option value="12" {{(isset($mes) && $mes == '12' ? 'selected' : '')}}>Dezembro</option>
                                </select>
                        </div>
                </div>
                
                <div class="form-group row pt-2">
                        <div class="col-sm-3 col-form-label text-right">Ano de Referência</div>
                        <div class="col-sm-9">
                                <input type="number" name="anoReferencia" class="form-control" min="2020" max="2099" maxlength="4" placeholder="ano de referência" value="{{($ano ?? '')}}">
                        </div>
                </div>

                <div class="form-group row pt-2">
                        <div class="col-sm-3 col-form-label text-right">Regional</div>
                        <div class="col-sm-9">
                                <select name="codigoRegional" id="codigoRegional" class="form-control">
                                        <option value="">--- selecione a regional ---</option>
                                        @php
                                        $listaRegionais = $gerencialModel::orderBy('descricaoRegional')->get();
                                        @endphp

                                        @foreach ($listaRegionais as $row => $regional) {
                                                <option value="{{$regional->id}}">{{$regional->descricaoRegional}}</option>
                                        }
                                        
                                        @endforeach
                                </select>
                        </div>
                </div>

                <div class="form-group pt-2">
                        <h4>PROCESSAR:</h4>
                        <div class="custom-control custom-checkbox custom-control-inline">
                                <input type="checkbox" name="importarLancamentos" id="importarLancamentos" value='1' class="custom-control-input" checked> 
                                <label for="importarLancamentos" class="custom-control-label">Lançamentos Contábeis</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                                <input type="checkbox" name="outrasContas" id="outrasContas" value='1' class="custom-control-input"> 
                                <label for="outrasContas" class="custom-control-label">[EXCEÇÕES] - Outras Contas Contábeis</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                                <input type="checkbox" name="amortizacao" id="amortizacao" value='1' class="custom-control-input"> 
                                <label for="amortizacao" class="custom-control-label">[EXCEÇÕES] - Amortizações</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                                <input type="checkbox" name="estorno" id="estorno" value='1' class="custom-control-input"> 
                                <label for="estorno" class="custom-control-label">Parâmetro Estorno</label>
                        </div>
<!--                        <div class="custom-control custom-checkbox custom-control-inline">
                                <input type="checkbox" name="consolidado" id="consolidado" value='1' class="custom-control-input"> 
                                <label for="consolidado" class="custom-control-label">Consolidado</label>
                        </div>
-->
                </div>

                <div class="text-center mt-5">
                        <button type="submit" class="btn btn-orange btn-large">
                                <span class="fa fa-cogs fa-lg"></span> Processar Período Informado
                        </button>
                </div>
                
        </form>
</div>