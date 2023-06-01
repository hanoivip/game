@extends('hanoivip::layouts.app')

@section('title', 'Game Rank')

@push('scripts')
    <script src="/js/gamerank.js"></script>
@endpush

@section('content')

@if (!empty($servers))
<form>
	<select id="gamerank-svname" name="svname" style="width: 100%;" data-action="{{ route('game.rank') }}" 
		data-template="hanoivip::game-rank-partial" data-update-id="game-rank-list">
			<option value="">Choose server</option>
    		@foreach ($servers as $sv)
    			<option value="{{ $sv->name }}">{{ $sv->title }}</option>
    		@endforeach
	</select>
	<select id="gamerank-type" name="type" style="width: 100%;" data-action="{{ route('game.rank') }}" 
		data-template="hanoivip::game-rank-partial" data-update-id="game-rank-list">
			<option value="1">Test rank type 1</option>
			<option value="2">Test rank type 2</option>
	</select>
	<div id="game-rank-list">
	</div>
</form>
@else
<p>Please come back later!</p>
@endif

@endsection