<?php

/**
 * A streamlined, Ajax-friendly registration handler
*/

$errors = array();

$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();

$newFieldValues = $http->postVariable('new_field_values');

$firstName = $newFieldValues['first_name'];
$lastName = $newFieldValues['last_name'];
$email = $newFieldValues['email'];
$password = $newFieldValues['password'];
$confirmPassword = $newFieldValues['confirm_password'];

$redirectUri = $_POST['RedirectAfterUserRegister'];

// TODO reCAPTCHA


// TODO i18N
requiredField($firstName, "Please provide your First Name.", $errors);
requiredField($lastName, "Please provide your Last Name.", $errors);
requiredField($email, "Please provide your Email Address.", $errors);
requiredField($password, "Please provide a password.", $errors);
requiredField($confirmPassword, "Please confirm your password.", $errors);

if ($email) {
    if (!eZMail::validate($email)) {
        $errors[] = "Not a valid email address";
    }
}

if (eZUser::fetchByEmail($email)) {
    $errors[] = "Email address has already been registered.";
}

if ($password != $confirmPassword) {
    $errors[] = "Passwords must match.";
}


if (count($errors) > 0) {
    // show errors, redirect back to registration page
    $tpl->setVariable("warnings", $errors); // tpl field is called 'warnings' for backward compatibility
    $Result['content']  = $tpl->fetch('design:user/register');
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
    $parentNodeId = $ini->variable('UserSettings', 'DefaultUserPlacement');

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

        $userAccount->loginCurrent();

        // TODO confirmation email

        $Module->redirectTo($redirectUri ?: '/');
    } else {
        // failed to create user
        $errors[] = "Failed to create user.";

        $tpl->setVariable("warnings", $errors); // tpl field is called 'warnings' for backward compatibility
        $Result['content']  = $tpl->fetch('design:user/register');
    }
}

function requiredField($field, $errorMessage, &$errors) {
    if (!$field || trim($field) == "") {
        $errors[] = $errorMessage;
    }
}