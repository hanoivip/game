@extends('hanoivip::layouts.app')

@section('title', 'Payment success')

@section('content')

<p>{{__('hanoivip.game::newrecharge.success')}}</p>
<a href="{{ route('newrecharge') }}"><button>Chuyển nữa</button></a>

@endsection
