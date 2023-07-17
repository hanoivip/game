@foreach ($servers as $sv)
	<option value="{{ $sv->name }}">{{ $sv->title }}</option>
@endforeach