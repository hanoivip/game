@extends('hanoivip::layouts.app')

@section('title', 'Wizard roles..')

@push('scripts')
    <script src="/js/wizard.js"></script>
@endpush

@section('content')

<script>
var savedRole = {{ $current->role }}
var savedServer = "{{ $current->server }}"
</script>

<form data-action="{{ route($next) }}" id="wizard">
	{{ csrf_field() }}
	@if (!empty($current))
		<div id="wizard-selected">
			<p>Saved target role</p>
			<p>Role Name/ID: {{ $current->role }} </p>
			<p>Server name: {{ $current->server }}</p>
			<label>Agree to use this role for further steps</label>
			<input type="checkbox" checked="checked" name="use-saved-role" id="use-saved-role"/>
		</div>
	@endif
	<br/>
	<div id="wizard-reselect">
    	<div id="wizard-servers-div"></div><br/>
    	<div id="wizard-roles-div"></div><br/>
    	<a id="save-role" data-action="{{ route('wizard.save') }}">Save this role as default</a>
	</div>
	<button type="submit" style="width: 100%;" class="btn btn-primary" id="btn-next" data-request-loading="wizard-loading">Next</button>
	<img src="/img/loading.gif" style="display: none; width: 32px; height: 32px;" id="wizard-loading"/>
</form>


@endsection
