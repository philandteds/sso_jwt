{def $current_siteaccess='/'|ezurl(no, relative)}



{* TODO remove inline styling *}
{literal}
    <style>        

        input[type='checkbox'] {
            width:auto;
            margin-right:1rem;
        }

        a.trigger-show-register-tab {
            color: white;
        }


        /* position error messages (plain input fields) */
        .has-error .help-block {
            margin-top: -8px;
            margin-bottom: 12px;
        }

        /* and a special case for the privacy policy checkbox */
        .privacy-policy-box.has-error .help-block {
            margin-top: 0px;
        }
    </style>
{/literal}



<div id="login-check" class="text-center" style="display:none">
    <br/><br/>
    <img src={"/icons/spiffygif_24x24.gif"|ezimage} alt="" />
    <br/><br/>
    {'Please wait...'|i18n("extension/pt")}
    <br/><br/>
</div>

<div class="login" data-example-id="togglable-tabs">
    <ul id="myTabs" class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#home" id="home-tab" role="tab" data-toggle="tab" aria-controls="home" aria-expanded="true">Log in</a></li>
        <li role="presentation" class=""><a href="#profile" role="tab" id="profile-tab" data-toggle="tab" aria-controls="profile" aria-expanded="false">Sign Up</a></li>
    </ul>
    <div id="myTabContent" class="tab-content"> <div role="tabpanel" class="tab-pane fade active in" id="home" aria-labelledby="home-tab">    {* main box *}
            <div class="interface inner">
                <h2 class="title">Sign in to {$site_name|wash(xhtml)}</h2>
                {* social logins *}
                <div class="services external" id="zendesk-js-login-external">
                    <div>
                        {def
                        $socail_login_base_url = concat( ezini( 'General', 'IdentityProviderURL', 'sso_jwt.ini' ), 'nxc_social_network_login/redirect/' )|ezurl( 'no' )
                        $current_sp            = ezini( 'General', 'CurrentServiceProvider', 'sso_jwt.ini' )
                        }
                        <a href="{$socail_login_base_url}twitter?after_login_url=sso_jwt/login/{$current_sp}" class="service twitter" target="_top">
                            <span data-icon="&#xe000;" class="icon"></span>
                            Sign in with Twitter
                        </a>
                        <a href="{$socail_login_base_url}facebook?after_login_url=sso_jwt/login/{$current_sp}" class="service facebook" id="zd_facebook_login_link" target="_top">
                            <span data-icon="&#xe002;" class="icon"></span>
                            Sign in with Facebook
                        </a>
                        <a href="{$socail_login_base_url}google?after_login_url=sso_jwt/login/{$current_sp}" class="service google" target="_top">
                            <span data-icon="&#xe001;" class="icon"></span>
                            Sign in with Google
                        </a>
                    </div>
                    {undef $socail_login_base_url $current_sp}
                    {* end social logins *}

                </div>

                {* old school login *}
                <div class="services internal">
                    <form id="login-tab" method="post" action="{ezini( 'General', 'IdentityProviderURL', 'sso_jwt.ini' )}user/login">
                        <div id="login_full">

                            <div class="error-container">
                                {* ajax errors will be inserted here *}
                            </div>

                            <div class="xrow-loginbox">
                                <div>
                                    <input class="halfbox" type="text" size="10" name="Login" placeholder="{'Email'|i18n('extension/pt')}" id="id1" value="{$User:login|wash}" tabindex="1" style="color: #666;"/>
                                </div>
                                <div>
                                    <input class="halfbox" type="password" size="10" name="Password" placeholder="{'Password'|i18n('extension/pt')}" id="id2" value="" tabindex="1" style="color: #666;" />
                                </div>
                                <div>
                                    <input type="submit" class="button" name="LoginButton" value="{'Sign In'|i18n('extension/pt')}" tabindex="1">
                                    <div style="display:none" class="spinner text-center">
                                        <img src={"/icons/spiffygif_24x24.gif"|ezimage} alt="" />
                                    </div>
                                </div>
                                <input type="hidden" name="RedirectURI" value="sso_jwt/login/{ezini( 'General', 'CurrentServiceProvider', 'sso_jwt.ini' )}" data-sso-ajax-value="sso_jwt/loginajax/{ezini( 'General', 'CurrentServiceProvider', 'sso_jwt.ini' )}{$current_siteaccess}" />
                            </div>
                        </div>
                    </form>

                    {* forgot passwd *}
                    <div class="assistance">
                        <div class="forgot_password">
                            {def $forgot_password_url = concat( ezini( 'General', 'IdentityProviderURL', 'sso_jwt.ini' ), 'user/forgotpassword' )|ezurl( 'no' )}
                            <a href="{$forgot_password_url}">Forgot my password</a>
                        </div>
                    </div>
                    {* end forgot passwd *}

                    <div class="text-center">
                        <a href="#" class="btn btn-primary trigger-show-register-tab">No account yet?</a>
                    </div>

                </div>
                {* end old school login *}

            </div>
            {* end main box *}
        </div>
        <div role="tabpanel" class="tab-pane fade" id="profile" aria-labelledby="profile-tab"> {* new sign up *}
            <div class="reg">


                <div class="sign_up question row">
                    <h2 class="title col-xs-12 col-sm-8">New to {$site_name}? <span>- Register below to join the family</span></h2>
                    <div class="register col-xs-12" >

                        {def $ajax_sso_login_url = concat( ezini( 'General', 'IdentityProviderURL', 'sso_jwt.ini' ), 'sso_jwt/loginajax/', ezini( 'General', 'CurrentServiceProvider', 'sso_jwt.ini' ), $current_siteaccess )|ezurl( 'no' )}
                        {def $sso_login_url = concat( ezini( 'General', 'IdentityProviderURL', 'sso_jwt.ini' ), 'sso_jwt/login/', ezini( 'General', 'CurrentServiceProvider', 'sso_jwt.ini' ) )|ezurl( 'no' )}
                        <form id="register-tab" enctype="multipart/form-data"
                              data-sso-ajax-action={concat( ezini( 'General', 'IdentityProviderURL', 'sso_jwt.ini' ), 'sso_jwt/registerajax' )|ezurl}
                              data-emarsys-signup-url={'emarsys/signup'|ezurl}
                              action={concat( ezini( 'General', 'IdentityProviderURL', 'sso_jwt.ini' ), 'sso_jwt/register' )|ezurl} method="POST" name="Register">

                            <div class="error-container">
                                {* ajax errors will be inserted here *}
                            </div>

                            <div>
                                <input class="halfbox" type="text" placeholder="{'First name'|i18n('extension/pt')}" name="new_user_data[first_name]" value="" data-sso-ajax-name="ajax_first_name"
                                       id="first-name" data-validation="required" data-validation-error-msg="{'Please tell us your first name'|i18n('extension/pt')}"
                                />
                            </div>

                            <div>
                                <input type="text" placeholder="{'Last name'|i18n('extension/pt')}" name="new_user_data[last_name]" value="" data-sso-ajax-name="ajax_last_name"
                                       id="last-name" data-validation="required" data-validation-error-msg="{'Please tell us your last name'|i18n('extension/pt')}"
                                />
                            </div>

                            <div>
                                <input type="text" id="register-email" placeholder="{'Email'|i18n('extension/pt')}" name="new_user_data[email]" value="" data-sso-ajax-name="ajax_email"
                                       id="email" data-validation="required email" data-validation-error-msg="{'Please enter your email address'|i18n('extension/pt')}"
                                />
                            </div>

                            <div>
                                <input type="password" placeholder="{'Password'|i18n('extension/pt')}" name="new_user_data[password]" value="" data-sso-ajax-name="ajax_password"
                                       id="password" data-validation="required" data-validation-error-msg="{'Please give yourself a password'|i18n('extension/pt')}"
                                />
                            </div>

                            <div>
                                <input type="password" placeholder="{'Password confirm'|i18n('extension/pt')}" name="new_user_data[password_confirm]" value="" data-sso-ajax-name="ajax_password_confirm"
                                       id="password-confirm" data-validation="required" data-validation-error-msg="{'Please confirm your password'|i18n('extension/pt')}"
                                />
                            </div>

                            {* emarsys *}
                            <div class="email-opt-in-box">
                                <input type="checkbox" id="email-opt-in" name="email-opt-in">
                                <label for="email-opt-in">{"sign me up for the latest news (you can unsubscribe at any time)."|i18n('extension/pt')}</label>
                            </div>                          

                            <div class="g-recaptcha" data-sitekey="{ezini( 'ReCaptcha', 'SiteKey', 'site.ini' )}" data-size="compact"></div>

                            <div>
                                <input type="submit" name="PublishButton" value="{'Create an Account'|i18n('design/standard/user')}" />
                                <div class="privacy-policy-box">
                                    <input type="checkbox" id="accept-privacy-policy" data-validation="required" data-validation-error-msg="{'Please indicate you agree to the Privacy Policy'|i18n('extension/pt'))}">
                                    <label for="accept-privacy-policy" data-validation="required">{"By creating your account you agree to our "|i18n('extension/pt')}
                                        <a href={ '/Support/Privacy-Policy'|ezurl} target="_blank">{"Privacy Policy"|i18n('extension/pt')}</a>
                                    </label>
                                </div>
                                <input type="hidden" name="new_user_data[RedirectAfterUserRegister]" value="{$sso_login_url}" data-sso-ajax-value="{$ajax_sso_login_url}" data-sso-ajax-name="ajax_RedirectAfterUserRegister"/>

                                <div style="display:none" class="spinner text-center">
                                    <br/><br/>
                                    <img src={"/icons/spiffygif_24x24.gif"|ezimage} alt="" />
                                </div>

                            </div>
                        </form>
                        {* we need to be sure, there will be RegisterUserID in the session when sign-up form is submitted *}
                        <img src="{concat( ezini( 'General', 'IdentityProviderURL', 'sso_jwt.ini' ), 'user/register' )|ezurl( 'no' )}" style="width: 0px; height: 0px;"/>
                    </div>

                    <div class="reg_info col-xs-12 col-sm-4">
                        <h2>{'Why Sign Up?'|i18n('design/standard/user')}</h2>
                        {*<img src="/extension/pt/design/pt/images/graphics/placeholder.jpg" alt="girl with questioning look" />*}
                        <p>{'Keep informed about product upgrades or safety announcements specific to your site_name product.'|i18n('design/standard/user', '', hash('site_name', $site_name))}</p>
                        <ul>
                            <li>{'order history'|i18n('design/standard/user')}</li>
                            <li>{'product registration'|i18n('design/standard/user')}</li>
                            <li>{'support history ticketing system'|i18n('design/standard/user')}</li>
                            <li>{'important notices for updates/recalls'|i18n('design/standard/user')}</li>
                        </ul>
                    </div>
                </div>

                {* end new sign up *}
            </div>
        </div>
    </div> </div>

<div class="assistance center">Forms or support pages not loading? <a style="color: #39c;" target="_blank" href="https://support.philandteds.com/hc/en-us/articles/204251784">- Try this</a></div>

{* end login wrapper *}

<script type="text/javascript" src={'javascript/jquery.form-validator.min.js'|ezdesign()}></script>

{literal}
<script type="text/javascript">
    $(document).ready(function() {
        $.validate({
            'form': "form#register-tab"//,
//            'scrollToTopOnError': false,
//            'errorMessagePosition': 'inline'
        });
    });
</script>
{/literal}
