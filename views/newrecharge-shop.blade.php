@extends('hanoivip::layouts.app')

@section('title', 'Shop')

@section('content')

@if (!empty($items))
<form method="get" action="{{route('newrecharge.do')}}">
{{ csrf_field() }}
<input type="hidden" id="svname" name="svname" value="{{$svname}}"/>
<input type="hidden" id="role" name="role" value="{{$role}}"/>
Chọn gói nạp:<select id="item" name="item">
	@foreach ($items as $item)
		<option value="{{$item->merchant_id}}">
			{{$item->merchant_title}}
		</option>
	@endforeach
</select>
	<button type="submit">Next</button>
</form>
@else
	<p>{{__('hanoivip::newrecharge.shop-empty')}}</p>
@endif

@endsection