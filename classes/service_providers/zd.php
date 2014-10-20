<?php

/**
 * @package SSOJWT
 * @class   SSOJWTServiceProviderHandlerZD
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    14 Oct 2014
 * */
class SSOJWTServiceProviderHandlerZD extends SSOJWTServiceProviderHandler {

    const RETURN_TO_SESSION_VAR = 'zd_return_to';

    /**
     * {@inheritdoc}
     */
    public function init() {
        $http = eZHTTPTool::instance();
        if( $http->hasGetVariable( 'return_to' ) ) {
            $http->setSessionVariable( self::RETURN_TO_SESSION_VAR, $http->getVariable( 'return_to' ) );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getToken() {
        $now    = time();
        $user   = eZUser::currentUser();
        $object = $user->attribute( 'contentobject' );

        return array(
            'jti'   => md5( $now . rand() ),
            'iat'   => $now,
            'name'  => $object->attribute( 'name' ),
            'email' => $user->attribute( 'email' )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSharedKey() {
        return self::getIni()->variable( 'zd', 'SharedKey' );
    }

    /**
     * {@inheritdoc}
     */
    public function getEndpointURL( $jwt ) {
        $subdomain = self::getIni()->variable( 'zd', 'Domain' );
        $url       = 'https://' . $subdomain . '.zendesk.com/access/jwt?jwt=' . $jwt;
        $http      = eZHTTPTool::instance();
        $returnTo  = $http->sessionVariable( self::RETURN_TO_SESSION_VAR, null );

        if( $returnTo !== null ) {
            $url .= '&return_to=' . urlencode( $returnTo );
        }

        $http->removeSessionVariable( self::RETURN_TO_SESSION_VAR );

        return $url;
    }

}
