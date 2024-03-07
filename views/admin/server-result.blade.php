@extends('hanoivip::admin.layouts.admin')

@section('title', 'Server Manager')

@section('content')

<form method="GET" action="{{ route('ecmin.server') }}">
	<button type="submit">Server Manager</button>
</form>

@endsection