@extends('hanoivip::layouts.app')

@section('title', 'Wizard roles..')

@push('scripts')
    <script src="/js/wizard.js"></script>
@endpush

@section('content')

<form data-action="{{ route($next) }}" id="wizard">
	<br/>
	<div id="wizard-servers-div"></div><br/>
	<div id="wizard-roles-div"></div><br/>
	<button type="submit" style="width: 100%;" class="btn btn-primary" id="btn-next" data-request-loading="wizard-loading">Next</button>
	<img src="/img/loading.gif" style="display: none; width: 32px; height: 32px;" id="wizard-loading"/>
</form>


@endsection
