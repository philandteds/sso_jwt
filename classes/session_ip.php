<?php

/**
 * @package SSOJWT
 * @class   SSOJWTSessionIp
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    19 Feb 2018
 * */
class SSOJWTSessionIp extends eZPersistentObject {

    public function __construct( array $row = null ) {
        parent::__construct( $row );

        if( $this->attribute( 'session_id' ) === null ) {
            $this->setAttribute( 'session_id', session_id() );
        }
        if( $this->attribute( 'user_ip' ) === null ) {
            $this->setAttribute( 'user_ip', self::getUserRealIP() );
        }
    }

    public static function definition() {
        return array(
            'fields'              => array(
                'session_id' => array(
                    'name'     => 'SessionID',
                    'datatype' => 'string',
                    'default'  => null,
                    'required' => true
                ),
                'user_ip'    => array(
                    'name'     => 'UserIP',
                    'datatype' => 'string',
                    'default'  => null,
                    'required' => true
                ),
            ),
            'function_attributes' => array(),
            'keys'                => array( 'session_id' ),
            'class_name'          => __CLASS__,
            'name'                => 'session_ip'
        );
    }

    public static function fetchList( $conditions = null, $limitations = null, $custom_conds = null ) {
        return eZPersistentObject::fetchObjectList(
            static::definition(), null, $conditions, null, $limitations, true, false, null, null, $custom_conds
        );
    }

    public static function getUserRealIP() {
        $ip  = eZSys::serverVariable( 'REMOTE_ADDR', true );
        $xIp = eZSys::serverVariable( 'HTTP_X_FORWARDED_FOR', true );
        if( $xIp !== null ) {
            $tmp = explode( ',', $xIp );
            $ip  = $tmp[0];
        }

        return $ip;
    }

    public static function isEnabled() {
        $ini   = eZINI::instance( 'sso_jwt.ini' );
        $value = $ini->variable( 'SessionIpCheck', 'Status' );
        if( in_array( $value, array( 'enabled', 'yes', 'true' ) ) === false ) {
            return false;
        }

        $skipSAs = $ini->variable( 'SessionIpCheck', 'SkipSiteaccesses' );
        $sa      = eZSiteAccess::current();
        if( in_array( $sa['name'], $skipSAs ) ) {
            return false;
        }

        return true;
    }
}
