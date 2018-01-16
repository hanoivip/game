@extends('layouts.app')

@section('title', 'Mua vàng trong game.')

@section('content')

<p>Thông tin tài khoản:<p><br/>
@foreach ($balances as $bal)
<p>Loại tài khoản:</p>{{$bal->balance_type}} <br/>
<p>Số dư:</p>{{$bal->balance}} <br/>
@endforeach


{{ Form::open(['route' => 'doRecharge', 'method' => 'submit' ]) }}

{{ Form::label('Chọn máy chủ') }}
{{ Form::select('svname', $servers) }}

{{ Form::label('Chọn số tiền') }}
{{ Form::select('package', $packs) }}
            

{{ Form::submit('Nap the') }}
{{ Form::close() }}

@endsection
