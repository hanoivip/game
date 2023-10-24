$(document).ready(function(){
	$('#recharge').on('submit', function (event) {
		event.preventDefault();
		var role = $('#recharge-roles').val()
		if (!role) {
			$("#error-message").text("Game role must be selected!")
			return
		}
		var url = $(this).attr('data-action');
		$("#btn-recharge").hide()
		$("#message").text("")
		$("#message").hide()
		$("#error-message").text("")
		$("#error-message").hide()
		$('#recharge-loading').show()
		$.ajax({
            url: url,
            method: 'POST',
            data: $(this).serialize(),
            cache: false,
            processData: false,
            success:function(response)
            {
            	//console.log(JSON.stringify(response))
            	if (response.message) {
            		$("#message").text(response.message)
            		$("#message").show()
            		$("#recharge-balance-refresh").click()
            	}
            	if (response.error_message) {
            		$("#error-message").text(response.error_message)
            		$("#error-message").show()
            	}
            	$("#btn-recharge").show()
            	$('#recharge-loading').hide()
            },
            error: function(response) {
            	$("#error-message").text(response)
        		$("#error-message").show()
        		$("#btn-recharge").show()
        		$('#recharge-loading').hide()
            }
        });
	});
	$('#recharge-svname').on('change', function () {
		event.preventDefault();
		var url = $(this).attr('data-action');
		var updateId = $(this).attr('data-update-id')
		var param = new FormData();  
		param.append("svname", this.value)
		$('#recharge-loading').show()
		$('#' + updateId).html('')
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
            	$('#recharge-loading').hide()
            },
            error: function(response) {
            	$("#error-message").text(response)
        		$("#error-message").show()
        		$('#recharge-loading').hide()
            }
        });
	})
	$('#recharge-refresh-roles').on('click', function () {
		event.preventDefault();
		$("#error-message").text("")
		$("#error-message").hide()
		
		var url = $(this).attr('data-action');
		var svname = $('#recharge-svname').val()
		var param = new FormData();  
		param.append("svname", svname)
		var updateId = $(this).attr('data-update-id')
		$('#recharge-loading').show()
		$('#' + updateId).html()
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
            	$('#recharge-loading').hide()
            },
            error: function(response) {
            	$("#error-message").text(response)
        		$("#error-message").show()
        		$('#recharge-loading').hide()
            }
        });
	})
	$('#recharge-balance-refresh').on('click', function () {
			event.preventDefault();
			var url = $(this).attr('data-action')
			var updateId = $(this).attr('data-update-id')
			$.ajax({
	            url: url,
	            method: 'POST',
	            contentType: 'application/x-www-form-urlencoded',
	            dataType : 'html',
	            cache: false,
	            processData: false,
	            success:function(response)
	            {
	            	console.log(response)
	            	$('#' + updateId).html(response)
	            },
	            error: function(response) {
	            	$("#error-message").text(response)
	        		$("#error-message").show()
	            }
	        });
		})
});