@push('scripts')
    <script src='{{ asset('js/crud.js') }}'></script>
@endpush

@inject('DB', 'Illuminate\Support\Facades\DB')
@inject('tabelaRateio', 'App\Models\GerencialTabelaRateio')
@inject('centroCusto', 'App\Models\GerencialCentroCusto')

@php
    //$tabela = new tabelaRateio;

    $values         = [];
    $percentuais    = [];
    $hideValue      = '{}';

    if (isset($id) && !empty($id)) {
        $dbData = DB::select('SELECT id, descricao, /*idEmpresa,*/ tabelaAtiva
                                FROM   GAMA..G3_gerencialTabelaRateios   (nolock)
                                WHERE  id = '.$id);
        $editData = $dbData[0];
//        $destino = json_decode($editData->destino);
        $values  = ['id'            => $editData->id,
                    'descricao'     => $editData->descricao,
                    /* 'idEmpresa'     => $editData->idEmpresa, */
                    'tabelaAtiva'   => $editData->tabelaAtiva];

        //-- Carrega os centros de custos e os respectivos percentuais de rateio
        $percentuais    = DB::select("  SELECT  Percentual.id,
                                                Percentual.idTabela,
                                                Percentual.idEmpresa,
                                                Percentual.idCentroCusto,
                                                Percentual.percentual,
                                                CentroCusto.descricaoCentroCusto,
                                                Empresa.nomeAlternativo
                                        FROM GAMA..G3_gerencialTabelaRateioPercentual   Percentual  (nolock)
                                        JOIN GAMA..G3_gerencialCentroCusto              CentroCusto (nolock) ON CentroCusto.id  = Percentual.idCentroCusto
                                        JOIN GAMA..G3_gerencialEmpresas                 Empresa     (nolock) ON Empresa.id      = Percentual.idEmpresa
                                        WHERE Percentual.idTabela   = $id");

        $hideValue  = [];
        foreach ($percentuais as $row => $data) {
            $hideValue[$data->idEmpresa][$data->idCentroCusto] = $data->percentual;
        }
        
        $hideValue = json_encode($hideValue);
    }
@endphp



<div class="container">
    <input type="hidden" name="centroCustoPerc" id="centroCustoPerc" value="{{$hideValue}}">

    <div class="form-row">
        <div class="col-xs-12 col-sm-3 col-md-3 form-group text-right">
                <label for="descricao">{{$tabelaRateio->columnAlias['descricao']}}</label>
        </div>
        <div class="col-xs-12 col-sm-9 col-md-9 form-group">
                <input type="text" 
                name="descricao" 
                class="form-control" 
                id="descricao" 
                placeholder="{{$tabelaRateio->columnAlias['descricao']}}"
                value="{{($values['descricao'] ?? '')}}">
        </div>
    </div>

    {{-- <div class="form-row">
        <div class="col-xs-12 col-sm-3 col-md-3 form-group text-right">
            <label for="idEmpresa">{{$tabelaRateio->columnAlias['idEmpresa']}}</label>
        </div>
        <div class="col-xs-12 col-sm-9 col-md-9 form-group">
                @php
                        $formOptions = $model->fk_gerencialEmpresas('id');                                    
                @endphp
                
                <select name="idEmpresa" id="idEmpresa" class="form-control @error('idEmpresa') form-validate @enderror">
                        @foreach ( $formOptions['options'] as $key => $options)
                                <option value="{{$options[0]}}"
                                                {{($values['id'] ?? '') == $options[0] ? 'selected' : ''}}>
                                        {{$options[1]}}</option>
                        @endforeach
                </select>
        </div>
    </div> --}}

    <div class="form-row">
        <div class="col-xs-12 col-sm-3 col-md-3 form-group text-right">
                <label for="tabelaAtiva">{{$tabelaRateio->columnAlias['tabelaAtiva']}}</label>
        </div>
        <div class="col-xs-12 col-sm-9 col-md-9 form-group">
                <input type="radio" name="tabelaAtiva" id="tabelaAtiva_S" value="S"
                        {{($values['tabelaAtiva'] ?? '') == 'S' ? 'checked' : ''}} > 
                        <label for="tabelaAtiva_S">Sim</label>
                <input type="radio" name="tabelaAtiva" id="tabelaAtiva_N" value="N" class="ml-3"
                        {{($values['tabelaAtiva'] ?? '') == 'N' ? 'checked' : ''}} > 
                        <label for="tabelaAtiva_N">Não</label>
        </div>
    </div>

    {{-- CENTROS DE CUSTO --}}
    <!-- Cabeçalho -->
    <div class="row">
        <div class="col-4">EMPRESA</div>
        <div class="col-4">CENTRO DE CUSTO</div>
        <div class="col-3">PERCENTUAL</div>
        <div class="col-1 pl-0">INCLUIR / ALTERAR</div>
    </div>

    <!-- Formulário -->
    <div class="form-row border-bottom border-secondary">
        <div class="col-4 form-group text-right">
            @php
                    $formOptions = $model->fk_gerencialEmpresas('id');                                    
            @endphp
            
            <select name="add-idEmpresa" id="add-idEmpresa" class="form-control @error('idEmpresa') form-validate @enderror">
                    @foreach ( $formOptions['options'] as $key => $options)
                            <option value="{{$options[0]}}"
                                            {{($values['id'] ?? '') == $options[0] ? 'selected' : ''}}>
                                    {{$options[1]}}</option>
                    @endforeach
            </select>
        </div>

        <div class="col-4 form-group text-right">
            @php
                $listaCentroCusto = $centroCusto->where('centroCustoAtivo', 'S')->orderBy('descricaoCentroCusto')->get();
            @endphp

            <select name="add-idCentroCusto" id="add-idCentroCusto" class="form-control @error('idCentroCusto') form-validate @enderror">
                @foreach ( $listaCentroCusto as $row => $dbData)
                        <option value="{{$dbData->id}}"
                                        {{($values['id'] ?? '') == $dbData->id ? 'selected' : ''}}>
                                {{$dbData->descricaoCentroCusto}}</option>
                @endforeach
            </select>

        </div>
        <div class="col-3 form-group text-right">
            <input type="number" 
                    name='add-percentual' 
                    id="add-percentual" 
                    class="form-control"
                    min='0.00' 
                    max='100.00' 
                    placeholder="Percentual Rateio" 
                    value="{{$value['percentual'] ?? ''}}">

        </div>
        <div class="col-1">
            <button type="button" class="btn btn-secondary btn-sm" id="addPercent"> <span class="fa fa-plus fa-lg"></span> | <span class="fa fa-edit fa-lg"></span> </button>
        </div>
    </div>

    <!-- Lista de Centros de Custo adicionados -->
    <div id="listaCC" class="pt-3">

        @forelse ($percentuais as $row => $data)
            <div class="row border-bottom border-light pt-2" id="PERC_{{$data->idEmpresa}}_{{$data->idCentroCusto}}">
                <div class="col-4">{{$data->idEmpresa}} - {{$data->nomeAlternativo}}</div>
                <div class="col-4">{{$data->idCentroCusto}} - {{$data->descricaoCentroCusto}}</div>
                <div class="col-3 text-right">{{number_format($data->percentual,2,',','.')}}%</div>
                <div class="col-1">
                    <button type="button" 
                            class="btn btn-danger btn-sm" 
                            onClick="delPercent('{{$data->idEmpresa}}_{{$data->idCentroCusto}}', {{$data->id}})"> 
                        <span class="fa fa-trash fa-lg"></span>
                    </button>
                </div>
            </div>
        @empty
            <div class="nulo">Nenhum centro de custo cadastrado!</div>
        @endforelse

<!--
        <div class="row border-bottom border-light pt-2">
            <div class="col-5">ADMINISTRAÇÃO CENTRAL</div>
            <div class="col-5">35,05%</div>
            <div class="col-2">
                <button type="button" class="btn btn-danger btn-sm" id="delPercent"> <span class="fa fa-trash fa-lg"></span> </button>
            </div>
        </div>

        <div class="row border-bottom border-light pt-2">
            <div class="col-5">VEÍCULOS NOVOS</div>
            <div class="col-5">35,05%</div>
            <div class="col-2">
                <button type="button" class="btn btn-danger btn-sm" id="delPercent"> <span class="fa fa-trash fa-lg"></span> </button>
            </div>
        </div>
-->

    </div>


</div>

@stack('scripts')