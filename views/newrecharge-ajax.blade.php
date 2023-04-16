@extends('hanoivip::layouts.app')

@section('title', 'Buy game item (new flow with ajax)')

@push('scripts')
    <script src="/js/newrecharge.js"></script>
@endpush

@section('content')

@if (!empty($servers))
<form method="post" action="{{ route('newrecharge.do') }}" id="newrecharge-form">
	{{ csrf_field() }}
	<input id="client" name="client" type="hidden" value="{{ $client }}" />
	<select id="newrecharge-svname" name="svname" style="width: 100%;" data-action="{{ route('game.roles') }}" 
		data-template="hanoivip::newrecharge-roles-partial" data-update-id="newrecharge-roles-div">
			<option value="">Chọn máy chủ</option>
    		@foreach ($servers as $sv)
    			<option value="{{ $sv->name }}">{{ $sv->title }}</option>
    		@endforeach
	</select>
	<div id="newrecharge-roles-div">
		@include('hanoivip::newrecharge-roles-partial')
	</div>
	<a data-action="{{ route('game.roles') }}" id="recharge-refresh-roles" data-update-id="recharge-roles-div">Làm mới ds nhân vật</a>
	<div id="newrecharge-shop-div">
		@include('hanoivip::newrecharge-shop-partial')
	</div>
	<button type="submit" class="btn btn-primary" id="btn-newrecharge">Next</button>
</form>
@else
<p>Please come back later!</p>
@endif

@endsection