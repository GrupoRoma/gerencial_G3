
<div class="container">
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
        @foreach ($reportData['layoutEmpresa'] as $chaveRelatorio => $dataGrupo)
                <div class="text-center">
                        <h4><strong>{{$chaveRelatorio}}</strong></h4>
                </div>
                <table class="table-report mb-3">
                        <thead>
                                <tr>
                                        <th class="text-left">CONTA GERENCIAL</th>

                                        @if ($tipoAnalise == 'V')
                                                @foreach ($centrosCusto as $row => $cc) 
                                                        <th>{{$cc->siglaCentroCusto}}</th>

                                                        @php
                                                                // INICIALIZA OS TOTAIS DE CADA CENTRO DE CUSTO
                                                                $totaisSubGrupo[$cc->siglaCentroCusto]  = NULL;
                                                                $totaisGrupo[$cc->siglaCentroCusto]     = NULL;
                                                                $totaisEmpresa[$cc->siglaCentroCusto]   = NULL;
                                                        @endphp
                                                @endforeach
                                        @else
                                                @foreach ($empresas as $row => $empresa) 
                                                        <th>{{$empresa->nomeAlternativo}}</th>

                                                        @php
                                                                // INICIALIZA OS TOTAIS DE CADA CENTRO DE CUSTO
                                                                $totaisSubGrupo[$empresa->nomeAlternativo]  = NULL;
                                                                $totaisGrupo[$empresa->nomeAlternativo]     = NULL;
                                                                $totaisEmpresa[$empresa->nomeAlternativo]   = NULL;
                                                        @endphp
                                                @endforeach
                                        @endif

                                        <th>TOTAL</th>
                                </tr>
                        </thead>
                        
                        <tbody>

                        <!--// GRUPO DE CONTA -->
                        @foreach ($dataGrupo as $grupo => $dataSubGrupo)
                                <!--// SUB-GRUPO DE CONTA -->
                                @foreach ($dataSubGrupo as $subGrupo => $dataCodigo)
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
                                                @if ($tipoAnalise == 'V')
                                                        <!--// ANÁLISE VERTICAL (EMPRESA x CENTROS DE CUSTO) -->
                                                        @foreach ($centrosCusto as $cc)
                                                                <td class="values">
                                                                        @if (isset($dataConta[$cc->siglaCentroCusto]))
                                                                                {{number_format($dataConta[$cc->siglaCentroCusto], $decimals,',','.')}}
                                                
                                                                                @php
                                                                                // ACUMULA OS TOTAIS
                                                                                $totaisSubGrupo[$cc->siglaCentroCusto]      += $dataConta[$cc->siglaCentroCusto];
                                                                                $totaisGrupo[$cc->siglaCentroCusto]         += $dataConta[$cc->siglaCentroCusto];
                                                                                $totaisEmpresa[$cc->siglaCentroCusto]       += $dataConta[$cc->siglaCentroCusto];

                                                                                $totalLinha                                 += $dataConta[$cc->siglaCentroCusto];
                                                                                @endphp
                                                                        @endif
                                                                </td>
                                                        @endforeach
                                                @else
                                                        <!--// ANÁLISE HORIZONTA (CENTRO DE CUSTO x EMPRESA) -->
                                                        @foreach ($empresas as $empresa)
                                                                <td class="values">
                                                                        @if (isset($dataConta[$empresa->nomeAlternativo]))
                                                                                {{number_format($dataConta[$empresa->nomeAlternativo], $decimals,',','.')}}
                                                
                                                                                @php
                                                                                // ACUMULA OS TOTAIS
                                                                                $totaisSubGrupo[$empresa->nomeAlternativo]      += $dataConta[$empresa->nomeAlternativo];
                                                                                $totaisGrupo[$empresa->nomeAlternativo]         += $dataConta[$empresa->nomeAlternativo];
                                                                                $totaisEmpresa[$empresa->nomeAlternativo]       += $dataConta[$empresa->nomeAlternativo];

                                                                                $totalLinha                                 += $dataConta[$empresa->nomeAlternativo];
                                                                                @endphp
                                                                        @endif
                                                                </td>
                                                        @endforeach
                                                @endif
                                                        <!--// Total "horizontal" da conta -->
                                                        {{-- <td class="total-col values">
                                                                {{number_format($hrTotals[$codigoConta], $decimals,',','.')}}
                                                                
                                                                @php
                                                                    // ACUMULA O TOTAL GERAL
                                                                    $totalSubGrupo      += $hrTotals[$codigoConta];
                                                                    $totalGrupo         += $hrTotals[$codigoConta];
                                                                    $totalEmpresa       += $hrTotals[$codigoConta];
                                                                @endphp
                                                        </td> --}}

                                                        <td class="total-col values">
                                                                {{number_format($totalLinha, $decimals,',','.')}}
                                                                
                                                                @php
                                                                    // ACUMULA O TOTAL GERAL
                                                                    $totalSubGrupo      = $totalGrupo   =       $totalEmpresa       += $totalLinha;
                                                                @endphp
                                                        </td>

                                                </tr> <!--// fim da linha de dados //-->
                                        @endforeach <!--// CONTA -->

                                        <!--// EXIBE OS TOTAIS DO SUBGRUPO -->
                                        <tr class="row-totals">
                                                <td class="account">{{$subGrupo}}</td>
                                        
                                                @if ($tipoAnalise == 'V')
                                                        <!--// 
                                                                ANÁLISE VERTICAL (EMPRESA x CENTRO DE CUSTO)
                                                                TOTAIS DO SUB-GRUPO DE CADA CENTRO DE CUSTO 
                                                        -->
                                                        @foreach ($centrosCusto as $cc)
                                                                <td class="values">
                                                                        @if (isset($totaisSubGrupo[$cc->siglaCentroCusto]))
                                                                                {{number_format($totaisSubGrupo[$cc->siglaCentroCusto], $decimals,',','.')}}
                                                                        @endif

                                                                        @php
                                                                                //INICIALIZA OS TOTALIZADORES
                                                                                $totaisSubGrupo[$cc->siglaCentroCusto] = NULL;
                                                                        @endphp
                                                                </td>
                                                        @endforeach
                                                    
                                                @else
                                                        <!--// 
                                                                ANÁLISE HORIZONTAL (CENTRO DE CUSTO x EMPRESA)
                                                                TOTAIS DO SUB-GRUPO DE CADA EMPRESA
                                                        -->
                                                        @foreach ($empresas as $empresa)
                                                                <td class="values">
                                                                        @if (isset($totaisSubGrupo[$empresa->nomeAlternativo]))
                                                                                {{number_format($totaisSubGrupo[$empresa->nomeAlternativo], $decimals,',','.')}}
                                                                        @endif

                                                                        @php
                                                                                //INICIALIZA OS TOTALIZADORES
                                                                                $totaisSubGrupo[$empresa->nomeAlternativo] = NULL;
                                                                        @endphp
                                                                </td>
                                                        @endforeach
                                                @endif

                                                <td class="total-col values">
                                                        {{number_format($totalSubGrupo, $decimals,',','.')}}

                                                        @php
                                                            $totalSubGrupo = NULL;
                                                        @endphp
                                                </td>
                                        </tr>
                                        
                                @endforeach <!--// SUBGRUPO -->

                                <!--// EXIBE OS TOTAIS DO GRUPO -->
                                <tr class="row-totals">
                                        <td class="account">{{$grupo}}</td>
                                        @if ($tipoAnalise == 'V')
                                                <!--// ANÁLISE VERTICAL - TOTAIS DO GRUPO DE CADA CENTRO DE CUSTO -->
                                                @foreach ($centrosCusto as $cc)
                                                        <td class="values">
                                                                @if (isset($totaisGrupo[$cc->siglaCentroCusto]))
                                                                        {{number_format($totaisGrupo[$cc->siglaCentroCusto], $decimals,',','.')}}
                                                                @endif

                                                                @php
                                                                        //INICIALIZA OS TOTALIZADORES
                                                                        $totaisGrupo[$cc->siglaCentroCusto] = NULL;
                                                                @endphp
                                                        </td>
                                                @endforeach
                                        @else
                                                <!--// ANÁLISE HORIZONTAL - TOTAIS DO GRUPO DE CADA CENTRO DE CUSTO -->
                                                @foreach ($empresas as $empresa)
                                                <td class="values">
                                                        @if (isset($totaisGrupo[$empresa->nomeAlternativo]))
                                                                {{number_format($totaisGrupo[$empresa->nomeAlternativo], $decimals,',','.')}}
                                                        @endif

                                                        @php
                                                                //INICIALIZA OS TOTALIZADORES
                                                                $totaisGrupo[$empresa->nomeAlternativo] = NULL;
                                                        @endphp
                                                </td>
                                                @endforeach
                                        @endif

                                        <td class="total-col values">
                                                {{number_format($totalGrupo, $decimals,',','.')}}

                                                @php
                                                    $totalGrupo = NULL;
                                                @endphp
                                        </td>
                                </tr>

                        @endforeach <!--// GRUPO -->

                                <!--// RESULTADO LÍQUIDO EXIBE OS TOTAIS DA EMPRESA -->
                                <tr class="row-totals ts2 tw-8">
                                        <td class="pl-2">RESULTADO LÍQUIDO</td>
                                        <!--<td>{{$chaveRelatorio}}</td> -->
                                        @if ($tipoAnalise == 'V')
                                                <!--// TOTAIS DA EMPRESA DE CADA CENTRO DE CUSTO -->
                                                @foreach ($centrosCusto as $cc)
                                                        <td class="values">
                                                                @if (isset($totaisEmpresa[$cc->siglaCentroCusto]))
                                                                        {{number_format($totaisEmpresa[$cc->siglaCentroCusto], $decimals,',','.')}}
                                                                @endif

                                                                @php
                                                                        //INICIALIZA OS TOTALIZADORES
                                                                        $totaisEmpresa[$cc->siglaCentroCusto] = NULL;
                                                                @endphp
                                                        </td>
                                                @endforeach
                                        @else
                                                <!--// ANÁLISE HORIZONTAL - TOTAIS DA EMPRESA DE CADA CENTRO DE CUSTO -->
                                                @foreach ($empresas as $empresa)
                                                        <td class="values">
                                                                @if (isset($totaisEmpresa[$empresa->nomeAlternativo]))
                                                                        {{number_format($totaisEmpresa[$empresa->nomeAlternativo], $decimals,',','.')}}
                                                                @endif

                                                                @php
                                                                        //INICIALIZA OS TOTALIZADORES
                                                                        $totaisEmpresa[$empresa->nomeAlternativo] = NULL;
                                                                @endphp
                                                        </td>
                                                @endforeach
                                        @endif

                                        <td class="total-col values">
                                                {{number_format($totalEmpresa, $decimals,',','.')}}

                                                @php
                                                $totalEmpresa = NULL;
                                                @endphp
                                        </td>
                                </tr>

                        </tbody>
                </table>
        @endforeach <!--// EMPRESA -->
</section>

</div>