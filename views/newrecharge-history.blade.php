@extends('hanoivip::layouts.app')

@section('title', 'Shop')

@section('content')

@if (!empty($history))
<table>
    <tr>
        <th>Mã đơn hàng</th>
        <th>Mã hóa đơn</th>
        <th>T.thái thanh toán</th>
        <th>Giá trị</th>
        <th>T.thái nhân vật</th>
    </tr>
	@foreach ($history as $his)
	<tr>
		<td>{{$his->order}}</td>
		<td>{{$his->receipt}}</td>
		<td>{{__('hanoivip::newrecharge.history.status.' . $his->status)}}</td>
		<td>{{$his->amount}}</td>
		<td>{{__('hanoivip::newrecharge.history.game_status.' . $his->game_status)}}</td>
	</tr>
	@endforeach
</table>

@for($page=0; $page<$total_page; ++$page)
	<a src="{{route('newhistory', ['page' => $page])}}">{{$page}}</a>
@endfor

@else
	<p>__('hanoivip::newrecharge.history.empty')</p>
@endif

@endsection