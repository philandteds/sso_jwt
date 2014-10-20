{ezcss_load( array( 'bootstrap.css', 'helpers.css' ) )}
{ezscript_load( array( 'collapse.js' ) )}

<div class="bootstrap-wrapper">
    <h2 class="h3 u-margin-t-m">{'SSO JWT Logs'|i18n( 'extension/sso_jwt' )} ({$total_count})</h2>

    <form class="panel panel-primary" action="{'sso_jwt/logs'|ezurl( 'no' )}" method="post">
        <div class="panel-heading">
            <h3 class="panel-title">{'Filter logs'|i18n( 'extension/sso_jwt' )}</h3>
        </div>
        <div class="panel-body">
            <div class="form-horizontal">
                <div class="form-group">
                    <label class="control-label col-lg-2">{'Service Provider'|i18n( 'extension/sso_jwt' )}:</label>
                    <div class="col-lg-10">
                        <select class="form-control" name="filter[service_provider]">
                            <option value="">{'- All -'|i18n( 'extension/sso_jwt' )}</option>
                            {foreach $service_providers as $service_provider}
                                <option value="{$service_provider}"{if eq( $filter.service_provider, $service_provider )} selected="selected"{/if}>{$service_provider}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-2">{'User'|i18n( 'extension/sso_jwt' )}:</label>
                    <div class="col-lg-10">
                        {def $filter_user = fetch( 'content', 'object', hash( 'object_id', $filter.user_id ) )}
                        {if $filter_user}
                            <a href="{$filter_user.main_node.url_alias|ezurl( 'no' )}">{$filter_user.name}</a>
                        {/if}
                        {undef $filter_user}
                        <input class="btn btn-primary" type="submit" name="BrowseFilterUserButton" value="{'Select'|i18n( 'extension/sso_jwt' )}" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-2">{'Error'|i18n( 'extension/sso_jwt' )}:</label>
                    <div class="col-lg-10">
                        <select class="form-control" name="filter[error]">
                            <option value="">{'- Not selected -'|i18n( 'extension/sso_jwt' )}</option>
                            <option value="1"{if eq( $filter.error, '1' )} selected="selected"{/if}>{'Yes'|i18n( 'extension/sso_jwt' )}</option>
                            <option value="0"{if eq( $filter.error, '0' )} selected="selected"{/if}>{'No'|i18n( 'extension/sso_jwt' )}</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-2">{'IP'|i18n( 'extension/sso_jwt' )}:</label>
                    <div class="col-lg-10">
                        <input class="form-control" type="text" value="{$filter.ip}" name="filter[ip]">
                    </div>
                </div>
                <div class="form-group u-margin-b-n">
                    <div class="col-lg-10 col-lg-offset-2">
                        <input class="btn btn-primary" type="submit" value="{'Filter'|i18n( 'extension/sso_jwt' )}" />
                    </div>
                </div>
            </div>

        </div>
    </form>

    {include
        uri='design:navigator/google.tpl'
        page_uri='sso_jwt/logs'
        item_count=$total_count
        view_parameters=hash( 'limit', $limit, 'offset', $offset )
        item_limit=$limit
    }
    {def $log_item_user = false()}
    {foreach $logs as $log}
        {set $log_item_user = $log.user}
        <div>
            <h3>{$log.date|datetime( 'custom', '%d.%m.%Y %H:%i:%s' )}, {$log.ip} [{$log.service_provider}]{if $log_item_user} <a href="{$log_item_user.main_node.url_alias|ezurl( 'no' )}">{$log_item_user.name}</a>{/if}</h3>

            <div class="panel-group" id="accordion-{$log.id}">
                {if $log.error}
                    {include uri='design:sso_jwt/logs/collapse_part.tpl' id=concat( '1-', $log.id ) title='Error' content=$log.error css_class='danger' is_collapsed=false()}
                {else}
                    {include uri='design:sso_jwt/logs/collapse_part.tpl' id=concat( '1-', $log.id ) title='Message' content=$log.message is_collapsed=false()}
                {/if}
                {include uri='design:sso_jwt/logs/collapse_part.tpl' id=concat( '2-', $log.id ) title='Backtrace' content=$log.backtrace_output}
            </div>
        </div>
        <hr>
    {/foreach}
    {undef $log_item_user}
    {include
        uri='design:navigator/google.tpl'
        page_uri='sso_jwt/logs'
        item_count=$total_count
        view_parameters=hash( 'limit', $limit, 'offset', $offset )
        item_limit=$limit
    }
</div>