<?php

/**
 * @package SSOJWT
 * @class   SSOJWTServiceProviderHandlerEZPT
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    02 Mar 2015
 * */
class SSOJWTServiceProviderHandlerEZPT extends SSOJWTServiceProviderHandlerEZ {

    const LANGUAGE_CODE = 'eng-RW';

    private static $portalGroups = array( 'LJ Users' );

    /**
     * {@inheritdoc}
     */
    public function getToken() {
        $token                     = parent::getToken();
        $token['consumer_profile'] = null;
        $token['address']          = null;
        $token['groups']           = array();

        $consumerProfile   = self::getConsumerProfile();
        $profileAttributes = array(
            'name'               => null,
            'children_under_5'   => null,
            'youngest_born_date' => null,
            'next_born_date'     => null,
            'are_pregnant'       => null,
        );
        if( $consumerProfile instanceof eZContentObjectTreeNode ) {
            $dataMap = $consumerProfile->attribute( 'data_map' );
            foreach( $profileAttributes as $attr => $value ) {
                if( isset( $dataMap[$attr] ) === false ) {
                    continue;
                }

                $profileAttributes[$attr] = $dataMap[$attr]->toString();
            }

            $token['consumer_profile'] = $profileAttributes;
        }

        $address           = self::getAddress();
        $addressAttributes = array(
            'street_address' => null,
            'city'           => null,
            'state'          => null,
            'zip'            => null,
            'country'        => null,
            'phone'          => null,
            'fax'            => null
        );
        if( $address instanceof eZContentObjectTreeNode ) {
            $dataMap = $address->attribute( 'data_map' );
            foreach( $addressAttributes as $attr => $value ) {
                if( isset( $dataMap[$attr] ) === false ) {
                    continue;
                }

                $addressAttributes[$attr] = $dataMap[$attr]->toString();
            }

            $isAddressEmpty = true;
            foreach( $addressAttributes as $value ) {
                if( empty( $value ) === false ) {
                    $isAddressEmpty = false;
                    break;
                }
            }

            if( $isAddressEmpty === false ) {
                $token['address'] = $addressAttributes;
            }
        }

        if( isset( $token['address'] ) === false ) {
            $isProfileEmpty = true;
            foreach( $profileAttributes as $attr => $value ) {
                if( $attr == 'name' ) {
                    continue;
                }

                if( empty( $value ) === false ) {
                    $isProfileEmpty = false;
                    break;
                }
            }

            if( $isProfileEmpty ) {
                $token['consumer_profile'] = null;
            }
        }

        $nodes = eZUser::currentUser()->attribute( 'contentobject' )->attribute( 'assigned_nodes' );
        foreach( $nodes as $node ) {
            $i = 0;
            while( $node instanceof eZContentObjectTreeNode && $node->attribute( 'depth' ) > 1 && $i < 20 ) {
                if( $node->attribute( 'class_identifier' ) == 'user_group' ) {
                    $token['groups'][] = $node->attribute( 'name' );
                }

                $node = $node->attribute( 'parent' );
            }
        }
        $token['groups'] = array_unique( $token['groups'] );

        return $token;
    }

    private static function getConsumerProfile( $userObjectID = null ) {
        if( $userObjectID === null ) {
            $userObjectID = eZUser::currentUserID();
        }

        $fetchParams = array(
            'Depth'            => false,
            'Limitation'       => array(),
            'LoadDataMap'      => false,
            'AsObject'         => true,
            'IgnoreVisibility' => true,
            'MainNodeOnly'     => true,
            'ClassFilterType'  => 'include',
            'ClassFilterArray' => array( 'consumer_profile' ),
            'AttributeFilter'  => array(
                array( 'consumer_profile/user', '=', $userObjectID )
            )
        );

        $nodes = eZContentObjectTreeNode::subTreeByNodeID( $fetchParams, 1 );
        return count( $nodes ) > 0 ? $nodes[0] : null;
    }

