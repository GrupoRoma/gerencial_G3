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

    // Identifica o nome da rota atual para uso nos botões de ação e no formulário
    $routeName = substr(Route::currentRouteName(),0,strrpos(Route::currentRouteName(),'.'));
    $saveEnabled = false;
    
    // Verifica se as ações são para criação ou edição de dados
    // para habilitar o botão para gravação dos dados
    if (Str::contains(Route::currentRouteName(), 'create')) {
        $saveEnabled = true;
    }
    elseif (Str::contains(Route::currentRouteName(), 'edit')) {
        $saveEnabled = true;
    };

@endphp
<div id="CRUD-area">
  <div class="row mb-2">
      <div class="col-xs-12 col-sm-4 col-md-4 pt-2 pb-2">
          <!--// Novo -->
          <button class="btn btn-secondary btn-sm" data-nav="{{route($routeName.'.create')}}" {{($saveEnabled ? 'disabled' : '')}} data-target="#CRUD-area">
              <span class="fa fa-file fa-lg"></span> <br>
              <span class="btn-label">novo</span>
          </button>

          <!--// Gravar -->
          <button type="submit" class="btn btn-secondary btn-sm" data-redir="{{route($routeName.'.index')}}" {{($saveEnabled ? '' : 'disabled')}} data-target="#CRUD-area">
              <span class="fa fa-save fa-lg"></span> <br>
              <span class="btn-label">gravar</span>
          </button>

          <!--// Cancelar -->
          <button class="btn btn-secondary btn-sm" data-nav="{{route($routeName.'.index')}}" data-target="#CRUD-area">
              <span class="fa fa-times fa-lg"></span> <br>
              <span class="btn-label">cancelar</span>
          </button>

          <!--// Ajuda -->
          <button class="btn btn-secondary btn-sm">
              <span class="fa fa-question fa-lg"></span> <br>
              <span class="btn-label">ajuda</span>
          </button>
      </div>

      <div class="col-xs-12 col-sm-4 col-md-4">
        <h2 class="text-uppercase align-bottom">{{$model->viewTitle}}</h2>
        <h5 class="text-uppercase align-bottom">{{($model->viewSubTitle ?? '')}}</h5>
      </div>

      <div class="col-xs-12 col-sm-4 col-md-4">
        <input class="form-control mr-sm-2 align-top" id='tdSearch' type="search" placeholder="Pesquisar" aria-label="Search">
      </div>
  </div>


  @yield('content')

  {{-- Modal para confirmação de exclusão de dados --}}
  <div class="modal fade" id="delete-confirm" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">EXCLUSÃO DE DADOS</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <p>Confirma a exclusão dos dados?</p>
            <div class="data-del"></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger confirm">CONFIRMAR</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          </div>
        </div>
      </div>
    </div>