@if (!empty($servers))
	<select id="svname" name="svname" style="width: 100%;" data-action="{{ route('game.roles') }}" 
		data-update-id="wizard-roles-div">
		<option value="">Choose server</option>
		@foreach ($servers as $sv)
			<option value="{{ $sv->name }}">{{ $sv->title }}</option>
		@endforeach
	</select>
@else
	<p>Still have no server</p>
	<a href="#">Refresh</a>
@endif