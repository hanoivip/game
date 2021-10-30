@extends('hanoivip::admin.layouts.admin')

@section('title', 'Recharge history - NewFlow')

@section('content')

@if (!empty($history))
<table>
    <tr>
        <th>Mã đơn hàng</th>
        <th>Mã hóa đơn</th>
        <th>T.thái thanh toán</th>
        <th>Giá trị</th>
        <th>T.thái nhân vật</th>
        <th>Ngày tháng</th>
        <th>Thao tác</th>
    </tr>
	@foreach ($history as $his)
	<tr>
		<td>{{$his->order}}</td>
		<td>{{$his->receipt}}</td>
		<td>{{__('hanoivip::newrecharge.history.status.' . $his->status)}}</td>
		<td>{{$his->amount}}</td>
		<td>{{__('hanoivip::newrecharge.history.game_status.' . $his->game_status)}}</td>
		<td>{{$his->created_at}}</td>
		<td>
			<form method="post" action="{{route('ecmin.newrecharge.receipt')}}">
				{{csrf_field()}}
				<input type="hidden" name="receipt" id="receipt" value="{{$his->receipt}}"/>
				<button type="submit">Detail</button>
			</form>
		</td>
	</tr>
	@endforeach
</table>

@for($page=0; $page<$total_page; ++$page)
	<a href="{{route('newhistory', ['page' => $page])}}">{{$page}}</a>
@endfor

@else
	<p>{{__('hanoivip::newrecharge.history.empty')}}</p>
@endif

@endsection