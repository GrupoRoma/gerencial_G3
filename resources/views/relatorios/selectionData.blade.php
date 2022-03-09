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
                                  foreignTable    = ( SELECT SUBSTRING(TabelaFK.name,4,LEN(TabelaFK.name))
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
@endphp

<div id="report-selection" class="container collapse {{$visibility}}">
    <!-- CARD -->
    <div class="card">
        <!-- CARD HEADER -->
        <div class="card-header bg-secondary text-white">
            <div class="row">
                <!-- TÍTULO -->
                <div class="col-11"><h6 class="text-center tw-7">{{$title}} [ FILTRO ]</h6></div>
                <!-- BOTÃO PARA FECHAR O FILTRO -->
                <div class="col-1">
                    <button type="button" class="btn close">
                        <span class="fa fa-times" data-toggle="collapse" data-target="#report-selection"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- CORPO DO FILTRO -->
        <div class="card-body bg-light">
            <form id="gerencial-form" method="GET"   action="{{route(Route::currentRouteName())}}">
                <input type="hidden" name="reportSelection" value="true">
                @csrf

                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-10">
                        @foreach ($config as $index => $formField)

                            @if (!empty($formField->columnName))
        
                                <div class="form-group row">
                                    <div class="col-sm-2 col-form-label text-right">{{strtoupper($formField->label)}}</div>
                                    <div class="col-sm-10">
        
                                        @switch($formField->formType)
                                            @case("period")
                                                @if ($formField->columnName == 'periodoGerencial')
                                                    <div class="form-inline">
                                                        <input class="form-control" type="text" name="{{$formField->columnName}}[]" id="periodoStart" placeholder="99/9999)" value="{{session('_GER_periodoAtivo')}}">
                                                        <input class="form-control ml-2" type="text" name="{{$formField->columnName}}[]" id="periodoEnd" placeholder="99/9999" value="{{session('_GER_periodoAtivo')}}">
                                                    </div>
                                                @else
                                                    <div class="form-inline">
                                                        <input class="form-control" type="date" name="{{$formField->columnName}}[]" id="periodStart" placeholder="Data inicial" value="">
                                                        <input class="form-control ml-2" type="date" name="{{$formField->columnName}}[]" id="periodEnd" placeholder="Data Final" value="{{date('d/m/Y')}}">
                                                    </div>
                                                @endif
                                                @break
{{--                                             @case("periodoGerencial")
                                                <div class="form-inline">
                                                    <input class="form-control" type="text" name="{{$formField->columnName}}[]" id="periodoStart" placeholder="99/9999)" value="{{date('m/Y')}}">
                                                    <input class="form-control ml-2" type="text" name="{{$formField->columnName}}[]" id="periodoEnd" placeholder="99/9999" value="{{date('m/Y')}}">
                                                </div>
                                                @break --}}
                                            @case("date")
                                                <input class="form-control" type="date" name="{{$formField->columnName}}" id="{{$formField->columnName}}" placeholder="{{$formField->label}}" value="{{date('d/m/Y')}}">
                                                @break
                                            @case("text")
                                                <input class="form-control" type="text" name="{{$formField->columnName}}" id="{{$formField->columnName}}" placeholder="{{$formField->label}}" value="">
                                                @break;
                                            @case("foreignKey")
                                                @if (isset($columnMeta[$formField->columnName]->foreignTable) && 
                                                     !empty($columnMeta[$formField->columnName]->foreignTable))

                                                    @php
                                                        $formOptions = $model->{'fk_'.$columnMeta[$formField->columnName]->foreignTable}($columnMeta[$formField->columnName]->fkColumnValue);
                                                    @endphp
        
                                                    <select class="custom-select" name="{{$formField->columnName}}" id="{{$formField->columnName}}">
                                                        <option value=""></option>
                                                        @foreach ( $formOptions['options'] as $key => $options)
                                                            <option value="{{$options[0]}}" {{(isset($columnValue) && $columnValue == $options[0]) ? 'selected' : ''}}>{{$options[1]}}</option>
                                                        @endforeach
                                                    </select>
                                                @elseif (method_exists($model, 'custom_'.$formField->columnName))
                                                    {!! $model->{'custom_'.$formField->columnName}() !!}
                                                @endif
        
                                                @break;
                                            @case("select")
                                                <select class="custom-select" name="{{$formField->columnName}}" id="{{$formField->columnName}}">
                                                    <option value=""></option>
                                                    @foreach ($formField->values as $item => $option) 
                                                        <option value="{{$option->value}}">{{$option->label}}</option>
                                                    @endforeach
                                                </select>
                                                @break;
                                            @case("checkbox")
                                            @case("radio")
                                                    @foreach ($formField->values as $item => $option) 
                                                        <div class="custom-control custom-{{$formField->formType}} custom-control-inline">
                                                            <input type="{{$formField->formType}}" name="{{$formField->columnName}}" id="{{$formField->columnName}}_{{$option->value}}" value='{{$option->value}}' class="custom-control-input"> 
                                                            <label for="{{$formField->columnName}}_{{$option->value}}" class="custom-control-label"> {{$option->label}}</label>
                                                        </div>
                                                    @endforeach
                                                @break;
        
                                            @default
                                                
                                        @endswitch
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-2 mt-auto">
                        <button type="submit" class="btn btn-secondary btn-large">GERAR RELATÓRIO</button>
                    </div>
                </div>

            </form>
        </div>
    </div>

</div>
