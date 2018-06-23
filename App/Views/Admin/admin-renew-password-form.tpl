{% extends 'layout.tpl' %}
{% block content %}
<div class="section section-admin-renew-password-form text-center">
    <div class="container">
        <h2 class="title">Please renew your password<br>with your email account<br>and authentication code (token)<br>received by email.<br></h2>
        <p class="description">After confirmation, your active password will be set.<br>Then you will be able to login and use back office again!</p>
        <!-- User notice message -->
        <div class="row">
            <div class="col-lg-8 text-center col-md-10 ml-auto mr-auto">
                <!-- Success message box is used here when no redirection is processed! -->
                <p class="alert alert-success form-success{{ success == 0 ? ' form-hide' }}" role="alert">
                    <i class="now-ui-icons ui-2_like"></i>&nbsp;&nbsp;<strong>WELL DONE!</strong>&nbsp;You password was updated successfully.<br>Now, you are able to use it immediately to login!
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                </p>
                <p class="alert alert-danger form-error{{ errors == 0 ? ' form-hide' }}" role="alert">
                    <i class="now-ui-icons ui-1_bell-53"></i>&nbsp;&nbsp;<strong>ERRORS!</strong>&nbsp;Change a few things up and try submitting again.{% if errors['rpf_renewal'] is defined %}<br>{{ errors['rpf_renewal']|raw }}{% endif %}{% if errors['rpf_check'] is defined %}<br>{{ errors['rpf_check']|raw }}{% endif %}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                </p>
            </div>
        </div>
        <!-- End user notice message -->
        <!-- Renew password form -->
        <div class="row">
            <div class="col-lg-5 text-center col-md-8 col-sm-10 ml-auto mr-auto">
                <div class="row card p-4" data-background-color="black">
                            <form novalidate class="renew-password-form" method="post" action="/admin/renew-password" data-try-validation="{{ tryValidation }}">
                            <p class="text-danger{{ errors['rpf_email'] is not defined ? ' form-hide' }}" role="alert">&nbsp;{{ errors['rpf_email']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                            <div class="input-group phpblog-field-group form-group-no-border input-lg">
                                <span class="input-group-addon">
                                    <i class="now-ui-icons ui-1_email-85"></i>
                                </span>
                                <input type="email" class="form-control" aria-label="Your email" id="rpf_email" name="rpf_email" placeholder="Email..." value="{{ email|e('html_attr') }}">
                            </div>
                            <p class="text-danger{{ errors['rpf_passwordUpdateToken'] is not defined ? ' form-hide' }}" role="alert">&nbsp;{{ errors['rpf_passwordUpdateToken']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                            <div class="input-group phpblog-field-group form-group-no-border input-lg">
                                <span class="input-group-addon">
                                    <i class="now-ui-icons objects_key-25"></i>
                                </span>
                                <input type="text" class="form-control" aria-label="Your token" id="rpf_passwordUpdateToken" name="rpf_passwordUpdateToken" placeholder="Authentication token received by email..." maxlength="15" value="{{ passwordUpdateToken|e('html_attr') }}">
                            </div>
                            <p class="text-danger{{ errors['rpf_password'] is not defined ? ' form-hide' }}" role="alert">&nbsp;{{ errors['rpf_password']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                            <div class="input-group phpblog-field-group form-group-no-border input-lg">
                                <span class="input-group-addon">
                                    <i class="now-ui-icons ui-1_lock-circle-open"></i>
                                </span>
                                <input type="password" class="form-control" aria-label="Your password" id="rpf_password" name="rpf_password" placeholder="Password..." value="{{ password|e('html_attr') }}">
                            </div>
                            <p class="text-danger{{ errors['rpf_passwordConfirmation'] is not defined ? ' form-hide' }}" role="alert">&nbsp;{{ errors['rpf_passwordConfirmation']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                            <div class="input-group phpblog-field-group form-group-no-border input-lg">
                                <span class="input-group-addon">
                                    <i class="now-ui-icons ui-1_lock-circle-open"></i>
                                </span>
                                <input type="password" class="form-control" aria-label="Your password confirmation" id="rpf_passwordConfirmation" name="rpf_passwordConfirmation" placeholder="Confirm your password..." value="{{ passwordConfirmation|e('html_attr') }}">
                            </div>
                            <p class="text-danger{{ errors['g-recaptcha-response'] is not defined ? ' form-hide' }}" role="alert">&nbsp;{{ errors['g-recaptcha-response']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                            <div id="form-recaptcha" class="g-recaptcha" data-sitekey="{{ siteKey }}"></div>
                            <input type="hidden" id="rpf_check" name="{{ rpfTokenIndex }}" value="{{ rpfTokenValue }}">
                            <div class="send-button">
                                <button type="submit" class="btn btn-warning btn-lg" name="rpf_submit" value="{{ submit }}">CONFIRM NEW PASSWORD</button>
                            </div>
                            <p class="form-text">All fields are mandatory.</p>
                            <hr class="separator">
                            <div class="checkbox">
                                <strong>-&nbsp;HELP&nbsp;-</strong><br>
                                <input id="rpf_show_password" type="checkbox">
                                <label for="rpf_show_password" class="unmask-pwd">
                                    <span class="text-muted phpblog-form-text">Show passwords when typing?</span><br>
                                </label>
                                <p class="text-muted mt-2 phpblog-form-text">Get information for<br><a class="text-muted" href="#" data-toggle="modal" data-target="#rpf-pwd-info-modal" title="Required password format">required password format&nbsp;<i class="fa fa-question-circle fa-lg" aria-hidden="true"></i></a></p>
                            </div>
                        </form>
                </div>
            </div>
        </div>
        <!-- End renew password form -->
        <p>
            <a class="btn btn-info" href="/admin/request-new-password" title="Request a new password process"><i class="fa fa-angle-left" aria-hidden="true"></i>&nbsp;You forgot your password, please click here!&nbsp;<i class="fa fa-life-ring fa-lg" aria-hidden="true"></i></a>
        </p>
    </div>
</div>
<!-- Password info modal -->
<div class="modal fade modal-mini" id="rpf-pwd-info-modal" tabindex="-1" role="dialog" aria-labelledby="Required password format" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header justify-content-center">
                <div class="modal-profile">
                    <i class="now-ui-icons objects_key-25"></i>
                </div>
            </div>
            <div class="modal-body">
                <p>
                    <strong>INFO</strong><br>
                    Your password<br>must contain the following<br>required format:
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