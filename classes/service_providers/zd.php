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
        $http     = eZHTTPTool::instance();
        $returnTo = null;

        $referer = eZSys::serverVariable( 'HTTP_REFERER', true );
        if( $referer !== null ) {
            $tmp = parse_url( $referer );
            parse_str( $tmp['query'], $params );
            if( isset( $params['return_to'] ) ) {
                $returnTo = $params['return_to'];
            }
        }

        if( $returnTo === null && $http->hasGetVariable( 'return_to' ) ) {
            $returnTo = $http->getVariable( 'return_to' );
        }

        if( $returnTo !== null ) {
            $http->setSessionVariable( self::RETURN_TO_SESSION_VAR, $returnTo );
            $http->setSessionVariable( 'RedirectAfterUserRegister', $returnTo );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getToken() {
        $now    = time();
        $user   = eZUser::currentUser();
        $object = $user->attribute( 'contentobject' );

        $token = array(
            'jti'   => md5( $now . rand() ),
            'iat'   => $now,
            'name'  => $object->attribute( 'name' ),
            'email' => $user->attribute( 'email' )
        );

        $userType = SyncZDUserType::getGroupType( $object );
        if( $userType == 'portal' ) {
            $dataMap    = $object->attribute( 'data_map' );
            $userFields = array();

            if( isset( $dataMap['account_number'] ) ) {
                $userFields['ax_account_number'] = $dataMap['account_number']->attribute( 'content' );
            }
            $token['user_fields'] = $userFields;
            $token['tags']        = 'pt_portal_user';
        }

        return $token;
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

    /**
     * {@inheritdoc}
     */
    public function getAfterLogoutURL() {
        $subdomain = self::getIni()->variable( 'zd', 'Domain' );
        return 'https://' . $subdomain . '.zendesk.com/';
    }

}
