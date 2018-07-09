@extends('hanoivip::layouts.web')

@section('title', 'Túi đồ web')

@section('content')

@if (!empty($error))
	<div class="alert alert-error">
        <p>{{ $error }}</p>
    </div>
@else

@if (empty($items))
	<div class="alert alert-warn">
        <p>Chưa có vật phẩm nào trong túi</p>
    </div>
@elseif (empty($servers))
	<div class="alert alert-warn">
        <p>Các máy chủ đang được chuẩn bị. Mời thử lại sau.</p>
    </div>
@else

<form method="post" action="{{ route('bag.exchage') }}">
	{{ csrf_field() }}
	<br/>
	Danh sách vật phẩm hiện có:
	<select id="itemId" name="itemId">
		@foreach ($items as $i)
			<option value="{{ $i->item_id }}"> {{ $i->item_title }} còn {{ $i->item_count }} cái!</option>
		@endforeach
	</select>
	<br/>
	Chọn server muốn chuyển
	<select id="svname" name="svname">
		@foreach ($servers as $sv)
			<option value="{{ $sv->name }}">{{ $sv->title }}</option>
		@endforeach
	</select>
	<input type="hidden" id="count" name="count" value="1"/>
	<br/>
	Chọn nhân vật
	<input id="roleid" name="roleid" />
	<br/>
	<button type="submit">Chuyển</button>
</form>
@endif


@endif

@endsection