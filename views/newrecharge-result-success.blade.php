@extends('hanoivip::layouts.app')

@section('title', 'Mua vàng trong game.')

@section('content')

<p>{{__('hanoivip::newrecharge.success')}}</p>
<a href="{{ route('newrecharge') }}"><button>Chuyển nữa</button></a>

@endsection
