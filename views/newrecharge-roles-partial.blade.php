@if (!empty($roles))
    <select id="newrecharge-roles" name="role" style="width: 100%;" data-action="{{ route('newrecharge.shop') }}"
    	data-template="hanoivip::newrecharge-shop-partial" data-update-id="newrecharge-shop-div">
    	<option value="">Choose your role</option>
    	@foreach ($roles as $roleid => $rolename)
    		<option value="{{ $roleid }}">{{ $rolename }}</option>
    	@endforeach
    </select>
@else
	<p style="text-align: left;">You still have no role!</p>
@endif
