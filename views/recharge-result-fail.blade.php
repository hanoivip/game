@extends('hanoivip::layouts.app')

@section('title', 'Mua vàng trong game.')

@section('content')

@if (isset($error_message))
<p>{{$error_message}}</p>
@endif
<a href="{{ route('recharge') }}">Chuyển lại</a>


@endsection
