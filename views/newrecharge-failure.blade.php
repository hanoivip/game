@extends('hanoivip::layouts.app')

@section('title', 'Mua vàng trong game.')

@section('content')

@if (isset($message))
<p>{{$message}}</p>
@endif

<a href="{{ route('newrecharge') }}"><button>Chuyển nữa</button></a>

@endsection
