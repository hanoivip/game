@extends('hanoivip::admin.layouts.admin')

@section('title', 'New Recharge Admin')

@section('content')

<a src="{{route('ecmin.newrecharge.receipt')}}">Tìm kiếm hoá đơn</a>

<a src="{{route('ecmin.newrecharge.stats')}}">Thống kê doanh số</a>

@endsection