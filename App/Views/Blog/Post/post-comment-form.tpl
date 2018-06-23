<div class="section section-comment-post text-center">
    <div class="container">
        <h2 class="title">Leave a comment?</h2>
        <p class="description">I invite you to react!</p>
        <!-- User notice message -->
        <div class="row">
            <div class="col-lg-8 text-center col-md-10 ml-auto mr-auto">
                <p class="alert alert-success form-success{{ success == 0 ? ' form-hide' }}" role="alert">
                    <i class="now-ui-icons ui-2_like"></i>&nbsp;&nbsp;<strong>WELL DONE!</strong>&nbsp;Your comment was saved successfully.<br>It is waiting for moderation!
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                </p>
                <p class="alert alert-danger form-error{{ errors == 0 ? ' form-hide' }}" role="alert">
                    <i class="now-ui-icons ui-1_bell-53"></i>&nbsp;&nbsp;<strong>ERRORS!</strong>&nbsp;Change a few things up and try submitting again.{% if errors['pcf_check'] is defined %}<br>{{ errors['pcf_check']|raw }}{% endif %}{% if errors['pcf_unsaved'] is defined %}<br>{{ errors['pcf_unsaved']|raw }}{% endif %}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                </p>
            </div>
        </div>
        <!-- End user notice message -->
        <div class="row">
            <div class="col-lg-5 text-center col-md-8 col-sm-10 ml-auto mr-auto">
            	<div class="card p-4" data-background-color="black">
                    <form novalidate class="comment-form form-switch-input" method="post" action="/comment-post/{{ post[0].id }}"  data-try-validation="{{ tryValidation }}">
    	            	<p class="text-danger{{ errors['pcf_nickName'] is not defined ? ' form-hide' }}" role="alert">&nbsp;{{ errors['pcf_nickName']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                        <div class="input-group phpblog-field-group form-group-no-border input-lg">
                            <span class="input-group-addon">
                                <i class="now-ui-icons users_circle-08"></i>
                            </span>
                            <input type="text" class="form-control" aria-label="Your nickname" id="pcf_nickName" name="pcf_nickName" placeholder="Nickname..." value="{{ nickName|e('html_attr') }}">
                        </div>
                        <p class="text-danger{{ errors['pcf_email'] is not defined ? ' form-hide' }}" role="alert">&nbsp;{{ errors['pcf_email']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                        <div class="input-group phpblog-field-group form-group-no-border input-lg">
                            <span class="input-group-addon">
                                <i class="now-ui-icons ui-1_email-85"></i>
                            </span>
                            <input type="email" class="form-control" aria-label="Your email" id="pcf_email" name="pcf_email" placeholder="Email..." value="{{ email|e('html_attr') }}">
                        </div>
                        <p class="text-danger{{ errors['pcf_title'] is not defined ? ' form-hide' }}" role="alert">&nbsp;{{ errors['pcf_title']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                        <div class="input-group phpblog-field-group form-group-no-border input-lg">
                            <span class="input-group-addon">
                                <i class="now-ui-icons shopping_tag-content"></i>
                            </span>
                            <input type="text" class="form-control" aria-label="Your title" id="pcf_title" name="pcf_title" placeholder="Title..." value="{{ title|e('html_attr') }}">
                        </div>
                        <p class="text-danger{{ errors['pcf_content'] is not defined ? ' form-hide' }}" role="alert">&nbsp;{{ errors['pcf_content']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                        <div class="textarea-container input-group phpblog-field-group form-group-no-border input-lg">
                            <span class="input-group-addon">
                                <i class="now-ui-icons files_single-copy-04"></i>
                            </span>
                            <textarea class="form-control" aria-label="Your comment" id="pcf_content" name="pcf_content" rows="4" cols="80" placeholder="Type a comment...">{{ content|raw }}</textarea>
                        </div>
                        <input type="hidden" id="pcf_check" name="{{ pcfTokenIndex }}" value="{{ pcfTokenValue }}">
                        <input type="hidden" id="pcf_postId" name="pcf_postId" value="{{ post[0].id }}">
                        <p class="text-danger{{ errors['pcf_noSpam'] is not defined ? ' form-hide' }}" role="alert">&nbsp;{{ errors['pcf_noSpam']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                        {# No Spam tools "captcha" #}
                        <div class="nospam-container">
                        {% for i in 0..pcfNoSpam|length - 1 %}
                            {% if pcfNoSpam[i].input is defined %}
                                {% set attributes = [] %}
                                {% set label = '' %}
                                {% for key, value in pcfNoSpam[i].input if value != false %}
                                    {% set attributes = attributes|merge([key~'="'~value|e('html_attr')~'"']) %}
                                {% endfor %}
                                {% if pcfNoSpam[i].label != false %}
                                    <label for="{{ pcfNoSpam[i].name }}" class="phpblog-label text-neutral">{{ pcfNoSpam[i].label }}</label>&nbsp;
                                {% endif %}
                                <input {{ attributes|join(' ')|raw }}>
                            {% endif %}
                        {% endfor %}
                        </div>
                        {#  End No Spam tools "captcha" #}
                        <div class="send-button">
                            <button type="submit" class="btn btn-warning btn-lg" name="pcf_submit" value="{{ submit }}">CONFIRM YOUR COMMENT</button>
                        </div>
                        <p class="form-text">All fields are mandatory.</p>
                    </form>
	            </div>
        	</div>
        </div>
    </div>
</div>