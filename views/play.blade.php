@extends('hanoivip::layouts.app')

@section('title', 'Chơi game')

@section('content')

<iframe src="{{ $playuri }}"></iframe>
							
@endsection