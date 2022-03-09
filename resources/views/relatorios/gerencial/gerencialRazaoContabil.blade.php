<style>
    .table-responsive   { font-size: .9rem;}
    .tw-8               { font-weight: 800;}
</style>

<h4>{{$reportData[0]->idContaContabil}} - {{$reportData[0]->descricaoContaContabil}}</h4>
<h4>{{$reportData[0]->siglaCentroCusto}} - {{$reportData[0]->centroCusto}}</h4>

<div class="table-responsive">
    <table class="table table-sm table-hover">
        <thead class="thead-dark">
                <tr>
                    <th>Lançamento</th>
                    <th>C/D</th>
                    <th>Data</th>
                    <th>Documento</th>
                    <th>Observação</th>
                    <th>Valor</th>
            </tr>
        </thead>
        
        <tbody>
            @php
                $total = 0;
            @endphp
            @foreach ($reportData as $data) 

                <tr>
                    <td class="text-center">{{$data->codigoLancamento}}</td>
                    <td clas="text-center">{{$data->naturezaLancamento}}</td>
                    <td clas="text-center">{{$data->dataLancamento}}</td>
                    <td clas="text-right">{{$data->documentoLancamento}}</td>
                    <td>{{$data->observacaoLancamento}}</td>
                    <td class="text-right">
                        {{number_format($data->valorLancamento,2,',','.')}}
                    </td>
                </tr>

                @php
                    // Total
                    $total  += $data->valorLancamento;
                @endphp
            @endforeach

            {{-- TOTAL DOS LANÇAMENTOS --}}
            <tr class="border-top border-2 text-right tw-8">
                <td colspan="5">Total dos Lancamentos</td>
                <td class="text-right">{{number_format($total,2,',','.')}}</td>
            </tr>
        </tbody>
    </table>
</div>
