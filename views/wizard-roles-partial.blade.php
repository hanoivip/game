@if (!empty($roles))
    <select id="role" name="role">
    	<option value="">Choose your role</option>
    	@foreach ($roles as $roleid => $rolename)
    		<option value="{{ $roleid }}">{{ $rolename }}</option>
    	@endforeach
    </select>
@else
	<p style="text-align: left;">You still have no role!</p>
@endif
