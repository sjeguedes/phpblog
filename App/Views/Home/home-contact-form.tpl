<div class="section section-contact-us text-center">
    <div class="container">
        <h2 class="title">You maybe want to work with me?</h2>
        <p class="description">Your project is very important to me ;-)</p>
        <!-- User notice message -->
        <div class="row">
            <div class="col-lg-8 text-center col-md-10 ml-auto mr-auto">
                <p class="alert alert-success cf-success{{ success == 0 ? ' cf-hide'}}" role="alert">
                    <i class="now-ui-icons ui-2_like"></i>&nbsp;&nbsp;<strong>WELL DONE!</strong>&nbsp;Your message was sent successfully.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                </p>
                <p class="alert alert-danger cf-error{{ errors == 0 ? ' cf-hide'}}" role="alert">
                    <i class="now-ui-icons ui-1_bell-53"></i>&nbsp;&nbsp;<strong>ERRORS!</strong>&nbsp;{% if errors['sending'] is defined %}{{ errors['sending']|raw }}{% else %}Change a few things up and try submitting again.{% if errors['check'] is defined %}<br>{{ errors['check']|raw }}{% endif %}{% endif %}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                </p>
            </div>
        </div>
        <!-- End user notice message -->
        <!-- Contact form -->
        <div class="row">
            <div class="card py-4" data-background-color="black">
                <div{{ ajaxModeForContactForm == 1 ? ' id="cf-ajax-wrapper"' }} class="col-lg-6 text-center col-md-8 ml-auto mr-auto">
                    {% block contactForm %}
                    <form class="contact-form" data-ajax="{{ ajaxModeForContactForm }}" data-not-sent="{{ sending }}" method="post" action="/">
                        <p class="text-danger{{ errors['familyName'] is not defined ? ' cf-hide'}}" role="alert">&nbsp;{{ errors['familyName']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                        <div class="input-group phpblog-field-group form-group-no-border input-lg">
                            <span class="input-group-addon">
                                <i class="now-ui-icons users_single-02"></i>
                                <i class="now-ui-icons users_single-02"></i>
                            </span>
                            <input type="text" class="form-control" aria-label="Your family name" id="cf_familyName" name="cf_familyName" placeholder="FAMILY NAME..." value="{{ familyName|e('html_attr') }}">
                        </div>
                        <p class="text-danger{{ errors['firstName'] is not defined ? ' cf-hide'}}" role="alert">&nbsp;{{ errors['firstName']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                        <div class="input-group phpblog-field-group form-group-no-border input-lg">
                            <span class="input-group-addon">
                                <i class="now-ui-icons users_single-02"></i>
                            </span>
                            <input type="text" class="form-control" aria-label="Your first name" id="cf_firstName" name="cf_firstName" placeholder="First name..." value="{{ firstName|e('html_attr') }}">
                        </div>
                        <p class="text-danger{{ errors['email'] is not defined ? ' cf-hide'}}" role="alert">&nbsp;{{ errors['email']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                        <div class="input-group phpblog-field-group form-group-no-border input-lg">
                            <span class="input-group-addon">
                                <i class="now-ui-icons ui-1_email-85"></i>
                            </span>
                            <input type="text" class="form-control" aria-label="Your email" id="cf_email" name="cf_email" placeholder="Email..." value="{{ email|e('html_attr') }}">
                        </div>
                        <p class="text-danger{{ errors['message'] is not defined ? ' cf-hide'}}" role="alert">&nbsp;{{ errors['message']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                        <div class="textarea-container input-group phpblog-field-group form-group-no-border input-lg">
                            <span class="input-group-addon">
                                <i class="now-ui-icons ui-2_chat-round"></i>
                            </span>
                            <textarea class="form-control" aria-label="Your message" id="cf_message" name="cf_message" rows="4" cols="80" placeholder="Type a message...">{{ message|raw }}</textarea>
                        </div>
                        <p class="text-danger{{ errors['g-recaptcha-response'] is not defined ? ' cf-hide'}}" role="alert">&nbsp;{{ errors['g-recaptcha-response']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                        <div id="cf-recaptcha" class="g-recaptcha" data-sitekey="{{ siteKey }}"></div>
                        <input type="hidden" id="cf_check" name="{{ cfTokenIndex }}" value="{{ cfTokenValue }}">
                        {% if ajaxModeForContactForm == 0 %}
                        <input type="hidden" id="cf_contact" name="cf_call" value="contact">
                        {% endif %}
                        <div class="send-button">
                            <button type="submit" class="btn btn-warning btn-lg" name="cf_submit" value="{{ submit }}">SEND YOUR MESSAGE</button>
                        </div>
                        <p class="form-text">All fields are mandatory.</p>
                    </form>
                    {% endblock %}
                </div>
            </div>
        </div>
        <!-- End contact form -->
    </div>
</div>