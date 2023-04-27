@extends('hanoivip::layouts.app')

@section('title', 'Mua vàng trong game.')

@push('scripts')
    <script src="/js/recharge.js"></script>
@endpush

@section('content')

<div id="recharge-balances-div">
	@include('hanoivip::balances-partial', ['balances' => $balances])
</div>
<div>
	<a data-action="{{ route('balance.info') }}" id="recharge-balance-refresh" data-update-id="recharge-balances-div">Cập nhật</a>
</div>


<p style="color: #337ab7;background: #f2dede;border-color: #ebccd1;text-align: center;width: 300px; margin: auto;" id="message"></p>

<p style="color: red;background: #f2dede;border-color: #ebccd1;text-align: center;width: 300px; margin: auto;" id="error-message"></p>

<form data-action="{{ route('game.recharge') }}" id="recharge">
	<p style="text-align: left;">Choose server</p>
	<select id="recharge-svname" name="svname" style="width: 100%;" data-action="{{ route('game.roles') }}" data-update-id="recharge-roles-div">
		@foreach ($servers as $sv)
			<option value="{{ $sv->name }}">{{ $sv->title }}</option>
		@endforeach
	</select>
	<p style="text-align: left;">Choose package to buy</p>
	<select id="package" name="package" style="width: 100%;">
		@foreach ($packs as $p)
			<option value="{{ $p->code }}">{{ $p->title }}</option>
		@endforeach
	</select>
	<div id="recharge-roles-div">
		@include('hanoivip::recharge-roles-partial', ['roles' => $roles])
	</div>
	<a data-action="{{ route('game.roles') }}" id="recharge-refresh-roles" data-update-id="recharge-roles-div">Refresh roles</a>
	<br/>
	
	<div>
    	@if (count($balances) > 1)
    		<p style="text-align: left;">Choose your balance type:</p>
    		<select id="balance_type" name="balance_type">
    			@foreach ($balances as $bal)
    				<option value="{{ $bal->balance_type }}">{{ __('hanoivip.payment::balance.types.' . $bal->balance_type)}}</option>
    			@endforeach
    		</select>
    	@elseif (count($balances) == 1)
    		<input type="hidden" id="balance_type" name="balance_type" value="{{ $balances[0]->balance_type }}" />
    	@else
    		<p>You must buy coin first!</p>
    	@endif
	</div>
	
    <br/>
	<button type="submit" style="width: 100%;" class="btn btn-primary" id="btn-recharge" data-request-loading="#recharge-loading">Buy it</button>
	<img src="/img/loading.gif" style="display: none; width: 32px; height: 32px;" id="recharge-loading"/>
</form>

@endsection
