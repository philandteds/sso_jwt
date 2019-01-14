$(document).ready(function() {
	var changePasswordNames = ['Password', 'ConfirmPassword'];
	var originalPasswordNames = [];
	var originalUrl = $('#editform').prop('action');
	var changePasswordUrl = $('#PortalChangePassword').val();
	var currentUrl = $(location).attr('href');
	var i = 0;
	// fetch original password names
	$("input[type='password']").each(function() {
		originalPasswordNames[i] = $(this).prop('name');
		i++;
	});
	//switch password names to names expected by changepassword.php onj portal
	for (i = 0; i < originalPasswordNames.length; i++) {
		$("input[name='" + originalPasswordNames[i] + "']").parent().hide();
	}
	// switch out the form for a portal change password form
	$('#change_password').click(function() {
		$('#editform').prop('action', changePasswordUrl);
		$('div.element').show();
		$("label.first_name").hide();
		$("label.last_name").hide();
		$("input[type='text'").each(function() {
			$(this).hide();
		});
		for (i =0; i < originalPasswordNames.length; i++) {
			$("input[name='" + originalPasswordNames[i] + "']").prop('name', changePasswordNames[i]);
		}
		$('#change_password').hide();
		var redirectField = '<input type="hidden" name="redirect" value="' + originalUrl + '"></input>';
		$('#editform').append(redirectField);
	});
});
