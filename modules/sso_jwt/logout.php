<?php

/**
 * @package SSOJWT
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    14 Oct 2014
 * */
$module          = $Params['Module'];
$serviceProvider = $Params['ServiceProvider'] === null ? SSOJWTServiceProviderHandler::getDefaultServiceProvider() : $Params['ServiceProvider'];
$handler         = SSOJWTServiceProviderHandler::get( $serviceProvider );
if( $handler instanceof SSOJWTServiceProviderHandler === false ) {
    return $module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
}

$http = eZHTTPTool::instance();
if(
    $http->hasGetVariable( 'message' ) && $http->hasGetVariable( 'kind' ) && $http->getVariable( 'kind' ) == 'error'
) {
    SSOJWTLogItem::create( $handler->getServiceProvider(), null, $http->getVariable( 'message' ) );
}

return $module->redirectTo( 'user/logout' );