    public static function getAddress( $userObjectID = null ) {
        if( $userObjectID === null ) {
            $userObjectID = eZUser::currentUserID();
        }

        $profile = self::getConsumerProfile( $userObjectID );
        if( $profile instanceof eZContentObjectTreeNode === false ) {
            return null;
        }

        $fetchParams = array(
            'Depth'            => false,
            'Limitation'       => array(),
            'LoadDataMap'      => false,
            'AsObject'         => true,
            'IgnoreVisibility' => true,
            'MainNodeOnly'     => true,
            'ClassFilterType'  => 'include',
            'ClassFilterArray' => array( 'address' ),
            'AttributeFilter'  => array(
                array( 'address/consumer_profile', '=', $profile->attribute( 'contentobject_id' ) )
            )
        );

        $nodes = eZContentObjectTreeNode::subTreeByNodeID( $fetchParams, 1 );
        return count( $nodes ) > 0 ? $nodes[0] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser( array $token ) {
        // portal users should not be able to login on web shops
        if( isset( $token['groups'] ) && is_array( $token['groups'] ) && count( $token['groups'] ) == 1 ) {
            foreach( self::$portalGroups as $portalGroup ) {
                if( in_array( $portalGroup, $token['groups'] ) ) {
                    $url = rtrim( self::getIni()->variable( 'General', 'IdentityProviderURL' ), '/' );
                    header( 'Location: ' . $url );
                    eZExecution::cleanExit();
                }
            }
        }

        // try to fetch existing user
        $user = eZUser::fetchByEmail( $token['email'] );
        if( $user instanceof eZUser ) {
            // Hanlder consumer profile and address data for existing user
            self::handleUserData( $user->attribute( 'contentobject' ), $token );

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

        $behaviour = new ezpContentPublishingBehaviour();
        $behaviour->disableAsynchronousPublishing( true );
        $behaviour->isTemporary( true );
        $behaviour->disableAsynchronousPublishing = true;
        $behaviour->isTemporary = true;
        ezpContentPublishingBehaviour::setBehaviour( $behaviour );

        $params = array(
            'parent_node_id'   => $parentNode->attribute( 'node_id' ),
            'class_identifier' => 'user',
            'attributes'       => $attribtues
        );
        $object = eZContentFunctions::createAndPublishObject( $params );
        if( $object instanceof eZContentObject === false ) {
            throw new Exception( 'New user creation error' );
        }

        // Create consumer profile and address data
        self::handleUserData( $object, $token );

        $dataMap = $object->attribute( 'data_map' );
        return $dataMap['user_account']->attribute( 'content' );
    }

    private static function handleUserData( eZContentObject $user, $token ) {
        // Check if there is any consumer profile data in the token
        if( isset( $token['consumer_profile'] ) === false ) {
            return null;
        }

        $consumerAttributes         = (array) $token['consumer_profile'];
        $consumerAttributes['user'] = $user->attribute( 'id' );

        // Handle consumer profile
        $consumerProfile = self::getConsumerProfile( $user->attribute( 'id' ) );
        if( $consumerProfile instanceof eZContentObjectTreeNode === false ) {
            // Create consumer profile
            $params                = array(
                'parent_node_id'   => $user->attribute( 'main_node_id' ),
                'class_identifier' => 'consumer_profile',
                'attributes'       => $consumerAttributes,
                'language'         => self::LANGUAGE_CODE
            );
            $consumerProfileObject = ContentSyncContentFunctions::createAndPublishObject( $params );
            if( $consumerProfileObject instanceof eZContentObject === false ) {
                throw new Exception( 'New consumer profile creation error' );
            }

            $consumerProfile = $consumerProfileObject->attribute( 'main_node' );
        } else {
            ContentSyncContentFunctions::updateAndPublishObject( $consumerProfile->attribute( 'object' ), array( 'attributes' => $consumerAttributes ) );
        }

        // Check if there is any address data in the token and if there is existing consumer profile
        if( isset( $token['address'] ) === false || $consumerProfile instanceof eZContentObjectTreeNode === false ) {
            return null;
        }

        $addressAttributes                     = (array) $token['address'];
        $addressAttributes['consumer_profile'] = $consumerProfile->attribute( 'contentobject_id' );

        // Handle address
        $address = self::getAddress( $user->attribute( 'id' ) );
        if( $address instanceof eZContentObjectTreeNode === false ) {
            // Create consumer profile
            $params        = array(
                'parent_node_id'   => $consumerProfile->attribute( 'main_node_id' ),
                'class_identifier' => 'address',
                'attributes'       => $addressAttributes,
                'language'         => self::LANGUAGE_CODE
            );
            $addressObject = ContentSyncContentFunctions::createAndPublishObject( $params );
            if( $addressObject instanceof eZContentObject === false ) {
                throw new Exception( 'New address creation error' );
            }
        } else {
            ContentSyncContentFunctions::updateAndPublishObject( $address->attribute( 'object' ), array( 'attributes' => $addressAttributes ) );
        }
    }

}
