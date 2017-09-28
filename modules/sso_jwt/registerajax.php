<?php

/**
 * A streamlined, Ajax-friendly registration handler
*/

$errors = array();

$module = $Params['Module'];
$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();

$newFieldValues = $http->postVariable('new_user_data');

$firstName = $newFieldValues['first_name'];
$lastName = $newFieldValues['last_name'];
$email = $newFieldValues['email'];
$password = $newFieldValues['password'];
$confirmPassword = $newFieldValues['password_confirm'];

$redirectUri = $newFieldValues['RedirectAfterUserRegister'];

$recaptcha = $http->postVariable('g-recaptcha-response');

// TODO i18N
requiredField($firstName, "first_name", "Please provide your First Name.", $errors);
requiredField($lastName, "last_name", "Please provide your Last Name.", $errors);
requiredField($email, "email", "Please provide your Email Address.", $errors);
requiredField($password, "password", "Please provide a password.", $errors);
requiredField($confirmPassword, "password_confirm", "Please confirm your password.", $errors);
requiredField($recaptcha, "recaptcha", "Please confirm that you're not a robot.", $errors);

if ($email) {
    if (!eZMail::validate($email)) {
        addError("email", "Not a valid email address", $errors);
    } else {

        if (eZUser::fetchByEmail($email)) {
            addError("email", "Email address has already been registered.", $errors);
        }
    }
}

if ($password != $confirmPassword) {
    addError("password", "Passwords must match.", $errors);
}

if ($recaptcha && !validateRecaptcha($recaptcha)) {
    addError("recaptcha", "Please prove that you're not a robot", $errors);
}

if (count($errors) > 0) {
    // show errors, redirect back to registration page
    errorsToTemplate($errors, $tpl);
    $Result['content']  = $tpl->fetch('design:user/register.tpl');
} else {

    // no errors, attempt to save
    $login = $email;

    $passwordHash = eZUser::createHash( $login, $password, eZUser::site(), eZUser::hashType() );
    $userAccount = $login . '|' . $email . '|' . $passwordHash . '|' . eZUser::passwordHashTypeName( eZUser::hashType() );

    $attributes = array(
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'user_account' => $userAccount
    );

    $ini = eZIni::instance();
    $creatorId = $ini->variable('UserSettings', 'UserCreatorID');
    $parentNodeId = eZURLAliasML::fetchNodeIDByPath('/Users/Members');

    $behaviour = new ezpContentPublishingBehaviour();
    $behaviour->disableAsynchronousPublishing = true;
    $behaviour->isTemporary = true;
    ezpContentPublishingBehaviour::setBehaviour( $behaviour );

    $user = eZContentFunctions::createAndPublishObject(
        array(
            'class_identifier' => 'user',
            'parent_node_id' => $parentNodeId,
            'creator_id' => $creatorId,
            'attributes' => $attributes
        )
    );

    if ($user) {
        $dataMap = $user->dataMap();
        $userAccount = $dataMap['user_account']->content();

        /** @var eZUser $userAccount */
        $userAccount->loginCurrent();

        // perform post-registration actions (emails etc)

        // TODO establish how we send emails. Looks like it's done through Campaign Monitor?

//        $userID = $user->attribute( 'id' );
//        $operationResult = eZOperationHandler::execute( 'user', 'register', array( 'user_id' => $userID ) );

        $module->redirectTo($redirectUri ?: '/');
    } else {
        // failed to create user
        addError("user", "Failed to create user.", $errors);

        errorsToTemplate($errors, $tpl);
        $Result['content']  = $tpl->fetch('design:user/register.tpl');
    }
}

function requiredField($field, $fieldName,  $errorMessage, &$errors) {
    if (!$field || trim($field) == "") {
        addError($fieldName, $errorMessage, $errors);
    }
}

function addError($fieldName, $errorMessage, &$errors) {

    $errors[] = array(
        'name' => $fieldName,
        'description' => $errorMessage
    );
}

/**
 * Reformat the errors array into the format expected by the register.tpl template
 *
 * @param $errors array list of errors ex the addError() function
 * @param $tpl eZTemplate
 */
function errorsToTemplate($errors, &$tpl) {

    $validationResults = array(
        'processed' => true,
        'attributes'=> $errors
    );

    $tpl->setVariable("validation", $validationResults);
}

function validateRecaptcha($recaptchaResponse) {

    $http = eZHTTPTool::instance();
    $latestSuccessfulRecaptcha = $http->sessionVariable("latest-successful-recaptcha", false);

    if ($latestSuccessfulRecaptcha === $recaptchaResponse) {
        // no need to check again, the recaptcha is known good.
        return true;
    }

    $ini  = eZINI::instance();
    $data = array(
        'secret'   => $ini->variable( 'ReCaptcha', 'SecrectKey' ), // [sic]
        'response' => $recaptchaResponse,
        'remoteip' => eZSys::clientIP()
    );

    $verifyURL = 'https://www.google.com/recaptcha/api/siteverify?';
    foreach( $data as $param => $value ) {
        $verifyURL .= $param . '=' . $value . '&';
    }
    $verifyURL = trim( $verifyURL, '&' );

    $curl     = curl_init( $verifyURL );
    curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, false );
    curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
    $response = curl_exec( $curl );
    $response = json_decode( $response );
    curl_close( $curl );

    if( is_object( $response ) && isset( $response->success ) && (bool) $response->success ) {

        $http->setSessionVariable("latest-successful-recaptcha", $recaptchaResponse);
        return true;
    }


    return false;
}