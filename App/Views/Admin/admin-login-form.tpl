{% extends 'layout.tpl' %}
{% block content %}
<div class="section section-admin-login-form text-center">
    <div class="container">
        <h2 class="title">Please login with your email account<br>to access back office.</h2>
        <p class="description">This area is reserved to registered members!</p>
        <!-- User notice message -->
        <div class="row">
            <div class="col-lg-8 text-center col-md-10 ml-auto mr-auto">
                <p class="alert alert-success form-success{{ passwordRenewalSuccess == 0 ? ' form-hide'}}" role="alert">
                    <i class="now-ui-icons ui-2_like"></i>&nbsp;&nbsp;<strong>WELL DONE!</strong>&nbsp;You password was updated successfully.<br>Now, you are able to use it immediately to login!
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                </p>
                <p class="alert alert-danger form-error{{ errors == 0 ? ' form-hide'}}" role="alert">
                    {% if errors['expiredSession'] is defined %}
                    <i class="now-ui-icons ui-1_bell-53"></i>&nbsp;&nbsp;<strong>YOU ARE NOT AUTHENTICATED ANYMORE!</strong>{% if errors['expiredSession']['unauthorizedFromAdmin'] is defined %}<br>You were disconnected for security reason.{% endif %}<br>Your session expired. Please login again.
                    {% else %}
                    <i class="now-ui-icons ui-1_bell-53"></i>&nbsp;&nbsp;<strong>ERRORS!</strong>&nbsp;Change a few things up and try submitting again.{% if errors['lif_login'] is defined %}<br>{{ errors['lif_login']|raw }}{% endif %}{% if errors['lif_check'] is defined %}<br>{{ errors['lif_check']|raw }}{% endif %}
                    {% endif %}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                </p>
            </div>
        </div>
        <!-- End user notice message -->
        <!-- Login form -->
        <div class="row">
            <div class="col-lg-5 text-center col-md-8 col-sm-10 ml-auto mr-auto">
                <div class="row card p-4" data-background-color="black">
                        <form novalidate class="login-form" method="post" action="/admin/login" data-try-validation="{{ tryValidation }}">
                            <p class="text-danger{{ errors['lif_email'] is not defined ? ' form-hide'}}" role="alert">&nbsp;{{ errors['lif_email']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                            <div class="input-group phpblog-field-group form-group-no-border input-lg">
                                <span class="input-group-addon">
                                    <i class="now-ui-icons ui-1_email-85"></i>
                                </span>
                                <input type="email" class="form-control" aria-label="Your email" id="lif_email" name="lif_email" placeholder="Email..." value="{{ email|e('html_attr') }}">
                            </div>
                            <p class="text-danger{{ errors['lif_password'] is not defined ? ' form-hide'}}" role="alert">&nbsp;{{ errors['lif_password']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                            <div class="input-group phpblog-field-group form-group-no-border input-lg">
                                <span class="input-group-addon">
                                    <i class="now-ui-icons ui-1_lock-circle-open"></i>
                                </span>
                                <input type="password" class="form-control" aria-label="Your password" id="lif_password" name="lif_password" placeholder="Password..." value="{{ password|e('html_attr') }}">
                            </div>
                            <p class="text-danger{{ errors['g-recaptcha-response'] is not defined ? ' form-hide'}}" role="alert">&nbsp;{{ errors['g-recaptcha-response']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                            <div id="form-recaptcha" class="g-recaptcha" data-sitekey="{{ siteKey }}"></div>
                            <input type="hidden" id="lif_check" name="{{ lifTokenIndex }}" value="{{ lifTokenValue }}">
                            <div class="send-button">
                                <button type="submit" class="btn btn-warning btn-lg" name="lif_submit" value="{{ submit }}">LET'S GO</button>
                            </div>
                            <p class="form-text">All fields are mandatory.</p>
                            <hr class="separator">
                            <div class="checkbox">
                                <strong>-&nbsp;HELP&nbsp;-</strong><br>
                                <input id="lif_show_password" type="checkbox">
                                <label for="lif_show_password" class="unmask-pwd">
                                    <span class="text-muted phpblog-form-text">Show password when typing?</span><br>
                                </label>
                                <p class="text-muted mt-2 phpblog-form-text">Get information for<br><a class="text-muted" href="#" data-toggle="modal" data-target="#lif-pwd-info-modal" title="Required password format">required password format&nbsp;<i class="fa fa-question-circle fa-lg" aria-hidden="true"></i></a></p>
                            </div>
                        </form>
                </div>
            </div>
        </div>
        <!-- End login form -->
        <p>
            <a class="btn btn-warning" href="/admin/request-new-password" title="Please ask for password renewal athentication code."><i class="fa fa-angle-right" aria-hidden="true"></i>&nbsp;You forgot your password, please click here!&nbsp;<i class="fa fa-life-ring fa-lg" aria-hidden="true"></i></a><br>
            <a class="btn" href="/admin/register" title="Please register to create an account."><i class="fa fa-angle-left" aria-hidden="true"></i>&nbsp;You are not registered yet, please click here!&nbsp;<i class="fa fa-user-plus fa-lg" aria-hidden="true"></i></a>
        </p>
    </div>
</div>
<!-- Password info modal -->
<div class="modal fade modal-mini" id="lif-pwd-info-modal" tabindex="-1" role="dialog" aria-labelledby="Required password format" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header justify-content-center">
                <div class="modal-profile">
                    <i class="now-ui-icons objects_key-25"></i>
                </div>
            </div>
            <div class="modal-body">
                <p>
                    <strong>REMINDER</strong><br>
                    Your registered password<br>contains the following<br>required format:
                    <ul class="phpblog-list">
                        <li>1 (or more) special character<br><i class="fa fa-angle-right" aria-hidden="true"></i>&nbsp;<a class="text-muted" href="https://www.owasp.org/index.php/Password_special_characters" title="Complete list of special characters" target="_blank">look at complete list.</a>&nbsp;<i class="fa fa-info-circle" aria-hidden="true"></i></li>
                        <li>1 (or more) lowercase letter</li>
                        <li>1 (or more) uppercase letter</li>
                        <li>1 (or more) number</li>
                        <li>a minimum of 8 characters</li>
                    </ul>
                    There is no order to use them!
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" data-dismiss="modal" title="Ok, that's more clear now!">OK, THAT'S MORE CLEAR NOW!</button>
            </div>
        </div>
    </div>
</div>
<!-- End password info modal -->
{% endblock %}