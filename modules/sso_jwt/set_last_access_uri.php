<?php

/**
 * @package SSOJWT
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    11 Jan 2015
 * */
$http = eZHTTPTool::instance();
if( $http->hasVariable( 'uri' ) ) {
    $http->setSessionVariable( 'LastAccessesURI', $http->variable( 'uri' ) );
}
eZExecution::cleanExit();