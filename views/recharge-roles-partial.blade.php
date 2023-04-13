@if (!empty($roles))
    <p style="text-align: left;">Chọn nhân vật:</p>
    <select id="recharge-roles" name="roleid" style="width: 100%;">
    	@foreach ($roles as $roleid => $rolename)
    		<option value="{{ $roleid }}">{{ $rolename }}</option>
    	@endforeach
    </select>
@else
	<p style="text-align: left;">Bạn vẫn chưa có nhân vật nào!</p>
@endif
