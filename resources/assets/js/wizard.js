$(document).ready(function(){
	$('#btn-next').hide()
	$('#wizard-loading').show()
	
	var form = $('wizard-form')
	var url = '/api/server/list'
	var param = new FormData();  
	param.append("template", 'hanoivip::wizard-serverlist-partial')
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
        	$('#wizard-servers-div').html(response)
        	$('#svname').on('change', onServerChange)
        	$('#wizard-loading').hide()
        },
        error: function(response) {
        	$('#wizard-loading').hide()
        }
    })
	
	$('#wizard').on('submit', function (event) {
		event.preventDefault();
		$('#wizard-loading').show()
		var role = $('#role').val()
		if (!role) {
			$("#error-message").text("Game role must be selected!")
			return
		}
		var url = $(this).attr('data-action');
		$.ajax({
            url: url,
            method: 'POST',
            data: $(this).serialize(),
            cache: false,
            processData: false,
            success:function(response)
            {
            	console.log(response)
            	if (response.error == 0) {
            	      window.location.replace(response.data.url);
            	}
            	$('#wizard-loading').hide()
            },
            error: function(response) {
            	$('#wizard-loading').hide()
            }
        })
	})
	
	function onRoleChange(e) {
		var role = $('#role').val()
		if (role) $('#btn-next').show()
	}
	
	
	function onServerChange() {
		$('#wizard-loading').show()
		$('#btn-next').hide()
		event.preventDefault()
		var url = $(this).attr('data-action');
		var updateId = $(this).attr('data-update-id')
		var param = new FormData();  
		param.append("svname", this.value)
		param.append("template", 'hanoivip::wizard-roles-partial')
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
            	$('#wizard-loading').hide()
            	$('#role').on('change', onRoleChange)
            },
            error: function(response) {
            	$('#wizard-loading').hide()
            }
        });
	}
});