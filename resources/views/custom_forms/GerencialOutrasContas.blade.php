@inject('DB', 'Illuminate\Support\Facades\DB')
@php
        $values = [];
        if (isset($id) && !empty($id)) {
                $dbData = DB::select('SELECT id, codigoEmpresaERP, codigoContaContabilERP, percentualSaldo, destino, historicoPadrao, outrasContasAtivo
                                        FROM   GAMA..G3_gerencialOutrasContas   (nolock)
                                        WHERE  id = '.$id);
                $editData = $dbData[0];

                $destino = json_decode($editData->destino);

                $values  = ['codigoEmpresaERP'          => $editData->codigoEmpresaERP,
                        'codigoContaContabilERP'    => $editData->codigoContaContabilERP,
                        'percentualSaldo'           => $editData->percentualSaldo,
                        'empresaDestino'            => $destino->empresaDestino,
                        'centroCustoDestino'        => $destino->centroCustoDestino,
                        'proporcaoDestino'          => $destino->proporcaoDestino,
                        'historicoPadrao'           => $editData->historicoPadrao,
                        'outrasContasAtivo'         => $editData->outrasContasAtivo];
        }
@endphp

<div class="container">
        <div class="row">
                <div class="col-xs-12 col-sm-6 col-md-6">
                        <h4 class="text-center">ORIGEM</h4>
                        <div class="form-row">
                                <div class="col-xs-12 col-sm-3 col-md-3 form-group">
                                        <label for="empresaOrigem">Empresa Origem</label>
                                </div>
                                <div class="col-xs-12 col-sm-9 col-md-9 form-group">
                                        @php
                                                $formOptions = $model->fk_gerencialEmpresas('id');                                    
                                        @endphp
                                        
                                        <select name="codigoEmpresaERP" id="codigoEmpresaERP" class="form-control @error('codigoEmpresaERP') form-validate @enderror">
                                                @foreach ( $formOptions['options'] as $key => $options)
                                                        <option value="{{$options[0]}}"
                                                                        {{($values['codigoEmpresaERP'] ?? '') == $options[0] ? 'selected' : ''}}>
                                                                {{$options[1]}}</option>
                                                @endforeach
                                        </select>
                                </div>
                        </div>

                        <div class="row">
                                <div class="col-xs-12 col-sm-3 col-md-3 form-group">
                                        <label for="codigoContaContabilERP">Conta Contabil</label>
                                </div>
                                <div class="col-xs-12 col-sm-9 col-md-9 form-group">
                                        @php
                                                //$formOptions = $model->fk_gerencialContaContabil('id');
                                                $formOptions = $model->custom_codigoContaContabilERP();
                                        @endphp

                                        <select name="codigoContaContabilERP" id="codigoContaContabilERP" class="form-control @error('codigContaContabilERP') form-validate @enderror">
                                                @foreach ( $formOptions['options'] as $key => $options)
                                                        <option value="{{$options[0]}}"
                                                                        {{($values['codigoContaContabilERP'] ?? '') == $options[0] ? 'selected' : ''}}>
                                                                {{$options[1]}}</option>
                                                @endforeach
                                        </select>
                                </div>
                        </div>

                        <div class="form-row">
                                <div class="col-xs-12 col-sm-3 col-md-3 form-group">
                                        <label for="percentualSaldo">% do Saldo Contabil</label>
                                </div>
                                <div class="col-xs-12 col-sm-9 col-md-9 form-group">
                                        <input type="number" 
                                        name="percentualSaldo" 
                                        class="form-control" 
                                        id="percentualSaldo" 
                                        placeholder="% do Saldo Contabil"
                                        min="0"
                                        max="100"
                                        value="{{($values['percentualSaldo'] ?? '')}}">
                                </div>
                        </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-6">
                        <h4 class="text-center">DESTINO</h4>
                        <div class="row">
                                <div class="col-xs-12 col-sm-3 col-md-3 form-group">
                                        <label for="idEmpresa">Empresa Destino</label>
                                </div>
                                <div class="col-xs-12 col-sm-9 col-md-9 form-group">
                                        @php
                                                $formOptions = $model->fk_gerencialEmpresas('id');
                                        @endphp

                                        <select name="empresaDestino" id="empresaDestino" class="form-control @error('empresaDestino') form-validate @enderror">
                                                @foreach ( $formOptions['options'] as $key => $options)
                                                        <option value="{{$options[0]}}"
                                                                        {{($values['empresaDestino'] ?? '') == $options[0] ? 'selected' : ''}}>
                                                                {{$options[1]}}</option>
                                                @endforeach
                                        </select>
                                </div>
                        </div>

                        <div class="row">
                                <div class="col-xs-12 col-sm-3 col-md-3 form-group">
                                        <label for="centroCusto">Centro de Custo</label>
                                </div>
                                <div class="col-xs-12 col-sm-9 col-md-9 form-group">
                                        @php
                                                $formOptions = $model->fk_gerencialCentroCusto('id');
                                        @endphp

                                        <select name="centroCustoDestino" id="centroCustoDestino" class="form-control @error('centroCustoDestino') form-validate @enderror">
                                                @foreach ( $formOptions['options'] as $key => $options)
                                                        <option value="{{$options[0]}}"
                                                                        {{($values['centroCustoDestino'] ?? '') == $options[0] ? 'selected' : ''}}>
                                                                {{$options[1]}}</option>
                                                @endforeach
                                        </select>
                                </div>
                        </div>
                        
                        <div class="form-row">
                                <div class="col-xs-12 col-sm-3 col-md-3 form-group">
                                        <label for="proporcaoDestino">% do Saldo Contabil</label>
                                </div>
                                <div class="col-xs-12 col-sm-9 col-md-9 form-group">
                                        <input type="number" 
                                        name="proporcaoDestino" 
                                        class="form-control" 
                                        id="proporcaoDestino" 
                                        placeholder="% do Saldo Contabil para o Centro de Custo"
                                        min="0"
                                        max="100"
                                        value="{{($values['proporcaoDestino'] ?? '')}}">
                                </div>
                        </div>
                </div>
        </div>

        <div class="row">
                <div class="col-xs-12 col-sm-3 col-md-3 form-group text-right">
                        <label for="historicoPadrao">Histórico Padrão</label>
                </div>
                <div class="col-xs-12 col-sm-9 col-md-9 form-group">
                        <textarea name="historicoPadrao" 
                                  id="historicoPadrao" 
                                  class="form-control" 
                                  cols="30"
                                  rows="5"
                                  placeholder="Histórico Padrão">{{($values['historicoPadrao'] ?? '')}}</textarea>
                </div>
        </div>

        <div class="row">
                <div class="col-xs-12 col-sm-3 col-md-3 form-group text-right">
                        <label for="outrasContasAtivo">Ativo</label>
                </div>
                <div class="col-xs-12 col-sm-9 col-md-9 form-group">
                        <input type="radio" name="outrasContasAtivo" id="outrasContasAtivo_S" value="S"
                                {{($values['outrasContasAtivo'] ?? '') == 'S' ? 'checked' : ''}} > 
                                <label for="outrasContasAtivo_S">Sim</label>
                        <input type="radio" name="outrasContasAtivo" id="outrasContasAtivo_N" value="N" 
                                {{($values['outrasContasAtivo'] ?? '') == 'N' ? 'checked' : ''}} > 
                                <label for="outrasContasAtivo_N">Não</label>
                </div>
        </div>
</div>