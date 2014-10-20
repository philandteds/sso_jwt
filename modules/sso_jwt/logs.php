<?php

/**
 * @package SSOJWT
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    18 Oct 2014
 * */
$module        = $Params['Module'];
$http          = eZHTTPTool::instance();
$defaultFilter = array(
    'service_provider' => null,
    'user_id'          => null,
    'error'            => null,
    'ip'               => null
);
$filter        = $http->sessionVariable( 'sso_jwt_logs_filter', $defaultFilter );

if( $http->hasVariable( 'filter' ) ) {
    $filter = array_merge( $filter, (array) $http->variable( 'filter' ) );
}

if( $module->isCurrentAction( 'BrowseFilterUser' ) ) {
    $browseParameters = array(
        'action_name' => 'SetFilterUser',
        'type'        => 'AddRelatedObjectToDataType',
        'from_page'   => 'sso_jwt/logs'
    );
    return eZContentBrowse::browse( $browseParameters, $Params['Module'] );
} elseif( $module->isCurrentAction( 'SetFilterUser' ) ) {
    $selectedIDs       = (array) $http->variable( 'SelectedObjectIDArray' );
    $filter['user_id'] = $selectedIDs[0];
}

$http->setSessionVariable( 'sso_jwt_logs_filter', $filter );

$customConds = null;
$conditions  = array();
if( strlen( $filter['service_provider'] ) !== 0 ) {
    $conditions['service_provider'] = array( '=', $filter['service_provider'] );
}
if( strlen( $filter['error'] ) !== 0 ) {
    if( (bool) $filter['error'] ) {
        $conditions['error'] = array( '<>', '' );
    } else {
        $customConds .= ' AND error IS NULL';
    }
}
if( strlen( $filter['ip'] ) !== 0 ) {
    $conditions['ip'] = array( 'like', '%' . $filter['ip'] . '%' );
}

if( count( $conditions ) === 0 ) {
    $conditions  = null;
    $customConds = ' WHERE 1 ' . $customConds;
}

$params      = $Params['Module']->UserParameters;
$offset      = isset( $params['offset'] ) ? (int) $params['offset'] : 0;
$limit       = isset( $params['limit'] ) ? (int) $params['limit'] : 50;
$limitations = array(
    'limit'  => $limit,
    'offset' => $offset
);

$tpl = eZTemplate::factory();
$tpl->setVariable( 'logs', SSOJWTLogItem::fetchList( $conditions, $limitations, $customConds ) );
$tpl->setVariable( 'filter', $filter );
$tpl->setVariable( 'offset', $offset );
$tpl->setVariable( 'limit', $limit );
$tpl->setVariable( 'total_count', SSOJWTLogItem::countAll( $conditions ) );
$tpl->setVariable( 'service_providers', SSOJWTLogItem::getPossibleServiceProviders() );

$Result['content']         = $tpl->fetch( 'design:sso_jwt/logs/show.tpl' );
$Result['navigation_part'] = eZINI::instance( 'sso_jwt.ini' )->variable( 'NavigationParts', 'Logs' );
$Result['path']            = array(
    array(
        'text' => ezpI18n::tr( 'extension/sso_jwt', 'SSO JWT Logs' ),
        'url'  => false
    )
);
