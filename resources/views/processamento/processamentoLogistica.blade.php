    <div class="card mr-auto filtro-relatorio">
        <div class="card-header text-white text-center"><h3>PROCESSAMENTO DOS PARÂMETROS DA LOGÍSTICA</h3></div>
        <div class="card-body bg-light text-dark">
            Regras para o rateio da logística

            <ol>
                <li>Apura o resultado líquido do Grupo no período <br><small class="text-muted">(RL - RESULTADO LÍQUIDO TOTAL)</small></li>
                <li>Apura o total de veículos vendidos no período deduzido das devoluções de vendas de períodos anteriores <br><small class="text-muted">(VV -VEÍCULOS VENDIDOS NO PERÍODO)</small></li>
                <li>Calcula o resultado líquido por veículo vendido <br><small class="text-muted">(RV - RESULTADO LÍQUIDO POR VEÍCULO VENDIDO = RL / VV )</small></li>
                <li>Calcula o valor do rateio por empresa de acordo com o volume de vendas de cada uma <br><small class="text-muted">(VR - VALOR RATEIO = RV * VV)</small></li>
            </ol>
        </div>
        <div class="card-footer text-danger text-center">
            <button class="btn btn-orange" data-nav="{{route('processarRateioLogistica')}}">CONFIRMAR PROCESSAMENTO</button>
        </div>
    </div>
