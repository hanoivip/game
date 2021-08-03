@extends('hanoivip::layouts.app')

@section('title', 'Mua vàng trong game.')

@section('content')

<p>{{__('hanoivip::recharge.pending')}}</p>

<a href="{{ route('newrecharge.refresh', ['trans' => $trans]) }}">Refresh</a>

<a href="{{ route('newrecharge') }}">Chuyển nữa</a>

@endsection
