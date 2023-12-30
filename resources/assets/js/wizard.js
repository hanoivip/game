$(document).ready(function(){
	$('#wizard-button').hide()
	$('#wizard-loading').show()
	$('#wizard-refresh-roles').on('click', refreshRoles)
	$('#save-role').on('click', saveDefaultRole)
	$('#use-saved-role').on('change', reselectRole)
	
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
        	$('#wizard-svname').on('change', onServerChanged)
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
		var server = $('#svname').val()
		if (!role || !server) {
			$("#error-message").text("Game role/server must be selected!")
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
	
	function onRoleSelected() {
		event.preventDefault()
		console.log('on role selected')
		$('#wizard-button').show()
		$('#wizard-loading').hide()
		$('#save-role').show()
	}
	
	function onServerChanged() {
		$('#wizard-loading').show()
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
            	$('#role').on('change', onRoleSelected)
            },
            error: function(response) {
            	$('#wizard-loading').hide()
            }
        });
	}
	
	function refreshRoles() {
		$('#save-role').hide()
		$('#wizard-loading').show()
		event.preventDefault()
		var url = $(this).attr('data-action');
		var updateId = $(this).attr('data-update-id')
		var param = new FormData();  
		var svname = $('#wizard-svname').val()
		param.append("svname", svname)
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
            	$('#role').on('change', onRoleSelected)
            },
            error: function(response) {
            	$('#wizard-loading').hide()
            }
        });
	}
	
	function reselectRole() {
		$('#wizard-selected').hide()
		$('#wizard-reselect').show()
	}
	
	function saveDefaultRole() {
		$('#wizard-loading').show()
		event.preventDefault()
		var url = $(this).attr('data-action');
		var updateId = $(this).attr('data-update-id')
		var param = new FormData();  
		var svname = $('#wizard-svname').val()
		var role= $('#role').val()
		param.append("svname", svname)
		param.append("role", role)
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
            	$('#wizard-loading').hide()
            },
            error: function(response) {
            	$('#wizard-loading').hide()
            }
        });
	}
});