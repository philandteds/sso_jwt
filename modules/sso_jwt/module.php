<?php

/**
 * @package SSOJWT
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    09 Oct 2014
 * */
$Module = array(
    'name'      => 'Single Sign-On (JWT)',
    'functions' => array()
);

$ViewList = array(
    // Identity provider views
    'login'               => array(
        'script'    => 'login.php',
        'functions' => array( 'idp' ),
        'params'    => array( 'ServiceProvider' )
    ),
    'logout'              => array(
        'script'    => 'logout.php',
        'functions' => array( 'idp' ),
        'params'    => array( 'ServiceProvider' )
    ),
    'logs'                => array(
        'functions'           => array( 'idp' ),
        'script'              => 'logs.php',
        'params'              => array(),
        'single_post_actions' => array(
            'BrowseFilterUserButton' => 'BrowseFilterUser'
        ),
        'single_get_actions'  => array(
            'SetFilterUser'
        ),
        'post_actions'        => array( 'BrowseActionName' )
    ),
    // Service provider views
    'access'              => array(
        'script'    => 'access.php',
        'functions' => array( 'sp' ),
        'params'    => array( 'ServiceProvider' )
    ),
    'set_last_access_uri' => array(
        'script'    => 'set_last_access_uri.php',
        'functions' => array( 'sp' ),
        'params'    => array()
    ),
);

$FunctionList = array(
    'idp' => array(),
    'sp'  => array()
);
