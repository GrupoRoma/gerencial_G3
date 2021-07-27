@php
    $pages = ceil($links->total() / $links->perPage());

    $url   = $links->path();
    $rota  = Route::currentRouteName();

    $currentPage = $links->currentPage();
    $previousPage= $currentPage - 1;
    $nextPage    = $currentPage + 1;

    $lastPage    = $links->lastPage();

@endphp

<div >
        <button class="btn {{($currentPage == $previousPage ? 'btn-secondary' : ' btn-warning')}}" 
                data-nav="{{route($rota, ['columnOrder='.$columnOrder,'page='.$previousPage])}}" {{($previousPage <= 0 ? 'disabled' : '')}}
                data-toggle="tooltip" title="Anterior [{{$previousPage}}]">
                <span class="fa fa-angle-left"></span>
        </button>

        @php
                if (($currentPage+10) <= $lastPage)   $startCount = 0;
                else                                  $startCount = ($currentPage - $lastPage);
        
        @endphp
        
        @for($page = $startCount; $page < ($startCount + 10) ; $page ++)
                <button class="btn {{(($currentPage+$page) == $currentPage ? 'btn-secondary' : ' btn-warning')}}" 
                        data-nav="{{route($rota, ['columnOrder='.$columnOrder,'page='.($currentPage+$page)])}}" {{(($currentPage+$page) == $currentPage ? 'disabled' : '')}}
                        data-toggle="tooltip" title="{{($currentPage+$page)}}"> 
                        {{($currentPage+$page)}}
                </button>

                @if (($currentPage+$page) == $lastPage) @break @endif
        @endfor

        @if (($currentPage+10) < $lastPage)
                <button class="btn btn-secondary" disabled> ... </button>
        @endif

        <button class="btn {{(($currentPage+$page) == $currentPage ? 'btn-secondary' : ' btn-warning')}}" 
                data-nav="{{route($rota, ['columnOrder='.$columnOrder,'page='.$nextPage])}}" 
                data-toggle="tooltip" title="Próxima [{{$nextPage}}]"
                {{($currentPage == $nextPage ? 'disabled' : '')}}>
                <span class="fa fa-angle-right"></span>
        </button>
</div>
