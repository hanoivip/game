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
            	if (response.message) {
            		$("#message").text(response.message)
            		$("#message").show()
            		$("#recharge-balance-refresh").click()
            	}
            	if (response.error_message) {
            		$("#error-message").text(response.message)
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
		$.ajax({
            url: url,
            method: 'POST',
            contentType: 'application/x-www-form-urlencoded',
            data: new URLSearchParams(param).toString(),
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
	$('#recharge-refresh-roles').on('click', function () {
		event.preventDefault();
		$("#error-message").text("")
		$("#error-message").hide()
		
		var url = $(this).attr('data-action');
		var svname = $('#recharge-svname').val()
		var param = new FormData();  
		param.append("svname", svname)
		var updateId = $(this).attr('data-update-id')
		$.ajax({
            url: url,
            method: 'POST',
            contentType: 'application/x-www-form-urlencoded',
            data: new URLSearchParams(param).toString(),
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
	$('#recharge-balance-refresh').on('click', function () {
			event.preventDefault();
			var url = $(this).attr('data-action');
			$.ajax({
	            url: url,
	            method: 'POST',
	            contentType: 'application/x-www-form-urlencoded',
	            cache: false,
	            processData: false,
	            success:function(response)
	            {
	            	console.log(response)
	            	if (response.error == 0) {
	            		$('#recharge-balance').text(response.data.balances[0].balance)
	            	}
	            },
	            error: function(response) {
	            	$("#error-message").text(response)
	        		$("#error-message").show()
	            }
	        });
		})
});