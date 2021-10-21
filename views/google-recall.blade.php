@extends('hanoivip::layouts.app')

@section('title', 'Google Recall Order')

@section('content')

<form method="POST" action="{{ route('google.recall') }}">
{{ csrf_field() }}
Merchant ID:<input type="text" id="product_id" name="product_id" />
Purchase Token:<input type="text" id="purchase_token" name="purchase_token" />
Server: <select id="svname" name="svname">
			@foreach ($servers as $s)
				<option value="{{$s->svname}}">{{$s->title}}</option>
			@endforeach
		</select>
Role ID: <input type="text" id="role" name="role" />
<button type="submit">OK</button>
</form>

@endsection