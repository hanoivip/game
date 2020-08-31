@extends('hanoivip::layouts.app')

@section('title', 'Mua vàng trong game.')

@section('content')

<p>{{__('hanoivip::recharge.success')}}</p>
<a href="{{ route('recharge') }}">Chuyển nữa</a>

@endsection
