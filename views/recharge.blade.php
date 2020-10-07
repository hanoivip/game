@extends('hanoivip::layouts.web')

@section('title', 'Mua vàng trong game.')

@section('content')

@if (!empty($balances))
    <p>Thông tin tài khoản:<p><br/>
    @foreach ($balances as $bal)
    <p>Loại tài khoản:</p>{{$bal->balance_type}} <br/>
    <p>Số dư:</p>{{$bal->balance}} <br/>
    @endforeach
@else
	<p>Chưa có xu nào trong tk!</p>
@endif


<form method="post" action="{{ route('doRecharge') }}">
{{ csrf_field() }}
<input type="hidden" name="tid" value="{{$tid}}"/>
	<p>Chọn máy chủ:</p>
	<select id="svname" name="svname"
		onchange="document.location.href='{{ route('recharge.role') }}?svname=' + this.value">
		@foreach ($servers as $sv)
			@if (isset($selected) && $sv->name == $selected)
				<option value="{{ $sv->name }}" selected>{{ $sv->title }}</option>
			@else
				<option value="{{ $sv->name }}">{{ $sv->title }}</option>
			@endif
		@endforeach
	</select>
	<p>Chọn số tiền:</p>
	<select id="package" name="package">
		@foreach ($packs as $p)
			<option value="{{ $p->code }}">{{ $p->title }}</option>
		@endforeach
	</select>
	@if (!empty($roles))
        <p>Chọn nhân vật:</p>
        <select id="roleid" name="roleid">
        	@foreach ($roles as $roleid => $rolename)
        		<option value="{{ $roleid }}">{{ $rolename }}</option>
        	@endforeach
        </select>
    @else
    	<p>Chưa có nhân vật nào trong sv này!</p>
    @endif
	<a href="#" onclick="document.location.href='{{ route('recharge.role') }}?svname=' + document.getElementById('svname').value">Làm mới ds nv</a>
	<br/>
	<button type="submit">Chuyển Xu</button>
</form>


@endsection
