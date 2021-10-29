@extends('hanoivip::admin.layouts.admin')

@section('title', 'Doanh số')

@section('content')

<a href="{{route('ecmin.newrecharge.statsToday')}}">Doanh số trong ngày</a>

<a href="{{route('ecmin.newrecharge.statsMonth')}}">Doanh số trong tháng</a>

<form method="post" action="{{route('ecmin.newrecharge.statsByTime')}}">
	{{csrf_field()}}
	<input type="date" name="start_time" id="start_time"/>
	<input type="date" name="end_time" id="end_time"/>
	<button type="submit">OK</button>
</form>
@endsection