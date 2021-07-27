<style>
    .table-responsive   { font-size: .9rem;}
    .tw-8               { font-weight: 800;}
</style>

@inject('planoConta',       'App\Models\GerencialContaContabil')
@inject('tipoLancamento',   'App\Models\GerencialTipoLancamento')


<!-- <h2 class="text-center">DETALHAMENTO DE CONTA GERENCIAL</h2> -->

<h3>CONTA GERENCIAL: {{$reportData['numeroContaGerencial']}} - {{$reportData['nomeConta']}}</h3>
<h3>PERÍODO: {{$reportData['mesAno']}}</h3>

<div class="table-responsive">
    <table class="table table-sm table-hover">
        <thead class="thead-dark">
                <tr>
                    <th>Natureza</th>
                    <th colspan="2">Conta Contábil</th>
                    <th>Valor Lançamento</th>
                    <th>Tipo de Lançamento</th>
            </tr>
        </thead>
        
        <tbody>
            @php
                $lastRegional   = NULL;
                $lastEmpresa    = NULL;
                $lastCCusto     = NULL;

                $totalCCusto    = 0;
                $totalEmpresa   = 0;
                $totalRegional  = 0;
            @endphp

            @foreach ($reportData['dataDetalhe'] as $data) 
                {{-- QUEBRA POR REGIONAL --}}
                @if ($data->nomeRegional != $lastRegional) 
                    {{-- Exibe o valor total da regional --}}
                    @if (!empty($lastRegional))
                        <tr class="border-top border-2 text-right tw-8">
                            <td colspan="3"> Total Regional</td>
                            <td>
                                {{number_format($totalRegional,2,',','.')}}
                            </td>
                            <td></td>
                        </tr>
                        @php    $totalRegional    = 0; @endphp
                    @endif
                    <tr>
                        <td colspan="5" class="tw-8">{{$data->nomeRegional}}</td>
                    </tr>
                    @php    $lastRegional   = $data->nomeRegional;  @endphp
                @endif

                {{-- QUEBRA POR EMPRESA --}}
                @if ($data->codigoEmpresa != $lastEmpresa) 
                    {{-- Exibe o valor total da empresa --}}
                    @if (!empty($lastEmpresa))
                        <tr class="border-top border-2 text-right tw-8">
                            <td colspan="3">Total Empresa</td>
                            <td>
                                {{number_format($totalEmpresa,2,',','.')}}
                            </td>
                            <td></td>
                        </tr>
                        @php    $totalEmpresa    = 0; @endphp
                    @endif

                    <tr>
                        <td colspan="5" class="tw-8">{{$data->nomeEmpresa}}</td>
                    </tr>
                    @php    $lastEmpresa    = $data->codigoEmpresa; @endphp
                @endif

                {{-- QUEBRA POR CENTRO DE CUSTO --}}
                @if ($data->codigoCentroCusto != $lastCCusto) 
                    {{-- Exibe o valor total do centro de custo --}}
                    @if (!empty($lastCCusto))
                        <tr class="border-top border-2 text-right tw-8">
                            <td colspan="3">Total Centro de Custo</td>
                            <td>
                                {{number_format($totalCCusto,2,',','.')}}
                            </td>
                            <td></td>
                        </tr>
                        @php    $totalCCusto    = 0; @endphp
                    @endif
                    
                    <tr>
                        <td colspan="5" class="tw-8">{{$data->siglaCentroCusto}} - {{$data->centroCusto}}</td>
                    </tr>
                    @php    $lastCCusto     = $data->codigoCentroCusto; @endphp
                @endif

                <tr data-toggle="tooltip" title="{{$data->historico}}">
                    <td class="text-center">
                        @if ($data->valorLancamento > 0)    CRD
                        @else                               DEB
                        @endif
                    </td>

                    {{-- Identifica a Conta Contábil --}}
                    @php
                        if (!empty($data->codigoContaContabilERP)) {
                            $contaContabil = $planoConta->getContaContabil($data->codigoContaContabilERP);
                        }
                    @endphp
                    
                    <td colspan="2">{{$contaContabil->PlanoConta_ID ?? ""}} - {{$contaContabil->PlanoConta_Descricao ?? ""}}</td>

                    <td class="text-right">
                        {{number_format($data->valorLancamento,2,',','.')}}
                    </td>

                    {{-- Identifica o tipo de lancamento --}}
                    @php
                        $tipo = $tipoLancamento->getTipoLancamento($data->tipoLancamento);
                    @endphp
                    <td>{{$tipo->descricaoTipoLancamento}}</td>
                </tr>

                @php
                    // Totais
                    $totalRegional  += $data->valorLancamento;
                    $totalEmpresa   += $data->valorLancamento;
                    $totalCCusto    += $data->valorLancamento;
                @endphp
            @endforeach

            {{-- TOTAL DO ÚLTIMO CENTRO DE CUSTO --}}
            <tr class="border-top border-2 text-right tw-8">
                <td colspan="3">Total Centro de Custo</td>
                <td>
                    {{number_format($totalCCusto,2,',','.')}}
                </td>
                <td></td>
            </tr>

            {{-- TOTAL DA ÚLTIMA EMPRESA --}}
            <tr class="border-top border-2 text-right tw-8">
                <td colspan="3">Total Empresa</td>
                <td>
                    {{number_format($totalEmpresa,2,',','.')}}
                </td>
                <td></td>
            </tr>

            {{-- TOTAL DA ÚLTIMA REGIONAL --}}
            <tr class="border-top border-2">
                <td colspan="3" class="tw-8 text-right">Total Regional</td>
                <td class="text-right tw-8">
                    {{number_format($totalRegional,2,',','.')}}
                </td>
                <td></td>
            </tr>

        </tbody>
    </table>
</div>