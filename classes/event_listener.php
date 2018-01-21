<?php

/**
 * @package SSOJWT
 * @class   SSOJWTEventListener
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    19 Feb 2018
 * */
class SSOJWTEventListener {

    public static function checkSessionIP( $uri ) {
        if( SSOJWTSessionIp::isEnabled() === false ) {
            return;
        }

        if( eZUser::currentUser()->isLoggedIn() === false ) {
            return;
        }

        $sessionIp  = null;
        $conditions = array(
            'session_id' => array( '=', session_id() )
        );
        $tmp = SSOJWTSessionIp::fetchList( $conditions );
        if( count( $tmp ) > 0 ) {
            $sessionIp = $tmp[0]->attribute( 'user_ip' );
        }

        if( count( $tmp ) === 0 || $sessionIp !== SSOJWTSessionIp::getUserRealIP() ) {
            session_destroy();
            session_start();
            session_regenerate_id(true);
        }
    }

}
