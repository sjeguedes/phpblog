{% extends "layout.tpl" %}
{% block content %}
<div class="section section-admin-register-form text-center">
    <div class="container">
        <h2 class="title">Please register to create your account.<br></h2>
        <p class="description">After registration, you will have to activate your account.<br>Then you will become a member able to use back office!</p>
        <!-- User notice message -->
        <div class="row">
            <div class="col-lg-8 text-center col-md-10 ml-auto mr-auto">
                <!-- Success message box is used here when no redirection is processed! -->
                <p class="alert alert-success form-success{{ success == 0 ? ' form-hide'}}" role="alert">
                    <i class="now-ui-icons ui-2_like"></i>&nbsp;&nbsp;<strong>WELL DONE!</strong>&nbsp;You registered successfully.<br>You are going to receive an account validation email in a few minutes!<br>You will be able to activate your registration definitively<br>by clicking on your personal link.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                </p>
                <p class="alert alert-danger form-error{{ errors == 0 ? ' form-hide'}}" role="alert">
                    <i class="now-ui-icons ui-1_bell-53"></i>&nbsp;&nbsp;<strong>ERRORS!</strong>&nbsp;Change a few things up and try submitting again.{% if errors['ref_register'] is defined %}<br>{{ errors['ref_register']|raw }}{% endif %}{% if errors['ref_check'] is defined %}<br>{{ errors['ref_check']|raw }}{% endif %}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                </p>
            </div>
        </div>
        <!-- End user notice message -->
        <!-- register form -->
        <div class="row">
            <div class="col-lg-5 text-center col-md-8 col-sm-10 ml-auto mr-auto">
                <div class="row card p-4" data-background-color="black">
                        <form novalidate class="register-form" method="post" action="/admin/register" data-try-validation="{{ tryValidation }}">
                            <p class="text-danger{{ errors['ref_familyName'] is not defined ? ' form-hide'}}" role="alert">&nbsp;{{ errors['ref_familyName']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                            <div class="input-group phpblog-field-group form-group-no-border input-lg">
                                <span class="input-group-addon">
                                    <i class="now-ui-icons users_single-02"></i>
                                    <i class="now-ui-icons users_single-02"></i>
                                </span>
                                <input type="text" class="form-control" aria-label="Your family name" id="ref_familyName" name="ref_familyName" placeholder="FAMILY NAME..." value="{{ familyName|e('html_attr') }}">
                            </div>
                            <p class="text-danger{{ errors['ref_firstName'] is not defined ? ' form-hide'}}" role="alert">&nbsp;{{ errors['ref_firstName']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                            <div class="input-group phpblog-field-group form-group-no-border input-lg">
                                <span class="input-group-addon">
                                    <i class="now-ui-icons users_single-02"></i>
                                </span>
                                <input type="text" class="form-control" aria-label="Your first name" id="ref_firstName" name="ref_firstName" placeholder="First name..." value="{{ firstName|e('html_attr') }}">
                            </div>
                            <p class="text-danger{{ errors['ref_nickName'] is not defined ? ' form-hide'}}" role="alert">&nbsp;{{ errors['ref_nickName']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                            <div class="input-group phpblog-field-group form-group-no-border input-lg">
                                <span class="input-group-addon">
                                    <i class="now-ui-icons business_badge"></i>
                                </span>
                                <input type="text" class="form-control" aria-label="Your nickname" id="ref_nickName" name="ref_nickName" placeholder="Nickname..." value="{{ nickName|e('html_attr') }}">
                            </div>
                            <p class="text-danger{{ errors['ref_email'] is not defined ? ' form-hide'}}" role="alert">&nbsp;{{ errors['ref_email']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                            <div class="input-group phpblog-field-group form-group-no-border input-lg">
                                <span class="input-group-addon">
                                    <i class="now-ui-icons ui-1_email-85"></i>
                                </span>
                                <input type="email" class="form-control" aria-label="Your email" id="ref_email" name="ref_email" placeholder="Email..." value="{{ email|e('html_attr') }}">
                            </div>
                            <p class="text-danger{{ errors['ref_password'] is not defined ? ' form-hide'}}" role="alert">&nbsp;{{ errors['ref_password']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                            <div class="input-group phpblog-field-group form-group-no-border input-lg">
                                <span class="input-group-addon">
                                    <i class="now-ui-icons ui-1_lock-circle-open"></i>
                                </span>
                                <input type="password" class="form-control" aria-label="Your password" id="ref_password" name="ref_password" placeholder="Password..." value="{{ password|e('html_attr') }}">
                            </div>
                            <p class="text-danger{{ errors['ref_passwordConfirmation'] is not defined ? ' form-hide'}}" role="alert">&nbsp;{{ errors['ref_passwordConfirmation']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                            <div class="input-group phpblog-field-group form-group-no-border input-lg">
                                <span class="input-group-addon">
                                    <i class="now-ui-icons ui-1_lock-circle-open"></i>
                                </span>
                                <input type="password" class="form-control" aria-label="Your password confirmation" id="ref_passwordConfirmation" name="ref_passwordConfirmation" placeholder="Confirm your password..." value="{{ passwordConfirmation|e('html_attr') }}">
                            </div>
                            <p class="text-danger{{ errors['g-recaptcha-response'] is not defined ? ' form-hide'}}" role="alert">&nbsp;{{ errors['g-recaptcha-response']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                            <div id="form-recaptcha" class="g-recaptcha" data-sitekey="{{ siteKey }}"></div>
                            <input type="hidden" id="ref_check" name="{{ refTokenIndex }}" value="{{ refTokenValue }}">
                            <div class="send-button">
                                <button type="submit" class="btn btn-warning btn-lg" name="ref_submit" value="{{ submit }}">CREATE ACCOUNT</button>
                            </div>
                            <p class="form-text">All fields are mandatory.</p>
                            <hr class="separator">
                            <div class="checkbox">
                                <strong>-&nbsp;HELP&nbsp;-</strong><br>
                                <input id="ref_show_password" type="checkbox">
                                <label for="ref_show_password" class="unmask-pwd">
                                    <span class="text-muted phpblog-form-text">Show passwords when typing?</span><br>
                                </label>
                                <p class="text-muted mt-2 phpblog-form-text">Get information for<br><a class="text-muted" href="#" data-toggle="modal" data-target="#ref-pwd-info-modal" title="Required password format">required password format&nbsp;<i class="fa fa-question-circle fa-lg" aria-hidden="true"></i></a></p>
                            </div>
                        </form>
                </div>
            </div>
        </div>
        <!-- End register form -->
    </div>
</div>
<!-- Password info modal -->
<div class="modal fade modal-mini" id="ref-pwd-info-modal" tabindex="-1" role="dialog" aria-labelledby="Required password format" aria-hidden="true">
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