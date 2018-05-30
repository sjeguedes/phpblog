{% extends 'layout.tpl' %}
{% block content %}
<div id="bloc-detail" class="section section-update-post text-center">
    <a class="normal-link" href="/admin/posts#post-list" title="Go back to admin post list!"><i class="now-ui-icons arrows-1_minimal-left">&nbsp;</i>Go back to admin post list!</a><br><br>
    <div class="container">
        <h2 class="title">Update post #{{ post.id }}</h2>
        <div class="card alert col-lg-7 text-center col-md-9 ml-auto mr-auto" role="alert" data-background-color="black">
            <div class="container">
                <i class="now-ui-icons travel_info"></i>&nbsp;&nbsp;<strong>Post permalink is generated with slug and post id.</strong><br><i class="fa fa-long-arrow-down" aria-hidden="true"></i>&nbsp;URL to access post will be:<br><strong class="text-lower text-muted">{{ domain }}/post/<span class="post-slug-notice">{{ post.slug }}</span>-{{ post.id }}</strong>
            </div>
        </div>
        <p class="description"><strong>Choose fields to be updated.</strong><br>You can change author easily.<br>it will appear on front-end with anonymous nickname.</p>
        <!-- User notice message -->
        <div class="row">
            <div class="col-lg-8 text-center col-md-10 ml-auto mr-auto">
                <p class="alert alert-success form-success{{ success == 0 ? ' form-hide'}}" role="alert">
                    <i class="now-ui-icons ui-2_like"></i>&nbsp;&nbsp;<strong>WELL DONE!</strong>&nbsp;Post was updated successfully.{% if post.isPublished == 0 %}<br>Don't forget to publish this post to show it on front-end!{% endif %}<br>Actual permalink is:<br><strong class="text-lower text-muted">{{ domain }}/post/{{ post.slug }}-{{ post.id }}</strong>
                    {% if imageSuccess is not null %}<br>{{ imageSuccess|raw }}{% endif %}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                </p>
                <p class="alert alert-danger form-error{{ errors == 0 ? ' form-hide'}}" role="alert">
                    <i class="now-ui-icons ui-1_bell-53"></i>&nbsp;&nbsp;<strong>ERRORS!</strong>&nbsp;Change a few things up and try submitting again.{% if errors['puf_check'] is defined %}<br>{{ errors['puf_check']|raw }}{% endif %}{% if errors['puf_notUpdated'] is defined %}<br>{{ errors['puf_notUpdated']|raw }}{% endif %}
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
                    <form novalidate class="post-update-form form-switch-input" method="post" action="/admin/update-post/{{ post.id }}"  data-try-validation="{{ tryValidation }}" enctype="multipart/form-data">
                        <p class="text-left"><small><strong>AUTHOR</strong></small></p>
                        <p class="text-danger{{ errors['puf_userAuthor'] is not defined ? ' form-hide' }}" role="alert">&nbsp;{{ errors['puf_userAuthor'] }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                        <div class="input-group phpblog-field-group form-group-no-border input-lg">
                            <span class="input-group-addon">
                                <i class="now-ui-icons users_single-02"></i>
                            </span>
                            <select id="puf_userAuthor" name="puf_userAuthor" class="form-control custom-select">
                            {% for i in 0..userList|length - 1 -%}
                            {% if userList[i].id == userAuthor.id -%}
                            <option selected value="{{ userList[i].id|e('html_attr') }}">{{ userList[i].firstName }}&nbsp;{{ userList[i].familyName }}&nbsp;({{ userList[i].nickName }})</option>
                            {% else -%}
                            <option value="{{ userList[i].id|e('html_attr') }}">{{ userList[i].firstName }}&nbsp;{{ userList[i].familyName }}&nbsp;({{ userList[i].nickName }})</option>
                            {% endif -%}
                            {% endfor -%}
                            </select>
                        </div>
                        <p class="text-left"><small><strong>TITLE</strong></small></p>
                        <p class="text-danger{{ errors['puf_title'] is not defined ? ' form-hide' }}" role="alert">&nbsp;{{ errors['puf_title']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                        <div class="textarea-container input-group phpblog-field-group form-group-no-border input-lg phpblog-tinymce">
                            <span class="input-group-addon phpblog-mce">
                                <i class="now-ui-icons shopping_tag-content"></i>
                            </span>
                            <textarea class="form-control" aria-label="Your title" id="puf_title" name="puf_title" rows="4" cols="80" placeholder="Title...">{{ title|raw }}</textarea>
                        </div>
                        <div class="phpblog-switch-block form-hide">
                            <label for="puf_customSlug" class="phpblog-label text-neutral">Customize slug?</label>&nbsp;
                            <input type="checkbox" id="puf_customSlug" name="puf_customSlug" class="bootstrap-switch" data-on-label="YES" data-off-label="NO"{{ customSlug == 1 ? ' value="1" checked' : 'value="0"' }}>
                            <p class="slug-info text-warning{{ customSlug == 0 ? ' form-hide' }}"><i class="fa fa-info-circle"></i>&nbsp;<strong>Notice</strong>: each time you deactivate custom slug,<br>you will lose any existing personalization!<br>Auto generated slug (based on title) will replace it definitively.</p>
                            <p class="text-left slug-element{{ customSlug == 0 ? ' form-hide' }}"><small><strong>SLUG</strong></small></p>
                            <div class="slug-element{{ customSlug == 0 ? ' form-hide' }}">
                                <p class="text-danger{{ errors['puf_slug'] is not defined ? ' form-hide' }}" role="alert">&nbsp;{{ errors['puf_slug']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                                <div class="input-group phpblog-field-group form-group-no-border input-lg">
                                    <span class="input-group-addon">
                                        <i class="now-ui-icons location_bookmark"></i>
                                    </span>
                                    <input type="text" class="form-control" aria-label="Your slug" id="puf_slug" name="puf_slug" data-previous-custom-slug="{{ slug|e('html_attr') }}" placeholder="Customize slug..." value="{{ slug|e('html_attr') }}">
                                </div>
                            </div>
                        </div>
                        <p class="text-left"><small><strong>INTRO</strong></small></p>
                        <p class="text-danger{{ errors['puf_intro'] is not defined ? ' form-hide' }}" role="alert">&nbsp;{{ errors['puf_intro']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                        <div class="textarea-container input-group phpblog-field-group form-group-no-border input-lg phpblog-tinymce">
                            <span class="input-group-addon phpblog-mce">
                                <i class="now-ui-icons files_paper"></i>
                            </span>
                            <textarea class="form-control" aria-label="Your intro" id="puf_intro" name="puf_intro" rows="4" cols="80" placeholder="Type an intro...">{{ intro|raw }}</textarea>
                        </div>
                        <p class="text-left"><small><strong>CONTENT</strong></small></p>
                        <p class="text-danger{{ errors['puf_content'] is not defined ? ' form-hide' }}" role="alert">&nbsp;{{ errors['puf_content']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                        <div class="textarea-container input-group phpblog-field-group form-group-no-border input-lg phpblog-tinymce">
                            <span class="input-group-addon phpblog-mce">
                                <i class="now-ui-icons files_single-copy-04"></i>
                            </span>
                            <textarea class="form-control" aria-label="Your content" id="puf_content" name="puf_content" rows="4" cols="80" placeholder="Type a content...">{{ content|raw }}</textarea>
                        </div>
                        <p class="text-left mb-0"><small><strong>IMAGE</strong></small></p>
                        <p class="selected-image text-center mt-0{{ image is empty ? ' form-hide' }}"><span class="text-warning"><i class="fa fa-info-circle"></i>&nbsp;<strong>Your selected file is:</strong></span><br>"<em>{{ image }}</em>"&nbsp;&nbsp;<button class="btn btn-danger btn-sm" title="Delete image"><i class="now-ui-icons ui-1_simple-remove"></i></button><br>
                        {% if (post.temporaryParams['postImage'] is not empty) and (post.temporaryParams['postImage'] == image) %}
                            {% set preview = '' %}
                            {% set noPreview = ' form-hide' %}
                        {% else %}
                            {% set preview = ' form-hide' %}
                            {% set noPreview = '' %}
                        {% endif %}
                        <small class="image-preview{{ preview }}">Current uploaded and validated image (front-end)</small>
                        <img class="raised rounded image-preview{{ preview }}" src="/uploads/images/ci-{{ post.temporaryParams['postCreatorId']|e('html_attr') }}/{{ post.temporaryParams['postImage']|e('html_attr') }}" alt="{{ post.temporaryParams['postImage']|e('html_attr') }}">
                        <small class="image-no-preview{{ noPreview }}">Image preview is available only after validation.</small>
                        </p>
                        <p class="text-danger{{ errors['puf_image'] is not defined ? ' form-hide' }}" role="alert">&nbsp;{{ errors['puf_image']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></p>
                        <div class="input-group phpblog-field-group form-group-no-border input-lg post-custom-image">
                            <span class="input-group-addon phpblog-mce">
                                <i class="now-ui-icons media-1_album"></i>
                            </span>
                            <label class="custom-file" id="customFile">
                                <input type="hidden" id="puf_imageRemoved" name="puf_imageRemoved" value="{{ imageRemoved|e('html_attr') }}">
                                <input type="file" id="puf_image" name="puf_image" class="custom-file-input form-control" lang="en" value="{{ image|e('html_attr') }}">
                                <span class="custom-file-control form-control-file{{ image is not empty ? ' selected' }}"></span>
                            </label>
                        </div>
                        <input type="hidden" id="puf_check" name="{{ pufTokenIndex }}" value="{{ pufTokenValue }}">
                        <input type="hidden" id="puf_postId" name="puf_postId" value="{{ post.id }}">
                        <div class="send-button">
                            <button type="submit" class="btn btn-warning btn-lg" name="puf_submit" value="{{ submit }}">CONFIRM YOUR POST UPDATE</button>
                        </div>
                        <p class="form-text">Update is obviously not mandatory.<br>However, fields can not be empty!</p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
{% endblock %}