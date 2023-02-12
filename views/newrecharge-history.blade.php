@extends('hanoivip::layouts.app')

@section('title', 'Payment history')

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
		<td>{{__('hanoivip.game::newrecharge.history.status.' . $his->status)}}</td>
		<td>{{$his->amount}}</td>
		<td>{{__('hanoivip.game::newrecharge.history.game_status.' . $his->game_status)}}</td>
	</tr>
	@endforeach
</table>

@for($page=0; $page<$total_page; ++$page)
	<a href="{{route('newhistory', ['page' => $page])}}">{{$page}}</a>
@endfor

<br/><br/>
<a class="btn btn-primary" href="{{route('newhistory')}}"><button>Cập nhật</button></a>

@else
	<p>{{__('hanoivip.game::newrecharge.history.empty')}}</p>
@endif

@endsection