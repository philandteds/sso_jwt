{* Calls the JS redirectToLastAccessUri() function, which bounces the user back to the page they were at before the login*}

<div class="sso-jwt-redirect">
    <h3>{'Redirecting... please wait....'|i18n('extension/sso_jwt')}</h3>
</div>

<script src={"javascript/sso_login.js"|ezdesign}></script>
{literal}
    <script type="text/javascript">
            redirectToLastAccessUri();
    </script>
{/literal}