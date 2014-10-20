<?php

/**
 * @package SSOJWT
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    15 Oct 2014
 * */
$cli->output( 'Removing expired SSO JWT tokens...' );

$p = array(
    'usage_date' => array( '<=', time() - SSOJWTTokenUsage::getExpiryTime() )
);
eZPersistentObject::removeObject( SSOJWTTokenUsage::definition(), $p );

$cli->output( 'Expired tokens were removed' );
