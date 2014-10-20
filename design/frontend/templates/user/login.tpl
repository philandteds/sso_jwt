{* Login - Override Template *}
{def $sso_login_url = concat( ezini( 'General', 'IdentityProviderURL', 'sso_jwt.ini' ), 'sso_jwt/login/', ezini( 'General', 'CurrentServiceProvider', 'sso_jwt.ini' ) )|ezurl( 'no' )}

<div class="content-login">
    <div id="login_full">
        <div class="loginbox">
            <p>
                {'Click on the following link to sign-in:'|i18n( 'extension/sso_jwt' )}
            </p>
            <a id="sso-jwt-login-link" href="{$sso_login_url}">{'Sign-In'|i18n( 'extension/sso_jwt' )}</a>
        </div>
    </div>
</div>
{literal}
<script type="text/javascript">
$( document ).ready(function() {
    $( '#sso-jwt-login-link' )[0].click();
} );
</script>
{/literal}