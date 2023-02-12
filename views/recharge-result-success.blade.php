@extends('hanoivip::layouts.app')

@section('title', 'Mua vàng trong game.')

@section('content')

<p>{{__('hanoivip.game::recharge.success')}}</p>
<a href="{{ route('recharge') }}">Chuyển nữa</a>

@endsection
