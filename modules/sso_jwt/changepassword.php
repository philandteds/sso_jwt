<?php

/**
 * Script to update the portal password
 */

$errors = array();
$ini = eZINI::instance();
$currentUser = eZUser::currentUser();

$module = $Params['Module'];
$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();

$password = $http->postVariable('Password');
$confirmPassword = $http->postVariable('ConfirmPassword');
$redirectUri = $http->postVariable('redirect');

if ($password != $confirmPassword) {
    addError("password", "Passwords must match.", $errors);
}


if (count($errors) > 0) {
    // show errors, redirect back to edit page
    errorsToTemplate($errors, $tpl);
    $Result['content']  = $tpl->fetch('design:content/edit.tpl');
} else {

    // no errors, attempt to save
    if ($currentUser) {
        $id = $currentUser->attribute('contentobject_id');
        $login = $currentUser->attribute('login');
        $email = $currentUser->attribute('email');
        $currentUser->setInformation($id, $login, $email, $password, $confirmPassword);
        $result = eZPersistentObject::storeObject($currentUser);
        if ($result) {
            $module->redirectTo($redirectUri ?: '/');
        }
    } else {
        // failed to create user
        addError("user", "Failed to change password.", $errors);
    }
}

if ($errors) {
    errorsToTemplate($errors, $tpl);
    $Result['content']  = $tpl->fetch('design:content/edit.tpl');
} else {
    $module->redirectTo($redirectUri ?: '/');
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
