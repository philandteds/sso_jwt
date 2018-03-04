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

//$url = $http->sessionVariable( 'LastAccessesURI', '/' );
//return $module->redirectTo( $url );

// the subsequent template will redirect the user back to the variable stored in lastAccessUri sessionStorage
$tpl = eZTemplate::factory();

$Result            = array();
$Result['content'] = $tpl->fetch( 'design:user/redirect_to_last_access_uri.tpl' );
$Result['path']    = array();
