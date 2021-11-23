@include('relatorios.gerencial.filtroGerencial')

@include('relatorios.gerencial.gerencialToolBar')

<div id="report-area">
        <div class="container-fluid">
        <!--// REPORT HEADER -->
        <section class="header-report">
                <div class="row">
                        <div class="col-sm12-col-xs-3 col-sm-3">
                                @if ($configLoaded->reportHeader->showLogo)
                                        <img src="{{$configLoaded->reportHeader->logoReport}}" 
                                        class="report-logo" 
                                        width="{{$configLoaded->reportHeader->logoMaxSize}}">
                                @endif
                        </div>
                        <div class="col-sm12-col-xs-6 col-sm-6 text-center">
                                <h3>{{$configLoaded->reportHeader->title}} - {{$configReport->periodo}}</h3>
                                <h5>{{$configLoaded->reportHeader->subTitle}}</h5>
                        </div>
                        <div class="col-sm12-col-xs-3 col-sm-3 text-right">
                                @if ($configLoaded->reportHeader->showDateTime)
                                        {{date('d/m/Y H:i:s')}}    
                                @endif
                        </div>
                </div>
        </section>


        <!--// REPORT DATA -->
        <section class="data-area">
                @php
                $totalSubGrupo      = $totalGrupo = $totalEmpresa = 0;
                $decimals           = ($configReport->decimais ? 2 : 0);
                @endphp

                <!--// EMPRESA -->
                @foreach ($reportData[$layout] as $chaveRelatorio => $dataGrupo)
                        <div class="text-center">
                                <h4><strong>{{$chaveRelatorio}}</strong></h4>
                        </div>
                        <table class="table-report mb-3">
                                <thead>
                                        <tr>
                                                <th class="text-left" rowspan="2">CONTA GERENCIAL</th>

                                                {{-- Cabeçalho Meses --}}
                                                @for ($coluna = $mesInicio; $coluna <= $mesFinal; $coluna ++)
                                                <th colspan='3'>
                                                        @switch($coluna)
                                                        @case(1) JAN @break
                                                        @case(2) FEV @break
                                                        @case(3) MAR @break
                                                        @case(4) ABR @break
                                                        @case(5) MAI @break
                                                        @case(6) JUN @break
                                                        @case(7) JUL @break
                                                        @case(8) AGO @break
                                                        @case(9) SET @break
                                                        @case(10) OUT @break
                                                        @case(11) NOV @break
                                                        @case(12) DEZ @break
                                                        @default
                                                        @endswitch
                                                        /{{$ano}}
                                                </th>

                                                @php
                                                        // INICIALIZA OS TOTAIS DE CADA CENTRO DE CUSTO
                                                        $totaisSubGrupo[$coluna]  = NULL;
                                                        $totaisGrupo[$coluna]     = NULL;
                                                        $totaisEmpresa[$coluna]   = NULL;
                                                @endphp

                                                @endfor
                                                <th rowspan="2">TOTAL</th>
                                        </tr>
                                        <tr>
                                                @for ($coluna = $mesInicio; $coluna <= $mesFinal; $coluna ++)
                                                        <th class="text-center">SALDO</th>
                                                        <th class="text-center">HR (%)</th>
                                                        <th class="text-center">VR (%)</th>
                                                @endfor

                                        </tr>
                                </thead>
                                
                                <tbody>

                                <!--// SUB-GRUPO DE CONTA -->
                                @foreach ($dataGrupo as $subGrupo => $dataSubGrupo)
                                        <!--// GRUPO DE CONTA -->
                                        @foreach ($dataSubGrupo as $grupo => $dataCodigo)
                                                <!--// CÓDIGO E DESCRICAO DA CONTA -->
                                                @foreach ($dataCodigo as $codigoConta => $dataConta)
                                                        <!--//Linha de dados//-->
                                                        @php    $totalLinha    = 0;    @endphp
                                                        
                                                        <tr class="row-data {{isset($infoConta[rtrim($codigoConta)]) ? 'moreinfo' : ''}}" data-explode="{{$dataConta['jsonData']}}">    <!-- Código e Descrição da Conta -->
                                                                <td class="account" data-toggle="tooltip"
                                                                                data-placement="left"
                                                                                data-html="true"
                                                                                title="{{$infoConta[rtrim($codigoConta)] ?? '' }}">{{$codigoConta}}
                                                                </td>

                                                                <!--// ANÁLISE VERTICAL (EMPRESA x CENTROS DE CUSTO) -->
                                                                @for ($coluna = $mesInicio; $coluna <= $mesFinal; $coluna ++)
                                                                        <td class="values">
                                                                                @if (isset($dataConta[$coluna]['valor']))
                                                                                        {{number_format($dataConta[$coluna]['valor'], $decimals,',','.')}}
                                                        
                                                                                        @php
                                                                                        // ACUMULA OS TOTAIS
                                                                                        $totaisSubGrupo[$coluna]      += $dataConta[$coluna]['valor'];
                                                                                        $totaisGrupo[$coluna]         += $dataConta[$coluna]['valor'];
                                                                                        $totaisEmpresa[$coluna]       += $dataConta[$coluna]['valor'];

                                                                                        $totalLinha                   += $dataConta[$coluna]['valor'];
                                                                                        @endphp
                                                                                @endif
                                                                        </td>
                                                                        <td class="values HRValues">
                                                                                @if (isset($dataConta[$coluna]['horizontal']))
                                                                                        {{number_format($dataConta[$coluna]['horizontal'], 2,',','.')}}
                                                                                @endif
                                                                        </td>
                                                                        <td class="values VRValues">
                                                                                @if (isset($dataConta[$coluna]['vertical']))
                                                                                        {{number_format($dataConta[$coluna]['vertical'], 2,',','.')}}
                                                                                @endif
                                                                        </td>

                                                                @endfor
                                                        

                                                                <td class="total-col values">
                                                                        {{number_format($totalLinha, $decimals,',','.')}}
                                                                        
                                                                        @php
                                                                        // ACUMULA O TOTAL GERAL
                                                                        $totalSubGrupo      = $totalGrupo   =       $totalEmpresa       += $totalLinha;
                                                                        @endphp
                                                                </td>

                                                        </tr> <!--// fim da linha de dados //-->
                                                @endforeach <!--// CONTA -->

                                                <!--// EXIBE OS TOTAIS DO GRUPO -->
                                                <tr class="row-totals ts2">
                                                        <td class="account">{{$grupo}}</td>
                                                
                                                                @for ($coluna = $mesInicio; $coluna <= $mesFinal; $coluna ++)

                                                                        <td class="values">
                                                                                @if (isset($totaisGrupo[$coluna]))
                                                                                        {{number_format($totaisGrupo[$coluna], $decimals,',','.')}}
                                                                                @endif

                                                                                @php
                                                                                        //INICIALIZA OS TOTALIZADORES
                                                                                        $totaisGrupo[$coluna] = NULL;
                                                                                @endphp
                                                                        </td>

                                                                        <td class="values HRvalues">

                                                                        </td>

                                                                        <td class="values VRvalues">

                                                                        </td>
                                                                @endfor
                                                        
                                                        <td class="total-col values">
                                                                {{number_format($totalGrupo, $decimals,',','.')}}

                                                                @php
                                                                $totalGrupo = NULL;
                                                                @endphp
                                                        </td>
                                                </tr>
                                                
                                        @endforeach <!--// GRUPO -->

                                        <!--// EXIBE OS TOTAIS DO SUB-GRUPO -->
                                        <tr class="row-totals ts2">
                                                <td class="account">{{$subGrupo}}</td>
                                                        @for ($coluna = $mesInicio; $coluna <= $mesFinal; $coluna ++)
                                                                <td class="values">
                                                                        @if (isset($totaisSubGrupo[$coluna]))
                                                                                {{number_format($totaisSubGrupo[$coluna], $decimals,',','.')}}
                                                                        @endif

                                                                        @php
                                                                                //INICIALIZA OS TOTALIZADORES
                                                                                $totaisSubGrupo[$coluna] = NULL;
                                                                        @endphp
                                                                </td>

                                                                <td class="values HRvalues">

                                                                </td>
                                                                
                                                                <td class="values VRvalues">

                                                                </td>
                                                        @endfor

                                                <td class="total-col values">
                                                        {{number_format($totalSubGrupo, $decimals,',','.')}}

                                                        @php
                                                        $totalSubGrupo = NULL;
                                                        @endphp
                                                </td>
                                        </tr>

                                @endforeach <!--// SUB-GRUPO -->

                                        <!--// RESULTADO LÍQUIDO EXIBE OS TOTAIS DA EMPRESA -->
                                        <tr class="row-totals ts3 tw-8">
                                                <td class="pl-2">RESULTADO LÍQUIDO</td>
                                                <!--<td>{{$chaveRelatorio}}</td> -->
                                                
                                                        @for ($coluna = $mesInicio; $coluna <= $mesFinal; $coluna ++)
                                                                <td class="values">
                                                                        @if (isset($totaisEmpresa[$coluna]))
                                                                                {{number_format($totaisEmpresa[$coluna], $decimals,',','.')}}
                                                                        @endif
                                                                </td>

                                                                <td class="values HRvalues">

                                                                </td>
                                                                
                                                                <td class="values VRvalues">

                                                                </td>
                                                        @endfor

                                                <td class="total-col values">
                                                        {{number_format($totalEmpresa, $decimals,',','.')}}
                                                </td>
                                        </tr>

                                        <!--// MARGEM BRUTA -->
                                        <tr class="border-0">
                                                <td class=" border-0 pt-3" colspan="{{($coluna * 3)}}"></td>
                                        </tr>
                                        <tr class="row-totals ts3 tw-8">
                                                <td class="pl-2">MARGEM BRUTA</td>

                                                @for ($coluna = $mesInicio; $coluna <= $mesFinal; $coluna ++)
                                                        <td class="values">
                                                                @if (isset($margemBrutaVertical[$chaveRelatorio][$coluna]))
                                                                        {{number_format($margemBrutaVertical[$chaveRelatorio][$coluna], $decimals,',','.')}}%
                                                                @endif
                                                        </td>

                                                        <td class="values HRvalues">

                                                        </td>
                                                        <td class="values VRvalues">

                                                        </td>
                                                @endfor

                                                @php
                                                        if (isset($receitaTotalVertical[$chaveRelatorio]) && $receitaTotalVertical[$chaveRelatorio] > 0) {
                                                                $margemTotal        = ($margemTotalVertical[$chaveRelatorio]/ ($receitaTotalVertical[$chaveRelatorio] ?? 1))*100;
                                                        }
                                                        else $margemTotal       = 0;
                                                @endphp

                                                <td class="total-col values">
                                                        {{number_format($margemTotal, $decimals,',','.')}}%
                                                </td>
                                        </tr>
                                        <!--// MARGEM BRUTA -->

                                        <!--// MARGEM LÍQUIDA -->
                                        <tr class="row-totals ts3 tw-8">
                                                <td class="pl-2">MARGEM LÍQUIDA</td>
                                        
                                                @php    $receitaEmpresa = 0;    @endphp
                                                @for ($coluna = $mesInicio; $coluna <= $mesFinal; $coluna ++)
                                                        <td class="values">
                                                                @php
                                                                        if (isset($receitaVertical[$chaveRelatorio][$coluna]) &&
                                                                                $receitaVertical[$chaveRelatorio][$coluna] <> 0) {
                                                                                echo number_format(($totaisEmpresa[$coluna] / ($receitaVertical[$chaveRelatorio][$coluna] ?? 1)) * 100, $decimals,',','.').'%';
                                                                                $receitaEmpresa += $receitaVertical[$chaveRelatorio][$coluna];
                                                                        }
                                                                @endphp
                                                        </td>
                                                        <td class="values HRvalues">

                                                        </td>
                                                        <td class="values VRvalues">

                                                        </td>
                                                @endfor

                                                @php
                                                        $mlTotal                 = ($receitaEmpresa <> 0 ? ($totalEmpresa / $receitaEmpresa)*100 : 0);
                                                        $totaisEmpresa[$coluna] = NULL;
                                                @endphp

                                                <td class="total-col values">
                                                        {{number_format($mlTotal, $decimals,',','.')}}%
                                                </td>
                                        </tr>
                                        <!--// MARGEM LÍQUIDA -->

                                        <!--// VALOR ABSOLUTO -->
                                        <tr class="row-totals ts3 tw-8">
                                        <td class="pl-2">MARGEM LÍQUIDA <small>(VALOR ABSOLUTO/mil)</small></td>
                                                                        
                                                @for ($coluna = $mesInicio; $coluna <= $mesFinal; $coluna ++)
                                                        <td class="values">
                                                                {{intval($totaisEmpresa[$coluna]/1000)}}
                                                        </td>

                                                        <td class="values HRvalues"></td>
                                                        <td class="values VRvalues"></td>
                                                @endfor

                                                <td class="total-col values">
                                                        {{intval($totalEmpresa/1000)}}
                                                </td>
                                        </tr>
                                        <!--// VALOR ABSOLUTO -->
                                </tbody>
                        </table>
                        @php
                        $totalEmpresa = NULL;
                        @endphp
                @endforeach <!--// COMPARATIVO MENSAL -->
        </section>

        </div>
</div>
