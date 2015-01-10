<?php

/**
 * @package SSOJWT
 * @class   SSOJWTServiceProviderHandler
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    09 Oct 2014
 * */
abstract class SSOJWTServiceProviderHandler {

    protected static $instances  = array();
    protected $serviceProviderID = null;

    /**
     * Returns ini file handler
     * @return eZINI
     */
    public final static function getIni() {
        return eZINI::instance( 'sso_jwt.ini' );
    }

    /**
     * Getter for type getServiceProvider
     * @return string
     */
    public final function getServiceProvider() {
        return $this->serviceProviderID;
    }

    /**
     * Setter for type getServiceProvider
     * @param string $serviceProvider
     */
    public final function setServiceProvider( $serviceProvider ) {
        $this->serviceProviderID = $serviceProvider;
    }

    /**
     * Returns service provider handler
     * @param string $serviceProvider
     * @return SSOJWTServiceProviderHandler|null
     */
    public final static function get( $serviceProvider ) {
        $className = self::getClassName( $serviceProvider );

        if( class_exists( $className ) === false ) {
            return null;
        }

        $reflector = new ReflectionClass( $className );
        if( $reflector->isSubclassOf( __CLASS__ ) === false ) {
            return null;
        }

        $handler = call_user_func( array( $className, 'getInstance' ) );
        $handler->setServiceProvider( $serviceProvider );

        return $handler;
    }

    /**
     * Returns service provider handler class name
     * @param string $serviceProvider
     * @return string
     */
    public final static function getClassName( $serviceProvider ) {
        $serviceProviers = (array) self::getIni()->variable( 'General', 'ServiceProviders' );

        if( isset( $serviceProviers[$serviceProvider] ) ) {
            return $serviceProviers[$serviceProvider];
        }

        // Return default
        return null;
    }

    /**
     * Single instance of called class
     * @return static
     */
    public final static function getInstance() {
        $class = get_called_class();
        if( isset( self::$instances[$class] ) === false ) {
            self::$instances[$class] = new $class;
        }

        return self::$instances[$class];
    }

    /**
     * Return default service provider
     * @return string
     */
    public final static function getDefaultServiceProvider() {
        $serviceProviders = (array) self::getIni()->variable( 'General', 'ServiceProviders' );
        $serviceProviders = array_keys( $serviceProviders );
        return $serviceProviders[0];
    }

    /**
     * This method should be used on service provider side to notify identity provider about occured error
     */
    public final function error( $message ) {
        $url = rtrim( self::getIni()->variable( 'General', 'IdentityProviderURL' ), '/' );
        $url .= '/sso_jwt/logout/' . $this->getServiceProvider();
        $url .= '?kind=error&message=' . urlencode( $message );

        header( 'Location: ' . $url );
        eZExecution::cleanExit();
    }

    /**
     * Init login trigger. This method might be overridden in specific service provider handler.
     */
    public function init() {
        
    }

    /**
     * Return token data. This method should be overridden in specific service provider handler.
     * @return []
     */
    public function getToken() {
        return array();
    }

    /**
     * Return shared key. This method should be overridden in specific service provider handler.
     * @return string
     */
    public function getSharedKey() {
        return null;
    }

    /**
     * Return endpoint location. This method should be overridden in specific service provider handler.
     * @param string $jwt
     * @return string
     */
    public function getEndpointURL( $jwt ) {
        return null;
    }

    /**
     * Return url to which user will be redirected after logout
     * @return string
     */
    public function getAfterLogoutURL() {
        return null;
    }

    /**
     * Validates token data. This method is called on service provider side
     * @param array $token
     * @return bool
     */
    public function validateToken( array $token ) {
        return true;
    }

    /**
     * Transforms token data into eZUser. This method is called on service provider side
     * @param array $token
     * @return eZUser|null
     */
    public function getUser( array $token ) {
        return null;
    }

    /**
     * Checks if SSO is enabled for current service provider
     * @return bool
     */
    public function isSSOEnabled() {
        return self::getIni()->variable( $this->getServiceProvider(), 'SSO' ) == 'enabled';
    }

}
