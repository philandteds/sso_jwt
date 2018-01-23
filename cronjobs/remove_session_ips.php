<?php

/**
 * @package SSOJWT
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    23 Feb 2018
 * */
$cli->output( 'Removing expired session IP checks...' );

$db = eZDB::instance();
$q  = '
    SELECT session_ip.session_id
    FROM session_ip
    LEFT JOIN ezsession ON session_ip.session_id = ezsession.session_key
    WHERE ezsession.session_key IS NULL
';
$r  = $db->arrayQuery( $q );
foreach( $r as $row ) {
    // Check if session exists on un-clustered instances
    if( file_exists( '/tmp/sess_' . $row['session_id'] ) ) {
        continue;
    }

    $p = array( 'session_id' => $row['session_id'] );
    eZPersistentObject::removeObject( SSOJWTSessionIp::definition(), $p );
}

$cli->output( 'Expired session IP checks were removed' );
