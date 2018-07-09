@extends('hanoivip::layouts.web')

@section('title', 'Chuyển đồ vào game')

@section('content')

@if (!empty($message))
    <div class="alert alert-success">
        {{ $message }}
    </div>
@endif
@if (!empty($error))
	<div class="alert alert-error">
        {{ $error }}
    </div>
@endif
							
@endsection