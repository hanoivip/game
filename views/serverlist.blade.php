@extends('hanoivip::layouts.app')

@section('title', 'Chọn máy chủ')

@section('content')
											
<h2>Chọn máy chủ</h2>
@if (count($servers) > 0)

<ul>
	@for ($i=0; $i<min(2, count($servers)); ++$i)
		<li>
			<a class="" title="" href="{{route('play', ['svname' => $servers[$i]->name])}}">
				<span>{{ $servers[$i]->title }}</span>
				<span></span>
			</a>
		</li>
	@endfor          
</ul>


@for ($j=0; $j<count($servers); $j+=10)
<ul id="S{{$j+1}}" style="display:block">
	@for ($i=$j; $i<min($j+10, count($servers)); ++$i)
		<li title="">
			<a href="{{route('play', ['svname' => $servers[$i]->name])}}">
        			<span>{{ !empty($ranks[$servers[$i]->name]) ? $ranks[$servers[$i]->name]['strength'][0]['player'] : '.' }}</span>
        			<span>{{ $servers[$i]->title }}</span>
        			<span>({{ $servers[$i]->ident }})</span>
        			<span>Tốt</span>
        		</a>
		</li>
	@endfor
</ul>
@endfor

@endif

@endsection
