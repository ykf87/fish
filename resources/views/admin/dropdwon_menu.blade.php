<div class="btn-group" style="margin-left: 10px">
    <button type="button" class="{{$btn_class}}" data-toggle="dropdown">{{$button_name}}
        <span class="caret"></span>
    </button>
    <ul class="dropdown-menu" role="menu">
        @foreach($list as $title => $url)
            @if($url == 'divider')
                <li class="divider"></li>
            @else
                <li><a href="{{$url}}">{{$title}}</a></li>
            @endif
        @endforeach
    </ul>
</div>