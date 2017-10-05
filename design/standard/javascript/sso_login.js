$(document).ready(function() {

    // store last access URI, in case if login form is displayed non on login page (access denied)
    if( $(".login").length >= 0 &&  document.URL.indexOf( '/user/login' ) === -1 ) {
        attemptAutoLogin();
    }

    if (isCorsWithCredentialsSupported()) {

        $("input[name=LoginButton]").click(function() {

            var loginForm = $("form#login-tab");
            ajaxUserFormSubmit(loginForm);
            return false;
        }) ;


        $("input[name=PublishButton]").click(function() {

            var registerForm = $("form#register-tab");
            ajaxUserFormSubmit(registerForm);
            return false;
        }) ;

    }

});

function ajaxUserFormSubmit(jform) {

    // copy all ajax-specific values into the hidden fields
    $("input[data-sso-ajax-value]").each(function() {
        $(this).val($(this).attr("data-sso-ajax-value"));
    });

    // copy the ajax field names into the forms we're submitting via ajax
    $("input[data-sso-ajax-name]").each(function() {
        $(this).attr("name", $(this).attr("data-sso-ajax-name"));
    });


    // copy the ajax action URLs into the forms we're submitting via ajax
    $("form[data-sso-ajax-action]").each(function() {
        $(this).attr("action", $(this).attr("data-sso-ajax-action"));
    });

    showSpinner(jform);

    $.ajax({
        type: "POST",
        url: jform.attr("action"),
        data: jform.serialize(),
        xhrFields: {
            withCredentials: true
        },
        success: function(data) {

            var jdata = $(data);
            var warnings = jdata.find(".warning");

            if (warnings.length > 0) {
                showErrors(warnings, jform);
                hideSpinner(jform);

            } else {
                // No warnings. Success.
                var successUrl = jdata.find("#jwt-redirect").text();

                if (successUrl) {
                    window.location.replace(successUrl);
                }
            }

        },
        error: function() {
            hideSpinner(jform);
        }

    });

}

function showErrors(errorList, containerForm) {

    var errorContainer = containerForm.find('.error-container');
    errorContainer.empty(errorContainer);
    errorContainer.append(errorList);
}

function showSpinner(containerForm) {
    containerForm.find('input[type="submit"]').hide();
    containerForm.find('.spinner').show();
}

function hideSpinner(containerForm) {
    containerForm.find('input[type="submit"]').show();
    containerForm.find('.spinner').hide();
}

function redirectToLoginPage() {
    // loginPageUrl must be defined in the surrounding page
    window.location.replace(loginPageUrl);
}

function isCorsWithCredentialsSupported() {
    if ('withCredentials' in new XMLHttpRequest()) {
        return true;
    } else {
        return false;
    }

}

function attemptAutoLogin() {

    $(".login").hide();
    $("#login-check").show();


    $.ajax( {
        url: lastAccessUriUrl + "?uri=" + document.URL,
        success: function() {
            $.ajax({
                type: 'GET',
                url: identityProviderUrl + 'account/is_logged_in?XDEBUG_SESSION_START=PHPSTORM',
                jsonpCallback: 'jsonCallback',
                contentType: 'application/json',
                dataType: 'jsonp',
                success: function( json ) {
                    if( json.is_logged_in ) {
                        window.location = identityProviderUrl + 'sso_jwt/login/pt';
                    } else {
                        redirectToLoginPage();
                    }
                },
                error: function(xhr, status) {
                    redirectToLoginPage();
                }
            });
        }
    } );

}
