@extends('hanoivip::admin.layouts.admin')

@section('title', 'Find Receipt')

@section('content')

<form method="post" action="{{route('ecmin.newrecharge.receipt')}}">
	{{ csrf_field() }}
	Nhập mã hoá đơn: <input type="text" id="receipt" name="receipt" value=""/>
	<br/>
	<button type="submit">OK</button>
</form>

@if (!empty($detail))
<p>Chi tiết hoá đơn {{$receipt}}</p>
    @if (gettype($detail) == 'string')
    <p>Không thành công, lý do: {{$detail}}</p>
    @else
    	@if ($detail->isPending())
    		<p>Thanh toán trễ,đợi thêm</p>
    	@elseif ($detail->isFailure())
    		<p>Thanh toán thất bại</p>
		@else
			<p>Thanh toán thành công</p>
    	@endif
    @endif
@endif

@if (!empty($trans))
<p>Chi tiết thanh toán</p>
<p>Hình thức: {{$trans->method}}</p>
<p>Đơn hàng: {{$trans->order}}</p>
@endif

@endsection