@extends('hanoivip::layouts.app')

@section('title', 'Payment failure')

@section('content')

@if (isset($message))
<p>{{$message}}</p>
@endif

<a href="{{ route('newrecharge') }}"><button>Chuyển nữa</button></a>

@endsection
