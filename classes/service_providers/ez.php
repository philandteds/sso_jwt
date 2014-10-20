<?php

/**
 * @package SSOJWT
 * @class   SSOJWTServiceProviderHandlerEZ
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    14 Oct 2014
 * */
class SSOJWTServiceProviderHandlerEZ extends SSOJWTServiceProviderHandler {

    /**
     * {@inheritdoc}
     */
    public function getToken() {
        $now    = time();
        $user   = eZUser::currentUser();
        $object = $user->attribute( 'contentobject' );

        $token = array(
            'token_id'  => md5( $now . rand() ),
            'issued_at' => $now,
            'login'     => $user->attribute( 'login' ),
            'email'     => $user->attribute( 'email' )
        );

        $attrs   = $this->getUserAttributes();
        $dataMap = $object->attribute( 'data_map' );
        foreach( $attrs as $attribute ) {
            $value = null;
            if( isset( $dataMap[$attribute] ) !== false ) {
                $value = $dataMap[$attribute]->toString();
            }

            $token[$attribute] = $value;
        }

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function getSharedKey() {
        return self::getIni()->variable( $this->getServiceProvider(), 'SharedKey' );
    }

    /**
     * {@inheritdoc}
     */
    public function getEndpointURL( $jwt ) {
        $serviceProvider = $this->getServiceProvider();
        $url             = rtrim( self::getIni()->variable( $serviceProvider, 'SiteURL' ), '/' );
        return $url . '/sso_jwt/access/' . $serviceProvider . '?jwt=' . $jwt;
    }

    /**
     * {@inheritdoc}
     */
    public function validateToken( array $token ) {
        $requiredFields = array( 'token_id', 'issued_at', 'login', 'email' );
        foreach( $requiredFields as $requiredField ) {
            if( isset( $token[$requiredField] ) === false || empty( $token[$requiredField] ) ) {
                throw new Exception( 'Requried field "' . $requiredField . '" is missing in the token definition' );
            }
        }

        $expiry = (int) $token['issued_at'] + (int) self::getIni()->variable( 'General', 'TokenValidTime' );
        if( $expiry <= time() ) {
            throw new Exception( 'Token is expired' );
        }

        $conditions = array( 'token_id' => $token['token_id'] );
        if( count( SSOJWTTokenUsage::fetchList( $conditions ) ) > 0 ) {
            throw new Exception( 'Token was already used' );
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser( array $token ) {
        // try to fetch existing user
        $user = eZUser::fetchByName( $token['login'] );
        if( $user instanceof eZUser ) {
            return $user;
        }

        // create new user
        $parentNodeID = (int) self::getIni()->variable( $this->getServiceProvider(), 'DefaultUserGroupID' );
        $parentNode   = eZContentObjectTreeNode::fetch( $parentNodeID );
        if( $parentNode instanceof eZContentObjectTreeNode === false ) {
            throw new Exception( 'Default user group (node_id: ' . $parentNodeID . ') does not exist' );
        }

        $password   = md5( eZUser::createPassword( 16 ) );
        $attribtues = array(
            'user_account' => $token['login'] . '|' . $token['email'] . '|md5_password|' . $password . '|1'
        );
        $attrs      = $this->getUserAttributes();
        foreach( $attrs as $identifier ) {
            $attribtues[$identifier] = isset( $token[$identifier] ) ? $token[$identifier] : null;
        }

        $params = array(
            'parent_node_id'   => $parentNode->attribute( 'node_id' ),
            'class_identifier' => 'user',
            'attributes'       => $attribtues
        );
        $object = eZContentFunctions::createAndPublishObject( $params );
        if( $object instanceof eZContentObject === false ) {
            throw new Exception( 'New user creation error' );
        }

        $dataMap = $object->attribute( 'data_map' );
        return $dataMap['user_account']->attribute( 'content' );
    }

    /**
     * Returns array of usar attributes which will be included in the token
     * @return []
     */
    public function getUserAttributes() {
        return (array) self::getIni()->variable( $this->getServiceProvider(), 'Attributes' );
    }

}
