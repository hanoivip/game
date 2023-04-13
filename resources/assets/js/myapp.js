$.ajaxSetup({
	headers: {
		'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
		'Access-Token': $('meta[name="access-token"]').attr('content'),
	}
});