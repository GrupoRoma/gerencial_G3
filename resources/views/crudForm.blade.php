{{-- Injeção da depedência para manipulação de dados 
  -- através de querys SQL
  --}}
  @inject('DB', 'Illuminate\Support\Facades\DB')
  
  @php
    // carrega os metadados das colunas da tabela de dados
    $tablePrefix = DB::getTablePrefix();
    $metaData = DB::select("SELECT columnName       = columns.name,
                                   columnId         = columns.column_id,
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
    if (Str::contains(Route::currentRouteName(), 'create')) {
        $saveEnabled = true;
        $formAction = $routeName.'.store';
        $method     = 'POST';
    }
    elseif (Str::contains(Route::currentRouteName(), 'edit')) {
        $saveEnabled = true;
        $formAction = $routeName.'.update';
        $method     = 'PUT';
    };
@endphp

{{--@if (isset($errors) && count($errors) > 0) {{dd($errors)}} @endif --}}

<form id="gerencial-form" action="{{route($formAction, $id)}}" method="{{$method}}" data-redir="{{route($routeName.'.index')}}">
    @csrf
    <input type="hidden" name="_method" value="{{$method}}">

{{-- Carrega o layout para os formulário de CRUD --}}
@extends('layouts.crud')

{{-- Formulário --}}
@section('content')

@if (\View::exists('custom_forms.'.class_basename($model)))
    @include('custom_forms.'.class_basename($model), ['tableData' => $tableData[0] ?? ''])
@else 
    {{--class_basename($model) --}}

    <div class="data-form-default">
        {{-- Token --}}
        @csrf
        <div class="container text-center text-orange">
            <span class="fas fa-square"></span> obrigatório
        </div>

        {{-- Percorre a lista de colunas para exibição definidas na model --}}
        @foreach ($model->columnList as $column)
            @php
                $columnValue = '';
                // Identifica o valor registrado para a coluna para preenchimento do campo do formulário
                if (isset($tableData[0]))   $columnValue = $tableData[0]->$column;
                else                        $columnValue = '';
            @endphp

            {{-- Grupo de formulário com o label e o campo (input, select, textarea, ...) --}}
            <div class="form-group row {{$columnMeta[$column]->nullable ? '' : 'form-required'}}">
                {{-- Label para a coluna. Utiliza a definição de columnAlias[] da model --}}
                <label for="{{$column}}" class="col-xs-12 col-sm-3 xol-md-4 col-form-label text-right">
                    {{$model->columnAlias[$column]}}
                </label>
                <div class="col-xs-12 col-sm-9 col-md-8">

                    @if (method_exists($model, 'custom_'.$column))
                        {!! $model->{'custom_'.$column}( $columnValue) !!}
                    @else    
                    
                        {{-- Verifica se a coluna é uma chave estrangeira 
                            -- Se for, localiza o método fk_[nome da tabela] da model, que deverá retornar
                            -- um array multidimensional (array[][optionValue, optionLabel]) com os dados
                            -- para seleção
                        --}}
                        @if (isset($columnMeta[$column]->foreignTable))
                            @php
                                $formOptions = $model->{'fk_'.$columnMeta[$column]->foreignTable}($columnMeta[$column]->fkColumnValue);
                            @endphp

                            <select name="{{$column}}" id="{{$column}}" class="custom-select {{$errors->has($column) ? 'form-validate' : ''}}" {{$formOptions['type']}}>
                                <option value=""></option>
                                @foreach ( $formOptions['options'] as $key => $options)
                                    <option value="{{$options[0]}}" {{$columnValue == $options[0] ? 'selected' : ''}}>{{$options[1]}}</option>
                                @endforeach
                            </select>
                        @else

                            {{-- Verifica o tipo de formulário a ser exibido
                            -- caso não exista definição de tipo e valores customizados
                            -- será utilizado o padrão do banco de dados
                            --}}
                            @if (isset($model->customType[$column]))
                                @php
                                    // Verifica se o tipo é radio ou checkbox

                                    if ($model->customType[$column]['type'] == 'radio') {
                                        $inputName  = $column;
                                        $columnValue= explode(',', $columnValue);
                                    }
                                    if ($model->customType[$column]['type'] == 'checkbox') {

                                        $columnValue= explode(',', $columnValue);
                                        //$inputName = $column.(count($columnValue) > 1 ? '[]' : '');
                                        $inputName = $column.(count($model->customType[$column]) > 1 ? '[]' : '');
                                    }
                                
                                    $numOptions = count($columnValue);
                                    $current    = 0;
                                    foreach ($model->customType[$column]['values'] as $value => $label) {
                                        if ($current == 0 || $current == 4) {
                                            if ($current > 0) echo "</div>";
                                            echo '<div class="row">';
                                            
                                            $current = 0;
                                        }
//echo $column;
//dd($model);
                                        echo '<div class="col-sm-12 col-xs-3 col-md-3">';
                                        echo '<input type="'.$model->customType[$column]['type'].'" 
                                                name="'.$inputName.'" 
                                                value="'.$value.'" 
                                                '.(in_array($value, $columnValue) ? 'checked' : '').'
                                                id="'.$column.'_'.$value.'"
                                                class=""">
                                                <label for="'.$column.'_'.$value.'">'.$label.'</label>';
                                        echo "</div>";

                                        $current ++;
                                    }
                                    echo "</div>";
                                @endphp
                            @else
                                {{-- Utiliza o padrão de conversão de tipos do Banco de dados para os tipos de Formulários HTML 5 --}}

                                {{-- TEXT AREA --}}
                                @if ($columnMeta[$column]->columnType == 'text')
                                    <textarea class="form-control @error($column) form-validate @enderror" 
                                                name="{{$column}}"
                                                id="{{$column}}" 
                                                placeholder="{{$model->columnAlias[$column]}}">{{$columnValue}}</textarea>
                                @else
                                    <input type="{{DBDataTypeForm($columnMeta[$column]->columnType)}}" 
                                        class="form-control @error($column) form-validate @enderror" 
                                        name="{{$column}}"
                                        id="{{$column}}" 
                                        placeholder="{{$model->columnAlias[$column]}}"
                                            @if (DBDataTypeForm($columnMeta[$column]->columnType) == 'number')
                                                    max="{{str_repeat('9', $columnMeta[$column]->columnLength)}}"
                                                    step="any"
                                            @else
                                                    maxlength="{{$columnMeta[$column]->columnLength}}"    
                                            @endif
                                        value="{{$columnValue}}"
                                    {{--meta="{{$jsonMeta}}"--}}
                                        >
                                @endif
                            @endif

                        @endif
                    @endif
                    {{-- Erro de validação --}}
                    @error($column)
                        <div class="alert alert-danger">{{$message}}</div>
                    @enderror
                </div>
            </div>
        @endforeach
    </div>
@endif

</form>

</div>
@endsection