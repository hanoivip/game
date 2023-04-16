/**
 * TODO: SnowBoard
 */
$(document).ready(function(){
	$('#newrecharge-svname').on('change', function () {
		event.preventDefault();
		var url = $(this).attr('data-action')
		var template = $(this).attr('data-template')
		var updateId = $(this).attr('data-update-id')
		//var data = $(this).attr('data-request-data')
		var param = new FormData();  
		param.append("svname", this.value)
		param.append("template", template)
		//param.append("data", data)
		$.ajax({
            url: url,
            method: 'POST',
            dataType : "html",
            contentType: 'application/x-www-form-urlencoded',
            cache: false,
            data: new URLSearchParams(param).toString(),
            processData: false,
            success:function(response)
            {
            	console.log(response)
            	$('#' + updateId).html(response)
            	$('#newrecharge-roles').on('change', function () {
					event.preventDefault();
					var url = $(this).attr('data-action')
					var template = $(this).attr('data-template')
					var updateId = $(this).attr('data-update-id')
					var param = new FormData();  
					param.append("svname", this.value)
					param.append("template", template)
					$.ajax({
			            url: url,
			            method: 'POST',
			            dataType : "html",
			            contentType: 'application/x-www-form-urlencoded',
			            cache: false,
			            data: new URLSearchParams(param).toString(),
			            processData: false,
			            success:function(response)
			            {
			            	$('#' + updateId).html(response)
			            },
			            error:function(response) {
			            	
			            }
					})
				})
            },
            error: function(response) {
            	$("#error-message").text(response)
        		$("#error-message").show()
            }
        })
	})
	$('#btn-newrecharge').on('click', function () {
		$("#error-message").text('')
		$("#error-message").hide()
		console.log('xxxxxx')
		var role = $('#newrecharge-roles').val()
		if (!role) {
			$("#error-message").text('Role must be selected')
			$("#error-message").hide()
			event.preventDefault()
		}
	})
	$('#newrecharge-form1').on('submit', function () {
		event.preventDefault();
		$("#error-message").text('')
		$("#error-message").hide()
		var url = $(this).attr('data-action')
		$.ajax({
            url: url,
            method: 'POST',
            dataType : "html",
            contentType: 'application/x-www-form-urlencoded',
            cache: false,
            data: $(this).serialize(),
            processData: false,
            success:function(response)
            {
            	console.log(response)
            	$(response).appendTo('body').modal();
            },
            error: function(response) {
            	$("#error-message").text(response)
        		$("#error-message").show()
            }
        })
	})
})