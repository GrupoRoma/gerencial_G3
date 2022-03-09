@php
// carrega os metadados das colunas da tabela de dados
$tablePrefix = DB::getTablePrefix();
    $metaData = DB::select("SELECT columnName       = columns.name,
                                    columnId        = columns.column_id,
                                    columnLength    = CASE WHEN types.name = 'nvarchar' OR types.name = 'nchar' THEN (columns.max_length/2)
                                                           ELSE columns.max_length
                                                      END,
                                    columnPrecision = columns.precision,
                                    nullable        = columns.is_nullable,
                                    primaryKey      = columns.is_identity,
                                    columnType      = CASE WHEN types.name = 'nvarchar' AND columns.max_length = -1 THEN 'text'
                                                           ELSE types.name
                                                      END,
                                    /*  Identifica o nome da tabela de dados da chave estrangeira para gerar os dados para o select */
                                    foreignTable    = ( SELECT CASE WHEN SUBSTRING(TabelaFK.name,1,3) <> '".$tablePrefix."' THEN TabelaFK.name ELSE SUBSTRING(TabelaFK.name,4,LEN(TabelaFK.name)) END
                                                        FROM sys.tables                 AS TabelaFK
                                                        JOIN sys.foreign_keys           AS FKey     ON FKey.referenced_object_id = TabelaFK.object_id
                                                        JOIN sys.foreign_key_columns    AS FKC      ON FKC.constraint_object_id = FKey.object_id
                                                        JOIN sys.columns                AS Col      ON Col.object_id = FKC.parent_object_id 
                                                                                                   AND Col.column_id = FKC.parent_column_id
                                                        WHERE FKey.parent_object_id = tables.object_id
                                                        AND   Col.name = columns.name),
                                    fkColumnValue   = ( SELECT ColValue.name
                                                        FROM sys.tables                 AS TabelaFK
                                                        JOIN sys.foreign_keys           AS FKey     ON FKey.referenced_object_id = TabelaFK.object_id
                                                        JOIN sys.foreign_key_columns    AS FKC      ON FKC.constraint_object_id = FKey.object_id
                                                        JOIN sys.columns                AS Col      ON Col.object_id = FKC.parent_object_id 
                                                                                                   AND Col.column_id = FKC.parent_column_id
                                                        JOIN sys.columns                AS ColValue ON ColValue.column_id = FKC.constraint_column_id 
                                                                                                   AND ColValue.object_id = FKC.parent_object_id
                                                        WHERE FKey.parent_object_id = tables.object_id
                                                        AND   Col.name = columns.name)
                            FROM sys.columns 
                            JOIN sys.tables ON tables.object_id = columns.object_id 
                            JOIN sys.types on types.user_type_id = columns.user_type_id 
                            WHERE tables.name = '$tablePrefix$tableName'");

    $columnMeta = [];
    foreach ($metaData as $row => $column) {
        $columnMeta[$column->columnName] = $column;
    }
    $jsonMeta = json_encode($columnMeta);
    
    // Identifica o nome da rota atual para uso nos botões de ação e no formulário
    $routeName = substr(Route::currentRouteName(),0,strrpos(Route::currentRouteName(),'.'));
    $saveEnabled = false;

    // Verifica se as ações são para criação ou edição de dados
    // para habilitar o botão para gravação dos dados e definir a rota
    // e o método do formulário
    if (Str::contains(Route::currentRouteName(), 'create'))     $saveEnabled = true;
    elseif (Str::contains(Route::currentRouteName(), 'edit'))   $saveEnabled = true;

@endphp

{{-- Carrega o layout para os formulário de CRUD --}}
@extends('layouts.crud')

@section('content')

@if (method_exists($tableData, 'hasPages'))
    @php $startCount = (($tableData->currentPage()-1)*$tableData->perPage())+1; @endphp

    <div class="pr-5 w-100 text-right">
        <strong>registros econtrados 
            <div class="regCount d-inline-flex">
            {{$tableData->total()}} | 
            Listando de {{(($tableData->currentPage()-1)*$tableData->perPage())+1}} a 
                        {{((($tableData->currentPage()-1)*$tableData->perPage())+$tableData->perPage())}}</div></strong></div>
@else
    <div class="pr-5 w-100 text-right"><strong>registros econtrados <div class="regCount d-inline-flex">{{$tableData->count()}}</div></strong></div>
    @php $startCount = 1; @endphp
@endif

<div class="data-table">
    <!-- TABLE DATA -->
    <table class="table table-striped table-hover table-sm mb-0" id='tableData'>
        <thead>
            <tr>
                <th></th>
                {{-- Prepara o cabeçalho da tabela de dados com as colunas definidas para exibição
                  -- em columnList na model
                  --}}
                @foreach (($model->columnsGrid ?? $model->columnList) as $columns) 
                    <th data-columnName="{{$columns}}" data-nav="public/{{$routeName}}" data-params='{"todo": "order", "columnOrder": "{{$columns}}"}'>
                        @if (isset($orderColumn) && $orderColumn == $columns) <span class="tw-9 text-orange"> 
                        @else <span>
                        @endif

                        {{$model->columnAlias[$columns]}}
                        </span>
                    </th>
                @endforeach
                <th></th>
            </tr>
        </thead>

        <tbody>

            {{-- Processa os dados da tabela e preenche a tabela --}}
            @foreach ($tableData as $row => $data)
                {{$dataInfo_del = ''}}
                {{-- Click na linha para editar o registro --}}
                <tr data-toggle="tooltip" title="clique para editar">
                    <!--<td class="row-count text-right align-bottom">{{($row+1)}}.</td> -->
                    <td class="row-count text-right align-bottom">{{($startCount++)}}.</td>
                    @foreach (($model->columnsGrid ?? $model->columnList) as $column)

                        <td data-nav="{{route($routeName.'.edit', $data->id)}}">
                            {{-- Verifica se a coluna é um chave estrangeira (FK) e busca os valores para exibição no grid --}}
                            @if (isset($columnMeta[$column]->foreignTable))
                                {{$model->{'vd_'.$columnMeta[$column]->foreignTable}($data->$column)}}
                            @else
                                @if (method_exists($model, 'vd_'.$column))
                                    {{-- {!! $model->{'vd_'.$column}( $data->$column ) !!} --}}
                                    {!! (Str::length($model->{'vd_'.$column}( $data->$column )) > 30 ?
                                                    Str::substr($model->{'vd_'.$column}( $data->$column ), 0, 30).' ...' :
                                                    $model->{'vd_'.$column}( $data->$column )) !!}
                                @else
                                    {{-- Valores customizados para exibição dos dados da coluna --}}
                                    @if (array_key_exists($column, $model->columnValue))
                                        {{-- $model->columnValue[$column][$data->$column] --}}
                                        {{(Str::length($model->columnValue[$column][$data->$column]) > 30 ?
                                                        Str::substr($model->columnValue[$column][$data->$column], 0, 30).' ...' :
                                                        $model->columnValue[$column][$data->$column])}}
                                    {{-- Limita a 30 caracteres para exbição na coluna --}}
                                    @else
                                        {{(Str::length($data->$column) > 30 ? Str::substr($data->$column, 0, 30).' ...' : $data->$column)}}
                                    @endif
                                @endif
                            @endif
                                @php
                                    $dataInfo_del .= $model->columnAlias[$column].': ';
                                    $dataInfo_del .= (isset($columnMeta[$column]->foreignTable) ? 
                                                            $model->{'vd_'.$columnMeta[$column]->foreignTable}($data->$column) :
                                                            (array_key_exists($column, $model->columnValue) ? 
                                                                    $model->columnValue[$column][$data->$column] : 
                                                                    (Str::length($data->$column) > 30 ? Str::substr($data->$column, 0, 30).' ...' : $data->$column)
                                                            )
                                                     );
                                    $dataInfo_del .= '<br>';                                    
                                @endphp
                        </td>
                    @endforeach    
                    
                    <td>
                    {{-- Botões de ação para edição e exclusão de dados --}}
                    {{-- Editar -- }}
                    <button class="btn btn-success btn-sm" data-nav="{{route($routeName.'.edit', $data->id)}}" title="Editar"><span class="fa fa-edit"></span></button>
                    {{-- Excluir --}}
                    @if ($model->deleteAble ?? TRUE) 

                        @if (!isset($model->noDeleteID) || !in_array($data->id, $model->noDeleteID))
                            <button class="btn btn-danger btn-sm" data-confirm="{{route($routeName.'.destroy', $data->id)}}" data-show="{{$dataInfo_del}}" data-redir="{{route($routeName.'.index')}}" title="Excluir"><span class="fa fa-trash-alt"></span></button>                        
                        @endif
                        
                    @endif
                    </td>
                </tr>
            @endforeach
        </tbody>

    </table>

    @if (method_exists($tableData, 'hasPages'))
        @include('layouts.paginateLinks', ['links' => $tableData, 'columnOrder' => $orderColumn])
    @endif
    

</div>

</div>
@endsection
