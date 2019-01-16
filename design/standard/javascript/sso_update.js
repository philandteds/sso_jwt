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
	//hide password fields on basic load
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
			// stop the default _ezpassword being used
			$("input[name='" + originalPasswordNames[i] + "']").prop('value', null);
			$("input[name='" + originalPasswordNames[i] + "']").prop('name', changePasswordNames[i]);
		}
		$('#change_password').hide();
		var redirectField = '<input type="hidden" name="redirect" value="' + originalUrl + '"></input>';
		$('#editform').append(redirectField);

		// initially disable submit button for validation
		$('input[name="PublishButton"]').addClass('disabledButton');
	});
	// basic validation and submit
	$('input[name="PublishButton"]').click(function(e) {
		validatePasswordForm();
		if ($(this).hasClass('disabledButton')) {
			e.preventDefault();
		}
	});

	// validate on input changes
	$("input[type='password']").change(function() {
		validatePasswordForm();
	});

	function validatePasswordForm() {
		// remove existing errors as they will be added back if they persist
		$('.help-block.has-error').remove();

		var noErrors = 1;
		var password = $('input[name="Password"]');
		var confirmPassword = $('input[name="ConfirmPassword"]');
		if (!password.val()) {
			noErrors = 0;
			password.after('<p class="help-block has-error">This field is required.</p>');
		}
		if (!confirmPassword.val()) {
			noErrors = 0;
			confirmPassword.after('<p class="help-block has-error">This field is required.</p>');
		}
		if (password.val() != confirmPassword.val()) {
			noErrors = 0;
			password.after('<p class="help-block has-error">Passwords must match.</p>');
		}
		if (noErrors) {
			$('input[name="PublishButton"]').removeClass('disabledButton');
		}
	}
});

