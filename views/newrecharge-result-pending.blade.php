@extends('hanoivip::layouts.app')

@section('title', 'Payment pending, need more actions')

@section('content')

<p>{{__('hanoivip.game::newrecharge.pending')}}</p>

<a href="{{ route('newrecharge.refresh', ['trans' => $trans]) }}"><button>Refresh</button></a>

<a href="{{ route('newrecharge') }}"><button>Pay more</button></a>

@endsection
