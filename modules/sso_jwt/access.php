<?php

/**
 * @package SSOJWT
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    09 Oct 2014
 * */
$module          = $Params['Module'];
$serviceProvider = $Params['ServiceProvider'] === null ? SSOJWTServiceProviderHandler::getDefaultServiceProvider() : $Params['ServiceProvider'];
$handler         = SSOJWTServiceProviderHandler::get( $serviceProvider );
if( $handler instanceof SSOJWTServiceProviderHandler === false ) {
    return $module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
}

if( $handler->isSSOEnabled() === false ) {
    return $module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
}

$jwt = eZHTTPTool::instance()->getVariable( 'jwt', null );
if( $jwt === null ) {
    return $module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
}

// Parse token data
$key = $handler->getSharedKey();
try {
    $token = (array) JWT::decode( $jwt, $key );
} catch( Exception $e ) {
    $handler->error( $e->getMessage() );
}

// Validate token
try {
    $handler->validateToken( $token );
} catch( Exception $e ) {
    $handler->error( $e->getMessage() );
}

// Store token usage
$tokenUsage = new SSOJWTTokenUsage();
$tokenUsage->setAttribute( 'token_id', $token['token_id'] );
$tokenUsage->setAttribute( 'token_jwt', $jwt );
$tokenUsage->store();

// Get user
$http = eZHTTPTool::instance();
try {
    $user = $handler->getUser( $token );
} catch( Exception $e ) {
    $handler->error( $e->getMessage() );
}
$http->setSessionVariable( 'eZUserLoggedInID', $user->attribute( 'contentobject_id' ) );
eZContentObject::cleanupAllInternalDrafts( $user->attribute( 'contentobject_id' ) );

$currentUser = eZUser::currentUser();

$wwwDir = eZSys::wwwDir();

$cookiePath = $wwwDir != '' ? $wwwDir : '/';

if ( $currentUser->isLoggedIn() )
{
    // Only set the cookie if it doesnt exist. This way we are not constantly sending the set request in the headers.
    if ( !isset( $_COOKIE['is_logged_in'] ) || $_COOKIE['is_logged_in'] != 'true' )
    {
        setcookie( 'is_logged_in', 'true', 0, $cookiePath );
    }
}
    else if ( isset( $_COOKIE['is_logged_in'] ) )
{
        setcookie( 'is_logged_in', false, 0, $cookiePath );
}

$url = $http->sessionVariable( 'LastAccessesURI', '/' );
return $module->redirectTo( $url );
