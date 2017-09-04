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

//$handler->init();
//
//if( eZUser::currentUser()->isLoggedIn() === false ) {
//    SSOJWTLogItem::create( $handler->getServiceProvider(), 'Redirect to login page' );
//    eZHTTPTool::instance()->setSessionVariable( 'RedirectAfterLogin', 'sso_jwt/login/' . $handler->getServiceProvider() );
//
//    $url = 'user/login?service_provider=' . $handler->getServiceProvider();
//    eZURI::transformURI( $url );
//
//    header( 'Location: ' . $url );
//    eZExecution::cleanExit();
//}

$token = $handler->getToken();
$key   = $handler->getSharedKey();
$jwt   = JWT::encode( $token, $key );
$url   = $handler->getEndpointURL( $jwt );

SSOJWTLogItem::create( $handler->getServiceProvider(), 'Token generated: ' . $jwt );

$tpl = eZTemplate::factory();
$tpl->setVariable( 'jwt', $jwt );
$tpl->setVariable( 'jwt_redirect', $url );

$Result['content']         = $tpl->fetch( 'design:user/jwt_success.tpl' );
//print ($data);
//eZExecution::cleanExit();
