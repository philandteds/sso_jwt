<?php

/**
 * @package SSOJWT
 * @class   SSOJWTTokenUsage
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    15 Oct 2014
 * */
class SSOJWTTokenUsage extends eZPersistentObject {

    public function __construct( array $row = null ) {
        parent::__construct( $row );
    }

    public static function definition() {
        return array(
            'fields'              => array(
                'id'         => array(
                    'name'     => 'ID',
                    'datatype' => 'integer',
                    'default'  => null,
                    'required' => true
                ),
                'token_id'   => array(
                    'name'     => 'TokenID',
                    'datatype' => 'string',
                    'default'  => null,
                    'required' => true
                ),
                'usage_date' => array(
                    'name'     => 'UsageDate',
                    'datatype' => 'integer',
                    'default'  => time(),
                    'required' => true
                ),
                'token_jwt'  => array(
                    'name'     => 'TokenJWT',
                    'datatype' => 'string',
                    'default'  => null,
                    'required' => true
                ),
            ),
            'function_attributes' => array(),
            'keys'                => array( 'id' ),
            'sort'                => array( 'id' => 'desc' ),
            'increment_key'       => 'id',
            'class_name'          => __CLASS__,
            'name'                => 'sso_jwt_token'
        );
    }

    public static function fetchList( $conditions = null, $limitations = null, $custom_conds = null ) {
        return eZPersistentObject::fetchObjectList(
                static::definition(), null, $conditions, null, $limitations, true, false, null, null, $custom_conds
        );
    }

    public static function getExpiryTime() {
        return eZINI::instance( 'sso_jwt.ini' )->variable( 'General', 'TokenExpiryTime' );
    }

}
