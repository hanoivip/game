@extends('hanoivip::layouts.app')

@section('title', 'Mua vàng trong game.')

@section('content')

<p>{{__('hanoivip::newrecharge.pending')}}</p>

<a href="{{ route('newrecharge.refresh', ['trans' => $trans]) }}">Cập nhật</a>

<a href="{{ route('newrecharge') }}">Chuyển nữa</a>

@endsection
