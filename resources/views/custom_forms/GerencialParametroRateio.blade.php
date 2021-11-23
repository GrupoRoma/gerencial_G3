@php
$values = ['descricaoParametro', 'idBaseCalculo', 'idTabelaRateio', 'idTipoLancamento', 'codigoEmpresaOrigem', 'codigoEmpresaDestino',
           'codigoContaGerencialOrigem', 'codigoContaGerencialDestino', 'codigoCentroCustoOrigem', 'codigoCentroCustoDestino', 
           'historicoPadrao', 'formaAplicacao', 'parametroAtivo'];
if (isset($tableData->descricaoParametro)) {
        $values = ['descricaoParametro'         => $tableData->descricaoParametro,
                   'idBaseCalculo'              => $tableData->idBaseCalculo,
                   'idTabelaRateio'             => $tableData->idTabelaRateio,
                   'idTipoLancamento'           => $tableData->idTipoLancamento,
                   'codigoEmpresaOrigem'        => $tableData->codigoEmpresaOrigem,
                   'codigoEmpresaDestino'       => $tableData->codigoEmpresaDestino,
                   'codigoContaGerencialOrigem' => $tableData->codigoContaGerencialOrigem,
                   'codigoContaGerencialDestino'=> $tableData->codigoContaGerencialDestino,
                   'codigoCentroCustoOrigem'    => $tableData->codigoCentroCustoOrigem,
                   'codigoCentroCustoDestino'   => $tableData->codigoCentroCustoDestino,
                   'historicoPadrao'            => $tableData->historicoPadrao,
                   'formaAplicacao'             => $tableData->formaAplicacao,
                   'parametroAtivo'             => $tableData->parametroAtivo
                  ];
    }
@endphp


