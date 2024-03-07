@extends('hanoivip::layouts.app')

@section('title', 'Mua vàng trong game.')

@push('scripts')
    <script src="/js/recharge4.js"></script>
@endpush


@section('content')

<style type="text/css"> 
form{
		margin: auto;
		width:  300px;
		height: 300px;
		padding-top: 20px;

	}
	form li{		
		width:  100%;
		height: 36px;
		margin-bottom: 10px;

	}
	form li label, form li input, form li select {
		float:left;
		line-height: 36px;
		padding:0 5px;
		font-size: 15px;
	}
	form li input, form li select{
		background-color: #fff;
		font-size: 12px;
		width: 131px;
		height: 30px;
		border-radius: 10px;
		float:right;
	}
	p{
		text-align: center;
	}
	form li span{
		width: 100%;
		float: right;
		line-height: 30px;
		text-align: center;
		background-color: red;
		margin-bottom: 10px;
	}
.btn_topup, .btn_recharge{
	width: 131px; border-radius: 10px;  padding: 5px 0; text-decoration: none; color: white;  height: 30px; background: #F2385A; position: relative; font-size: 14px; border-radius: 10px; transition: all 0.2s; background-color: #3498DB;  box-shadow: 0px 5px 0px 0px #258cd1;float: right;
}
</style>

<main class="main fixCen cf">
    <div class="lnews_left">
        <div class="cf">
            <a href="javascript:void(0);" class="spr main_dl-btdl main_dl-ios controlDownload"></a>
            <a href="javascript:void(0);" class="spr main_dl-btdl main_dl-gg controlDownload"></a>
            <a href="javascript:void(0);" class="spr main_dl-btdl main_dl-apk controlDownload"></a>
        </div>
    </div>
    <div class="lnews_right"><div class="lnews_right_ct">

<ul class="ListServer" style="display:block;color: black;">

<div id="recharge-balances-div">
	@include('hanoivip::balances-partial', ['balances' => $balances])
</div>
<div>
	<a data-action="{{ route('balance.info') }}" id="recharge-balance-refresh" data-update-id="recharge-balances-div">Cập nhật</a>
</div>

<p style="color: #337ab7;background: #f2dede;border-color: #ebccd1;text-align: center;width: 300px; margin: auto;" id="message"></p>

<p style="color: red;background: #f2dede;border-color: #ebccd1;text-align: center;width: 300px; margin: auto;" id="error-message"></p>



<form data-action="{{ route('game.recharge') }}" id="recharge">
	<p style="text-align: left;">Chọn máy chủ:</p>
	<select id="recharge-svname" name="svname" style="width: 100%;" data-action="{{ route('game.roles') }}" data-update-id="recharge-roles-div">
		{{ show_user_servers() }}
	</select>
	<p style="text-align: left;">Chọn số tiền:</p>
	<select id="package" name="package" style="width: 100%;">
		@foreach ($packs as $p)
			<option value="{{ $p->code }}">{{ $p->title }}</option>
		@endforeach
	</select>
	<div id="recharge-roles-div">
		@include('hanoivip::recharge-roles-partial', ['roles' => $roles])
	</div>
	<a data-action="{{ route('game.roles') }}" id="recharge-refresh-roles" data-update-id="recharge-roles-div">Làm mới ds nhân vật</a>
	<br/>
	<div>
    	@if (count($balances) > 1)
    		<p style="text-align: left;">Chọn loại tài khoản thanh toán:</p>
    		<select id="balance_type" name="balance_type">
    			@foreach ($balances as $bal)
    				<option value="{{ $bal->balance_type }}">{{ __('hanoivip.payment::balance.types.' . $bal->balance_type)}}</option>
    			@endforeach
    		</select>
    	@elseif (count($balances) == 1)
    		<input type="hidden" id="balance_type" name="balance_type" value="{{ $balances[0]->balance_type }}" />
    	@else
    		<p>Bạn cần thanh toán trước!</p>
    	@endif
	</div>
	<br/>
	<button type="submit" style="width: 100%;
    padding: 7px 0;
    text-indent: 0;
    color: #fff;
    background-color: #5cb85c;
    border-color: #4cae4c;
    border-radius: 5px;" id="btn-recharge" data-request-loading="#recharge-loading">Chuyển xu</button>
    <img src="/img/loading.gif" style="display: none;" id="recharge-loading"/>
</form>



</ul>
</div>
</div>
</main>
@endsection
