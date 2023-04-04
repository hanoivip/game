@extends('hanoivip::layouts.app')

@section('title', 'Payment history')

@section('content')

@if (!empty($history))
<table>
    <tr>
        <th>Order</th>
        <th>Receipt</th>
        <th>Payment status</th>
        <th>Payment amount</th>
        <th>Game role status</th>
        <th>Time</th>
    </tr>
	@foreach ($history as $his)
	<tr>
		<td>{{$his->order}}</td>
		<td>{{$his->receipt}}</td>
		<td>{{__('hanoivip.game::newrecharge.history.status.' . $his->status)}}</td>
		<td>{{$his->amount}}</td>
		<td>{{__('hanoivip.game::newrecharge.history.game_status.' . $his->game_status)}}</td>
		<td>{{__($his->created_at)}}</td>
	</tr>
	@endforeach
</table>

@for($page=0; $page<$total_page; ++$page)
	<a href="{{route('newhistory', ['page' => $page])}}">{{$page}}</a>
@endfor

<br/><br/>
<a class="btn btn-primary" href="{{route('newhistory')}}">Refresh</a>

@else
	<p>{{__('hanoivip.game::newrecharge.history.empty')}}</p>
@endif

@endsection