    <div class="card mr-auto filtro-relatorio">
        <div class="card-header text-white text-center"><h3>{{$tituloPagina}}</h3></div>
        <div class="card-body bg-light text-dark">
            @php
                if (isset($mes) && isset($ano)) {
                    echo "<h3 class='tw-7 mb-3'>PERÍODO: ".$mes."/".$ano."</h3>";
                }

            @endphp

            {!!$infoPagina!!}
        </div>
        <div class="card-footer text-danger text-center">
            <button class="btn btn-orange" data-nav="{{route($action)}}">{{$buttonActionText}}</button>
        </div>
    </div>
