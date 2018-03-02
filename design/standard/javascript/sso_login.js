$(document).ready(function() {

    $(".trigger-show-register-tab").click(showRegisterTab);

    // store last access URI, in case if login form is displayed non on login page (access denied)
    if( $(".login").length > 0 ) {
        ssoDebugLog("Login div detected.");

        if (document.URL.indexOf( '/user/login' ) === -1) {
            // we're not on the login page, so we've landed somewhere that has an embedded login form.
            //  Set the redirect URI, then forward through to the auto-login handling
            ssoDebugLog("We are not on the login page, set last access URI.");

            setLastAccessUri();
        } else {
            // we've just landed on the login page. Try and do the auto-login.
            ssoDebugLog("We are on the login page. Proceed to auto-login.");

            attemptAutoLogin();
        }
    }

    if (isCorsWithCredentialsSupported()) {

        $("#login-tab input[name=LoginButton]").click(function() {

            var loginForm = $("form#login-tab");

            showSpinner(loginForm);

            ajaxUserFormSubmit(loginForm);
            return false;
        }) ;


        $("#register-tab input[name=PublishButton]").click(function() {

            var registerForm = $("form#register-tab");

            // client-side validation to catch the obvious problems.
            var valid = registerForm.isValid(null, {}, true);
            if (!valid) {
                return false;
            }

            showSpinner(registerForm);

            // emarsys email opt in
            submitEmarsysNewsletterSignup(
                registerForm.attr('data-emarsys-signup-url'),
                $("#register-email").val(), // email
                null, // country
                $("#email-opt-in").is(':checked'), // opt in
                $("#first-name").val(), // first name
                $("#last-name").val(), // last name
                function()  { }, // success,
                function() { // complete
                    // chain through to new user signup
                    ajaxUserFormSubmit(registerForm);
                }
            );

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

    ssoDebugLog("ajaxUserFormSubmit: " + jform.attr("action"));

    $.ajax({
        method: "POST",
        url: jform.attr("action"),
        data: jform.serialize(),
        xhrFields: {
            withCredentials: true
        },
        success: function(data) {

            ssoDebugLog("Ajax success in ajaxUserFormSubmit().");

            var jdata = $(data);
            var warnings = jdata.find(".warning");

            if (warnings.length > 0) {
                ssoDebugLog("Warnings detected");

                showErrors(warnings, jform);
                hideSpinner(jform);

            } else {
                ssoDebugLog("No warnings detected. Success.");

                // No warnings. Success.
                var successUrl = jdata.find("#jwt-redirect").text();

                if (successUrl) {
                    ssoDebugLog("Redirecting to " + successUrl);

                    window.location.replace(successUrl);
                } else {
                    ssoDebugLog("Impossible state: Success on ajaxUserFormSubmit() but no success URL.");
                }
            }

        },
        error: function(xhr, status) {
            ssoDebugLog("Ajax failure on ajaxUserFormSubmit(). Status " + status);

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

function showAutologinSpinner() {

    ssoDebugLog("Showing autologin spinner. Hiding login form");

    $(".login").hide();
    $("#login-check").show();

}

function showLoginForm() {

    ssoDebugLog("Showing login form");

    $(".login").show();
    $("#login-check").hide();
}

function isCorsWithCredentialsSupported() {
    if ('withCredentials' in new XMLHttpRequest()) {
        ssoDebugLog("CORS with credentials is supported.");
        return true;
    } else {
        ssoDebugLog("CORS with credentials is NOT supported. Can't use AJAX.");
        return false;
    }
}

function attemptAutoLogin() {

    ssoDebugLog("attemptAutoLogin()");

    showAutologinSpinner();

    $.ajax({
        type: 'GET',
        url: identityProviderUrl + 'account/is_logged_in',
        jsonpCallback: 'jsonCallback',
        contentType: 'application/json',
        dataType: 'jsonp',
        timeout: 7000,
        success: function( json ) {
            ssoDebugLog("Ajax success in attemptAutoLogin().");

            if( json.is_logged_in ) {
                ssoDebugLog("CMS says we are logged in. Redirecting to login page.");

                window.location.replace(ssoLoginPageUrl);
            } else {
                ssoDebugLog("CMS says we NOT are logged in. Showing login form.");

                showLoginForm();
            }
        },
        error: function(xhr, status) {
            ssoDebugLog("Ajax failure in attemptAutoLogin(). Status: " + status + ". Showing login form.");
            showLoginForm();
        }
    });

}


function setLastAccessUri() {

    ssoDebugLog("setLastAccessUri()");

    showAutologinSpinner();

    $.ajax( {
        url: lastAccessUriUrl + "?uri=" + document.URL,
        success: function() {
            ssoDebugLog("Ajax success in setLastAccessUri(). Proceeding to autologin.");
            attemptAutoLogin();
        },
        error: function(xhr, status) {
            ssoDebugLog("Ajax failure in setLastAccessUri. Status: " + status + ". Showing login form.");
            showLoginForm();
        }
    } );
}

function showRegisterTab() {
    $("#profile-tab").tab('show');
}

function ssoDebugLog(message) {
    try {
        if (typeof(logSsoDebugMessages) !== 'undefined') {
            if (logSsoDebugMessages) {
                console.log(message);
            }
        }
    } catch(e) {}
}


