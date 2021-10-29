@extends('hanoivip::admin.layouts.admin')

@section('title', 'New Recharge Admin')

@section('content')

<a href="{{route('ecmin.newrecharge.receipt')}}">Tìm kiếm hoá đơn</a>
<br/>
<a href="{{route('ecmin.newrecharge.stats')}}">Thống kê doanh số</a>

@endsection