<div class="row border-bottom border-secondary">
        <div class="col-xs-12 col-sm-6 col-md-6">
                <div class="form-row">
                        <div class="col-xs-12 col-sm-3 col-md-3 form-group">
                                <label for="descricaoParametro">Descrição</label>
                        </div>
                        <div class="col-xs-12 col-sm-9 col-md-9 form-group">
                                <input type="text" 
                                name="descricaoParametro" 
                                class="form-control" 
                                id="descricaoParametro" 
                                placeholder="Descrição"
                                value="{{($values['descricaoParametro'] ?? '')}}">
                        </div>
                </div>

                <div class="row">
                        <div class="col-xs-12 col-sm-3 col-md-3 form-group">
                                <label for="idBaseCalculo">Base de Cálculo</label>
                        </div>
                        <div class="col-xs-12 col-sm-9 col-md-9 form-group">
                                @php
                                        $formOptions = $model->fk_gerencialBaseCalculo('id');
                                @endphp

                                <select name="idBaseCalculo" id="idBaseCalculo" class="form-control @error('idBaseCalculo') form-validate @enderror">
                                        @foreach ( $formOptions['options'] as $key => $options)
                                                <option value="{{$options[0]}}"
                                                                {{($values['idBaseCalculo'] ?? '') == $options[0] ? 'selected' : ''}}>
                                                        {{$options[1]}}</option>
                                        @endforeach
                                </select>
                        </div>
                </div>

                <div class="row">
                        <div class="col-xs-12 col-sm-3 col-md-3 form-group">
                                <label for="idTipoLancamento">Tipo de Lançamento</label>
                        </div>
                        <div class="col-xs-12 col-sm-9 col-md-9 form-group">
                                @php
                                        $formOptions = $model->fk_gerencialTipoLancamento('id');
                                @endphp

                                <select name="idTipoLancamento" id="idTipoLancamento" class="form-control @error('idTipoLancamento') form-validate @enderror">
                                        @foreach ( $formOptions['options'] as $key => $options)
                                                <option value="{{$options[0]}}"
                                                                {{($values['idTipoLancamento'] ?? '') == $options[0] ? 'selected' : ''}}>
                                                        {{$options[1]}}</option>
                                        @endforeach
                                </select>
                        </div>
                </div>

                <div class="row">
                        <div class="col-xs-12 col-sm-3 col-md-3 form-group">
                                <label for="parametroAtivo">Parâmetro Ativo</label>
                        </div>
                        <div class="col-xs-12 col-sm-9 col-md-9 form-group">
                                <input type="radio" name="parametroAtivo" id="parametroAtivo_S" value="S"
                                        {{($values['parametroAtivo'] ?? '') == 'S' ? 'checked' : ''}} > 
                                        <label for="parametroAtivo_S">Sim</label>
                                <input type="radio" name="parametroAtivo" id="parametroAtivo_N" value="N"
                                        {{($values['parametroAtivo'] ?? '') == 'N' ? 'checked' : ''}} > 
                                        <label for="parametroAtivo_N">Não</label>
                        </div>
                </div>
        </div>

        <div class="col-xs-12 col-sm-6 col-md-6">
                <div class="row">
                        <div class="col-xs-12 col-sm-3 col-md-3 form-group">
                                <label for="historicoPadrao">Histórico Padrão</label>
                        </div>
                        <div class="col-xs-12 col-sm-9 col-md-9 form-group">
                                <input type="text" 
                                       name="historicoPadrao" 
                                       class="form-control" 
                                       id="historicoPadrao" 
                                       placeholder="Histórico"
                                       value="{{($values['historicoPadrao'] ?? '')}}">
                        </div>
                </div>
                
                <div class="row">
                        <div class="col-xs-12 col-sm-3 col-md-3 form-group">
                                <label for="formaAplicacao">Forma de Aplicação</label>
                        </div>
                        <div class="col-xs-12 col-sm-9 col-md-9 form-group">
                                <input type="radio" name="formaAplicacao" id="formaAplicacao_peso" value="PESO"
                                        {{($values['formaAplicacao'] ?? '') == 'PESO' ? 'checked' : ''}} 
                                        onClick="$('#idTabelaRateio').prop('disabled', true).val('');"> 
                                        <label for="formaAplicacao_peso">[PESO] Peso em relação à Base de Cálculo</label>
                                <input type="radio" name="formaAplicacao" id="formaAplicacao_tbla" value="TBLA"
                                        {{($values['formaAplicacao'] ?? '') == 'TBLA' ? 'checked' : ''}} 
                                        onClick="$('#idTabelaRateio').prop('disabled', false);"> 
                                        <label for="formaAplicacao_tbla">[TBLA] Tabela de Referência</label>
                        </div>
                </div>
                
                <div class="row">
                        <div class="col-xs-12 col-sm-3 col-md-3 form-group">
                                <label for="idTipoLancamento">Tabela de Rateio</label>
                        </div>
                        <div class="col-xs-12 col-sm-9 col-md-9 form-group">
                                @php
                                        $formOptions = $model->fk_gerencialTabelaRateio('id');
                                @endphp

                                <select name="idTabelaRateio" id="idTabelaRateio" class="form-control @error('idTabelaRateio') form-validate @enderror" disabled>
                                        <option value=""></option>
                                        @foreach ( $formOptions['options'] as $key => $options)
                                                <option value="{{$options[0]}}"
                                                                {{($values['idTabelaRateio'] ?? '') == $options[0] ? 'selected' : ''}}>
                                                        {{$options[1]}}</option>
                                        @endforeach
                                </select>
                        </div>
                </div>
        </div>
</div>


<div class="row">
        <!-- ORIGEM -->
        <div class="col-xs-12 col-sm-6 col-md-6 form-group">
                <h4> ORIGEM </h4>
                <label for="codigoEmpresaOrigem">Empresa(s)</label>
                {!!$model->custom_codigoEmpresaOrigem(($values['codigoEmpresaOrigem'] ?? ''))!!}

                <label for="codigoContaGerencialOrigem">Conta Gerencial</label>
                {!!$model->custom_codigoContaGerencialOrigem(($values['codigoContaGerencialOrigem'] ?? ''))!!}

                <label for="codigoCentroCustoOrigem">Centro(s) Custo</label>
                {!!$model->custom_codigoCentroCustoOrigem(($values['codigoCentroCustoOrigem'] ?? ''))!!}
        </div>

        <!-- DESTINO -->
        <div class="col-xs-12 col-sm-6 col-md-6 form-group">
                <h4> DESTINO </h4>
                <label for="codigoEmpresaDestino">Empresa(s)</label>
                {!!$model->custom_codigoEmpresaDestino(($values['codigoEmpresaDestino'] ?? ''))!!}

                <label for="codigoContaGerencialDestino">Conta Gerencial</label>
                {!!$model->custom_codigoContaGerencialDestino(($values['codigoContaGerencialDestino'] ?? ''))!!}

                <label for="codigoCentroCustoDestino">Centro(s) Custo</label>
                {!!$model->custom_codigoCentroCustoDestino(($values['codigoCentroCustoDestino'] ?? ''))!!}
        </div>
</div>

