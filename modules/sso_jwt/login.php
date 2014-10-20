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

$handler->init();

if( eZUser::currentUser()->isLoggedIn() === false ) {
    SSOJWTLogItem::create( $handler->getServiceProvider(), 'Redirect to login page' );
    eZHTTPTool::instance()->setSessionVariable( 'RedirectAfterLogin', 'sso_jwt/login/' . $handler->getServiceProvider() );
    return $module->redirectTo( 'user/login' );
}

$token = $handler->getToken();
$key   = $handler->getSharedKey();
$jwt   = JWT::encode( $token, $key );
$url   = $handler->getEndpointURL( $jwt );

SSOJWTLogItem::create( $handler->getServiceProvider(), 'Token generated: ' . $jwt );

header( 'Location: ' . $url );
eZExecution::cleanExit();
