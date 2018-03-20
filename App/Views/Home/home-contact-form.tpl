<div id="bloc-contact-us" class="section section-contact-us text-center">
    <div class="container">
        <h2 class="title">You maybe want to work with me?</h2>
        <p class="description">Your project is very important to me ;-)</p>
        <!-- User notice message -->
        <div class="row">
            <div class="col-lg-8 text-center col-md-10 ml-auto mr-auto">
                <p class="alert alert-success form-success{{ success == 0 ? ' form-hide'}}" role="alert">
                    <i class="now-ui-icons ui-2_like"></i>&nbsp;&nbsp;<strong>WELL DONE!</strong>&nbsp;Your message was sent successfully.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                </p>
                <p class="alert alert-danger form-error{{ errors == 0 ? ' form-hide'}}" role="alert">
                    <i class="now-ui-icons ui-1_bell-53"></i>&nbsp;&nbsp;<strong>ERRORS!</strong>&nbsp;{% if errors['cf_sending'] is defined %}{{ errors['cf_sending']|raw }}{% else %}Change a few things up and try submitting again.{% if errors['cf_check'] is defined %}<br>{{ errors['cf_check']|raw }}{% endif %}{% endif %}
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
            <div class="col-lg-5 text-center col-md-8 col-sm-10 ml-auto mr-auto">
                <div class="card p-4" data-background-color="black">
                    <div{{ ajaxModeForContactForm == 1 ? ' id="cf-ajax-wrapper"' }}>
                        {% block contactForm %}
                        <form novalidate class="contact-form" data-ajax="{{ ajaxModeForContactForm }}" data-not-sent="{{ sending }}" method="post" action="/">
                            <p class="text-danger{{ errors['cf_familyName'] is not defined ? ' form-hide'}}" role="alert">&nbsp;{{ errors['cf_familyName']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                            <div class="input-group phpblog-field-group form-group-no-border input-lg">
                                <span class="input-group-addon">
                                    <i class="now-ui-icons users_single-02"></i>
                                    <i class="now-ui-icons users_single-02"></i>
                                </span>
                                <input type="text" class="form-control" aria-label="Your family name" id="cf_familyName" name="cf_familyName" placeholder="FAMILY NAME..." value="{{ familyName|e('html_attr') }}">
                            </div>
                            <p class="text-danger{{ errors['cf_firstName'] is not defined ? ' form-hide'}}" role="alert">&nbsp;{{ errors['cf_firstName']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                            <div class="input-group phpblog-field-group form-group-no-border input-lg">
                                <span class="input-group-addon">
                                    <i class="now-ui-icons users_single-02"></i>
                                </span>
                                <input type="text" class="form-control" aria-label="Your first name" id="cf_firstName" name="cf_firstName" placeholder="First name..." value="{{ firstName|e('html_attr') }}">
                            </div>
                            <p class="text-danger{{ errors['cf_email'] is not defined ? ' form-hide'}}" role="alert">&nbsp;{{ errors['cf_email']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                            <div class="input-group phpblog-field-group form-group-no-border input-lg">
                                <span class="input-group-addon">
                                    <i class="now-ui-icons ui-1_email-85"></i>
                                </span>
                                <input type="email" class="form-control" aria-label="Your email" id="cf_email" name="cf_email" placeholder="Email..." value="{{ email|e('html_attr') }}">
                            </div>
                            <p class="text-danger{{ errors['cf_message'] is not defined ? ' form-hide'}}" role="alert">&nbsp;{{ errors['cf_message']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                            <div class="textarea-container input-group phpblog-field-group form-group-no-border input-lg">
                                <span class="input-group-addon">
                                    <i class="now-ui-icons ui-2_chat-round"></i>
                                </span>
                                <textarea class="form-control" aria-label="Your message" id="cf_message" name="cf_message" rows="4" cols="80" placeholder="Type a message...">{{ message|raw }}</textarea>
                            </div>
                            <p class="text-danger{{ errors['g-recaptcha-response'] is not defined ? ' form-hide'}}" role="alert">&nbsp;{{ errors['g-recaptcha-response']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
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
        </div>
        <!-- End contact form -->
    </div>
</div>