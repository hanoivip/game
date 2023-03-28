@extends('hanoivip::layouts.app')

@section('title', 'Choose item to buy')

@section('content')

@if (!empty($items))
<form method="get" action="{{route('newrecharge.do')}}">
{{ csrf_field() }}
<input type="hidden" id="svname" name="svname" value="{{$svname}}"/>
<input type="hidden" id="role" name="role" value="{{$role}}"/>
<input type="hidden" id="client" name="client" value="{{$client}}"/>
Choose item to buy:<select id="item" name="item">
	@foreach ($items as $item)
		<option value="{{$item->merchant_id}}">
			{{$item->merchant_title}} - {{$item->price}} {{$item->currency}}
		</option>
	@endforeach
</select>
	<button type="submit">Next</button>
</form>
@else
	<p>{{__('hanoivip.game::newrecharge.shop-empty')}}</p>
@endif

@endsection