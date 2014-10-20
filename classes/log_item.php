<?php

/**
 * @package SSOJWT
 * @class   SSOJWTTokenUsage
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    18 Oct 2014
 * */
class SSOJWTLogItem extends eZPersistentObject {

    public function __construct( array $row = null ) {
        parent::__construct( $row );

        if( $this->attribute( 'id' ) === null ) {
            $bt = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
            foreach( $bt as $i => $call ) {
                // remove calls for current class and index.php
                if( isset( $call['class'] ) === false || $call['class'] === __CLASS__ ) {
                    unset( $bt[$i] );
                }
            }

            $baseDir = getcwd() . '/';
            foreach( $bt as $call ) {
                $backtrace[] = array(
                    'file'     => isset( $call['file'] ) ? str_replace( $baseDir, '', $call['file'] ) : null,
                    'line'     => isset( $call['line'] ) ? $call['line'] : null,
                    'function' => isset( $call['function'] ) ? $call['function'] : null,
                    'class'    => isset( $call['class'] ) ? $call['class'] : null,
                    'type'     => isset( $call['type'] ) ? $call['type'] : null,
                );
            }

            $this->setAttribute( 'backtrace', array_reverse( $backtrace ) );
        } else {
            $this->setAttribute( 'backtrace', unserialize( $this->attribute( 'backtrace' ) ) );
        }
    }

    public static function definition() {
        return array(
            'fields'              => array(
                'id'               => array(
                    'name'     => 'ID',
                    'datatype' => 'integer',
                    'default'  => 0,
                    'required' => true
                ),
                'date'             => array(
                    'name'     => 'Date',
                    'datatype' => 'integer',
                    'default'  => time(),
                    'required' => true
                ),
                'service_provider' => array(
                    'name'     => 'ServiceProvider',
                    'datatype' => 'string',
                    'default'  => null,
                    'required' => false
                ),
                'user_id'          => array(
                    'name'     => 'UserID',
                    'datatype' => 'integer',
                    'default'  => eZUser::currentUserID(),
                    'required' => false
                ),
                'message'          => array(
                    'name'     => 'Message',
                    'datatype' => 'string',
                    'default'  => null,
                    'required' => false
                ),
                'error'            => array(
                    'name'     => 'Error',
                    'datatype' => 'string',
                    'default'  => null,
                    'required' => false
                ),
                'backtrace'        => array(
                    'name'     => 'Backtrace',
                    'datatype' => 'string',
                    'default'  => null,
                    'required' => false
                ),
                'ip'               => array(
                    'name'     => 'IP',
                    'datatype' => 'string',
                    'default'  => \eZSys::clientIP(),
                    'required' => false
                )
            ),
            'function_attributes' => array(
                'backtrace_output' => 'getBacktraceOutput',
                'user'             => 'getUser'
            ),
            'keys'                => array( 'id' ),
            'sort'                => array( 'id' => 'desc' ),
            'increment_key'       => 'id',
            'class_name'          => __CLASS__,
            'name'                => 'sso_jwt_logs'
        );
    }

    public function getBacktraceOutput() {
        return var_export( $this->attribute( 'backtrace' ), true );
    }

    public function getUser() {
        return (int) $this->attribute( 'user_id' ) > 0 ? eZContentObject::fetch( $this->attribute( 'user_id' ) ) : null;
    }

    public function store( $fieldFilters = null ) {
        $this->setAttribute( 'backtrace', serialize( $this->attribute( 'backtrace' ) ) );
        eZPersistentObject::storeObject( $this, $fieldFilters );
        $this->setAttribute( 'backtrace', unserialize( $this->attribute( 'backtrace' ) ) );
    }

    public static function fetchList( $conditions = null, $limitations = null, $custom_conds = null ) {
        return eZPersistentObject::fetchObjectList(
                static::definition(), null, $conditions, null, $limitations, true, false, null, null, $custom_conds
        );
    }

    public static function getPossibleServiceProviders() {
        $db = eZDB::instance();
        $q  = 'SELECT DISTINCT service_provider '
            . 'FROM sso_jwt_logs '
            . 'WHERE service_provider IS NOT NULL '
            . 'ORDER BY service_provider';
        $r  = $db->arrayQuery( $q );

        $return = array();
        foreach( $r as $row ) {
            $return[] = $row['service_provider'];
        }
        return $return;
    }

    public static function getExpiryTime() {
        return eZINI::instance( 'sso_jwt.ini' )->variable( 'General', 'LogsExpiryTime' );
    }

    public static function countAll( $conds = null, $fields = null ) {
        return eZPersistentObject::count( static::definition(), $conds, $fields );
    }

    public static function create( $serviceProvider, $message, $error = null ) {
        $item = new SSOJWTLogItem();
        if( $serviceProvider !== null ) {
            $item->setAttribute( 'service_provider', $serviceProvider );
        }
        if( $message !== null ) {
            $item->setAttribute( 'message', $message );
        }
        if( $error !== null ) {
            $item->setAttribute( 'error', $error );
        }
        $item->store();

        return $item;
    }

}
