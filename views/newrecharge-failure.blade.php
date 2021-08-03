@extends('hanoivip::layouts.app')

@section('title', 'Mua vàng trong game.')

@section('content')

@if (isset($message))
<p>{{$message}}</p>
@endif

<a href="{{ route('newrecharge') }}">Chuyển nữa</a>

@endsection
