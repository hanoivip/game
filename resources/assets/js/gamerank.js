$(document).ready(function() {
	function onLoadRank() {
		event.preventDefault();
		var url = $(this).attr('data-action');
		var updateId = $(this).attr('data-update-id')
		var template = $(this).attr('data-template')
		var extmpl = $('#gamerank-type option:selected').attr("data-ex-template")
		console.log(extmpl)
		var svname = $('#gamerank-svname').val()
		var type = $('#gamerank-type').val()
		var param = new FormData()
		param.append("svname", svname)
		param.append("type", type)
		param.append("template", extmpl ? extmpl : template)
		$.ajax({
            url: url,
            method: 'POST',
            contentType: 'application/x-www-form-urlencoded',
            data: new URLSearchParams(param).toString(),
            dataType : 'html',
            cache: false,
            processData: false,
            success:function(response)
            {
            	console.log(response)
            	$('#' + updateId).html(response)
            },
            error: function(response) {
            }
        })
	}
	$('#gamerank-type').on('change', onLoadRank)
	$('#gamerank-svname').on('change', onLoadRank)
})