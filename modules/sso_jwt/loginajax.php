<?php

/**
 * @package SSOJWT
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    09 Oct 2014
 * */
$module          = $Params['Module'];
$serviceProvider = $Params['ServiceProvider'] ?: SSOJWTServiceProviderHandler::getDefaultServiceProvider();
$siteAccessPrefix = $Params['SiteAccessPrefix'];


$handler         = SSOJWTServiceProviderHandler::get( $serviceProvider );
if( $handler instanceof SSOJWTServiceProviderHandler === false ) {
    return $module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
}

$handler->init();
$token = $handler->getToken();
$key   = $handler->getSharedKey();

$handler->setUrlPrefix($siteAccessPrefix);

$jwt   = JWT::encode( $token, $key );
$url   = $handler->getEndpointURL( $jwt );

SSOJWTLogItem::create( $handler->getServiceProvider(), 'Token generated: ' . $jwt );

$tpl = eZTemplate::factory();
$tpl->setVariable( 'jwt', $jwt );
$tpl->setVariable( 'jwt_redirect', $url );

$Result['content']         = $tpl->fetch( 'design:user/jwt_success.tpl' );
//print ($data);
//eZExecution::cleanExit();
