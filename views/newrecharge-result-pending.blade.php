@extends('hanoivip::layouts.app')

@section('title', 'Mua vàng trong game.')

@section('content')

<p>{{__('hanoivip::newrecharge.pending')}}</p>

<a href="{{ route('newrecharge.refresh', ['trans' => $trans]) }}"><button>Cập nhật</button></a>

<a href="{{ route('newrecharge') }}"><button>Chuyển nữa</button></a>

@endsection
