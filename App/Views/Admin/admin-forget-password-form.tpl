{% extends "layout.tpl" %}
{% block content %}
<div class="section section-admin-login-form text-center">
    <div class="container">
        <h2 class="title">You forgot your password?<br>Use your email account to renew it.</h2>
        <p class="description">You will receive an authentication code to use in another dedicated form.<br>Then , you will be able to perform this action!</p>
        <!-- User notice message -->
        <div class="row">
            <div class="col-lg-8 text-center col-md-10 ml-auto mr-auto">
                <!-- Success message box is on the same page here. -->
                <p class="alert alert-success form-success{{ success == 0 ? ' form-hide'}}" role="alert">
                    <i class="now-ui-icons ui-2_like"></i>&nbsp;&nbsp;<strong>WELL DONE!</strong>&nbsp;You request was treated successfully.<br>You are going to receive a password renewal email in a few minutes!<br>You will be able to change your password<br>by clicking on your personal link.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                </p>
                <p class="alert alert-danger form-error{{ errors == 0 ? ' form-hide'}}" role="alert">
                    <i class="now-ui-icons ui-1_bell-53"></i>&nbsp;&nbsp;<strong>ERRORS!</strong>&nbsp;Change a few things up and try submitting again.{% if errors['fpf_renewalCode'] is defined %}<br>{{ errors['fpf_renewalCode']|raw }}{% endif %}{% if errors['fpf_check'] is defined %}<br>{{ errors['fpf_check']|raw }}{% endif %}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                </p>
            </div>
        </div>
        <!-- End user notice message -->
        <!-- Forget password form -->
        <div class="row">
            <div class="col-lg-5 text-center col-md-8 col-sm-10 ml-auto mr-auto">
                <div class="row card p-4" data-background-color="black">
                        <form novalidate class="forget-password-form form-nospam" method="post" action="/admin/request-new-password" data-try-validation="{{ tryValidation }}">
                            <p class="text-danger{{ errors['fpf_email'] is not defined ? ' form-hide'}}" role="alert">&nbsp;{{ errors['fpf_email']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                            <div class="input-group phpblog-field-group form-group-no-border input-lg">
                                <span class="input-group-addon">
                                    <i class="now-ui-icons ui-1_email-85"></i>
                                </span>
                                <input type="email" class="form-control" aria-label="Your email" id="fpf_email" name="fpf_email" placeholder="Email..." value="{{ email|e('html_attr') }}">
                            </div>
                            <input type="hidden" id="fpf_check" name="{{ fpfTokenIndex }}" value="{{ fpfTokenValue }}">
                            <p class="text-danger{{ errors['fpf_noSpam'] is not defined ? ' form-hide' }}" role="alert">&nbsp;{{ errors['fpf_noSpam']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                            {# No Spam tools "captcha" #}
                            <div class="nospam-container">
                            {% for i in 0..fpfNoSpam|length - 1 %}
                                {% if fpfNoSpam[i].input is defined %}
                                    {% set attributes = [] %}
                                    {% set label = '' %}
                                    {% for key, value in fpfNoSpam[i].input if value != false %}
                                        {% set attributes = attributes|merge([key~'="'~value|e('html_attr')~'"']) %}
                                    {% endfor %}
                                    {% if fpfNoSpam[i].label != false %}
                                        <label for="{{ fpfNoSpam[i].name }}" class="phpblog-label text-neutral">{{ fpfNoSpam[i].label }}</label>&nbsp;
                                    {% endif %}
                                    <input {{ attributes|join(' ')|raw }}>
                                {% endif %}
                            {% endfor %}
                            </div>
                            {#  End No Spam tools "captcha" #}
                            <div class="send-button">
                                <button type="submit" class="btn btn-warning btn-lg" name="fpf_submit" value="{{ submit }}">GET RENEWAL CODE</button>
                            </div>
                            <p class="form-text">All fields are mandatory.</p>
                        </form>
                </div>
            </div>
        </div>
        <!-- End forget password form -->
    </div>
</div>
{% endblock %}