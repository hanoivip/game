@extends('hanoivip::layouts.app')

@section('title', 'Payment pending, need more actions')

@section('content')

<p>{{__('hanoivip::newrecharge.pending')}}</p>

<a href="{{ route('newrecharge.refresh', ['trans' => $trans]) }}"><button>Cập nhật</button></a>

<a href="{{ route('newrecharge') }}"><button>Chuyển nữa</button></a>

@endsection
