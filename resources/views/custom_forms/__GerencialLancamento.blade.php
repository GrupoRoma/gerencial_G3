<div class="form-row">
        <div class="col-xs-12 col-sm-6 col-md-6 form-group">
                <label for="anoReferencia">Ano de Referência</label>
                <input type="number" name="anoReferencia" class="form-control" id="anoReferencia" placeholder="Ano de Referência" min="2020" max="2099" maxlength="4">
        </div>
        <div class="col-xs-12 col-sm-6 col-md-6 form-group">
                <label for="mesReferencia">Mês de Referência</label>
                <input type="month" name="mesReferencia" class="form-control" id="mesReferencia" placeholder="Mês de Referência" min="1" max="12" maxlength="2">
        </div>
</div>

<div class="form-group">
<!--        <label for="idEmpresa">Empresa</label>-->
        @php
                $formOptions = $model->fk_gerencialEmpresas('id');
        @endphp

        <select name="idEmpresa" id="idEmpresa" class="form-control @error('idEmpresa') form-validate @enderror">
                @foreach ( $formOptions['options'] as $key => $options)
                        <option value="{{$options[0]}}">{{$options[1]}}</option>
                @endforeach
        </select>

<div class="form-row">
        <div class="col-xs-12 col-sm-6 col-md-6 form-group">
                <label for="idContaGerencial">Conta Gerencial</label>
                @php
                        $formOptions = $model->fk_gerencialContaGerencial('id');
                @endphp

                <select name="idContaGerencial" id="idContaGerencial" class="form-control @error('idcontaGerencial') form-validate @enderror">
                        @foreach ( $formOptions['options'] as $key => $options)
                                <option value="{{$options[0]}}">{{$options[1]}}</option>
                        @endforeach
                </select>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-6 form-group">
                <label for="centroCusto">Centro de Custo</label>
                <input type="number" name="centroCusto" class="form-control" id="centroCusto" placeholder="Centro de Custo">
        </div>
</div>

<div class="form-row">
        <div class="col-xs-12 col-sm-6 col-md-6 form-group">
                <input type="radio" name="creditoDebito" id="credito" value="CRD"> <label for="credito">Crédito</label>
                <input type="radio" name="creditoDebito" id="Debito" value="DEB">  <label for="debito">Débito</label>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-6 form-group">
                <label for="contaContabil">Conta Contábil</label>
                <input type="text" class="form-control" id="contaContabil" placeholder="Número da Conta Contábil">
        </div>
</div>

<div class="form-row">
        <div class="col-xs-12 col-sm-6 col-md-6 form-group">
                <label for="valorLancamento">Valor</label>
                <input type="number" class="form-control" id="valorLancamento" placeholder="Valor do Lançamento" min="-999999999.99" max="999999999.99" step="0.05">
        </div>
        <div class="col-xs-12 col-sm-6 col-md-6 form-group">
                <label for="numeroDocumento">Número do Documento</label>
                <input type="text" class="form-control" id="numeroDocumento" placeholder="Número do Documento">
        </div>
</div>

<div class="col-xs-12 col-sm-6 col-md-6 form-group">
        <label for="historicoLancamento">Histórico</label>
        <textarea name="historicoLancamento" id="historicoLancamento" class="form-control" rows="5"></textarea>
</div>