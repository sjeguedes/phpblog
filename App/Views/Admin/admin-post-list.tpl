{% if postList is defined %}
<div class="section section-admin-post-list">
    <div class="container">
        <div class="row">
            <div id="bloc-post-list" class="col-md-12 ml-auto mr-auto text-center">
                <h2 class="title">Admin post list</h2>
                <p class="description">All the posts created by members in back-end are present here!<br>They can be easily updated, and need to be validated before publication..</p>
                <!-- User notice message -->
                <div class="row">
                    <div class="col-lg-8 text-center col-md-10 ml-auto mr-auto">
                        <p class="alert alert-success form-success{{ success['post']['state'] == 0 ? ' form-hide'}}" role="alert">
                            <i class="now-ui-icons ui-2_like"></i>&nbsp;&nbsp;<strong>WELL DONE!</strong>&nbsp;{% if success['post'] is defined %}{{ success['post']['message']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>{% endif %}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">
                                    <i class="now-ui-icons ui-1_simple-remove"></i>
                                </span>
                            </button>
                        </p>
                        <p class="alert alert-danger form-error{{ errors == 0 ? ' form-hide'}}" role="alert">
                            <i class="now-ui-icons ui-1_bell-53"></i>&nbsp;&nbsp;<strong>ERRORS!</strong>&nbsp;Change a few things up and try submitting again.{% if errors['paf_check'] is defined %}<br><br>{{ errors['paf_check']|raw }}{% endif %}
                            {% if errors['paf_failed']['post']['message'] is defined %}<br><br>{{ errors['paf_failed']['post']['message']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>{% endif %}
                            {% if errors['paf_failed']['post']['message2'] is defined %}<br><br>{{ errors['paf_failed']['post']['message2']|raw }}{% endif %}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">
                                    <i class="now-ui-icons ui-1_simple-remove"></i>
                                </span>
                            </button>
                        </p>
                    </div>
                </div>
                <!-- End user notice message -->
                <!-- Do not change tags order for ".slider-navigation"
                and ".slider-paging" to avoid Javascript issue -->
                <div class="slider-navigation mt-2 mb-2">&nbsp;</div>
                <div class="post-box card">
                    <!-- Begin Slick slider post list paging -->
                    {# Store last slide where post action was performed! -#}
                    {% if (success['post'] is defined) and (success['post']['slideRank'] != 1) -%}
                        {# Success state -#}
                        {% set slideRank = success['post']['slideRank'] -%}
                    {% elseif (slideRankAfterSubmit is defined) and (slideRankAfterSubmit != 1) -%}
                        {# Error state: there is no redirection after submission -#}
                        {% set slideRank = slideRankAfterSubmit -%}
                    {% else %}
                        {% set slideRank = 1 -%}
                    {% endif -%}
                    <div class="post-list-paging slider-paging" data-slide-rank="{{ slideRank|e('html_attr') }}">
                    <!-- // -->
                    {% set rank = 0 -%}
                    {% for i in 0..postList|length - 1 -%}
                        {# Begin slider item "div" if (i == 0) or (i % postPerSlide == 0) -#}
                        {% if (i == 0) or (i % postPerSlide == 0) -%}
                        {% set rank = rank + 1 -%}
                        <!-- Begin Slick slider post list slide item if (i == 0) or (i % postPerSlide == 0) -->
                        <div class="slide-item" data-slide-item="{{ rank }}">
                    {# // -#}
                        <!-- // -->
                            {% endif -%}
                            <div class="p-2 mb-0">
                                <div class="flex-table">
                                    <div class="flex-col">
                                        <div class="flex-header bg-primary">
                                            <span class="flex-label">Post</span>
                                        </div>
                                        <p class="flex-content list-element">
                                            {% if postList[i].isPublished == 1 -%}<a href="/post/{{ postList[i].id }}/#post-{{ postList[i].id }}" class="btn btn-default btn-icon btn-round" target="_blank" title="View post #{{ postList[i].id }} on front page"><small>NUMBER</small><br>#{{ postList[i].id }}</a>
                                            {% else -%}
                                            <span class="btn btn-default btn-icon btn-round" target="_blank" title="Post #{{ postList[i].id }}"><small>NUMBER</small><br>#{{ postList[i].id }}</span>
                                            {% endif -%}
                                        </p>
                                    </div>
                                    <div class="flex-col">
                                        <div class="flex-header bg-primary">
                                            <span class="flex-label">Added on</span>
                                        </div>
                                        <p class="flex-content"><br><i class="fa fa-hourglass-end" aria-hidden="true"></i>&nbsp;{{ postList[i].creationDate }}</p>
                                    </div>
                                    <div class="flex-col">
                                        <div class="flex-header bg-primary">
                                            <span class="flex-label">Post details</span>
                                        </div>
                                        <p class="flex-content">
                                            <button class="btn btn-primary" data-toggle="modal" data-target="#modal-post-content-{{ i + 1 }}" title="Show content">SHOW CONTENT</button>
                                        </p>
                                    </div>
                                    <div class="flex-col">
                                        <div class="flex-header bg-primary">
                                            <span class="flex-label">ACTIONS</span>
                                        </div>
                                        <p class="flex-content">
                                            <button data-toggle="modal" data-target="#ppd-modal-{{ postList[i].id }}" class="btn btn-danger btn-sm" title="Delete post"><i class="now-ui-icons ui-1_simple-remove"></i></button>
                                            {% if postList[i].isValidated == 0 %}<button data-toggle="modal" data-target="#ppv-modal-{{ postList[i].id }}" class="btn btn-warning btn-sm" title="Validate post"><i class="now-ui-icons ui-1_check"></i></button>{% endif -%}
                                            {% if postList[i].isValidated == 1 %}<a href="/admin/update-post/{{ postList[i].id }}#detail" class="btn btn-default btn-sm" title="Update post" target="_blank"><i class="now-ui-icons arrows-1_refresh-69"></i></a>{% endif -%}
                                            {% if (postList[i].isValidated == 1) and (postList[i].isPublished == 0) %}<button data-toggle="modal" data-target="#ppp-modal-{{ postList[i].id }}" class="btn btn-success btn-sm" title="Publish post"><i class="now-ui-icons ui-1_calendar-60"></i></button>{% endif -%}
                                            {% if postList[i].isPublished == 1 %}<button data-toggle="modal" data-target="#ppu-modal-{{ postList[i].id }}" class="btn btn-danger btn-sm" title="Cancel post publication"><i class="now-ui-icons ui-1_calendar-60"></i>&nbsp;<i class="now-ui-icons ui-1_simple-remove"></i></button>{% endif %}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            {# Add "hr" tag if ((i + 1) % postPerSlide != 0) and (i < postList|length - 1) -#}
                            {% if ((i + 1) % postPerSlide != 0) and (i < postList|length - 1) -%}
                            <hr>
                            {% endif %}
                        {#  End slider item "div" if (i + 1) % postPerSlide == 0 or last i -#}
                        {% if ((i + 1) % postPerSlide == 0) or (i == postList|length - 1) -%}
                        <!-- End Slick slider post list slide item -->
                        {# Interval of "postPerSlide" items -#}
                        </div>
                        {# // -#}
                        <!-- // -->
                        {% endif -%}
                    {% endfor -%}
                    <!-- End Slick slider post list paging -->
                    </div>
                    <!-- // -->
                <!-- End of element .post-box.card -->
                </div>
                <!-- // -->
            </div>
        </div>
    </div>
</div>
{# Second same loop to create post modals functionalities, to avoid overflow hidden issue with slider -#}
{# Use rank as reminder to know which slide post item belongs to! -#}
 {% set rank = 0 -%}
{% for i in 0..postList|length - 1 -%}
    {% if (i == 0) or (i % postPerSlide == 0) -%}
        {% set rank = rank + 1 -%}
    {% endif -%}
<!-- Comment content modal -->
<div class="modal fade" id="modal-post-content-{{ i + 1 }}" tabindex="-1" role="dialog" aria-labelledby="Comment content">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header justify-content-center">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    <i class="now-ui-icons ui-1_simple-remove"></i>
                </button>
                <h4 class="title title-up">Post #{{ postList[i].id }}<br>{{ postList[i].title|raw|nl2br }}</h4>
            </div>
            <div class="modal-body">
                {% if date(postList[i].creationDate) != date(postList[i].updateDate) %}<p class="text-muted"><strong>LAST UPDATE: {{ postList[i].updateDate }}</strong></p>{% endif -%}
                <p><strong>Author nickname:</strong> {{ postList[i].temporaryParams.author.nickName }}<br>
                <strong>Author email:</strong> {{ postList[i].temporaryParams.author.email }}</p>
                <p class="text-center"><strong>ARTICLE INTRO</strong></p>
                <p class="text-left">{{ postList[i].intro|raw|nl2br }}</p>
            </div>
            <div class="modal-footer">
                <a href="/post/{{ postList[i].id }}" class="btn btn-default btn-xs" title="Look at single post #{{ postList[i].id }}" target="_blank">VIEW COMPLETE POST #{{ postList[i].id }}</a>
                <button type="button" class="btn btn-danger" data-dismiss="modal" title="Close viewer">CLOSE</button>
            </div>
        </div>
    </div>
</div>
<!-- End post content modal -->
<!-- Validation modals -->
<!-- Delete post -->
<div class="modal fade modal-mini modal-danger" id="ppd-modal-{{ postList[i].id }}" tabindex="-1" role="dialog" aria-labelledby="Deleting action about post #{{ postList[i].id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header justify-content-center">
                <div class="modal-profile">
                    <i class="now-ui-icons ui-1_simple-remove"></i>
                </div>
            </div>
            <div class="modal-body">
                <p>Please confirm<br><strong>deleting</strong> action<br>about <strong>post #{{ postList[i].id }}</strong>.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-neutral text-muted" data-dismiss="modal" title="Cancel post deleting">CANCEL</button>
                <form method="post" action="/admin/delete-post/{{ postList[i].id }}">
                    <input type="hidden" id="ppd_slide_rank" name="ppd_slide_rank" value="{{ rank }}">
                    <input type="hidden" id="ppd_check" name="{{ ppdTokenIndex }}" value="{{ ppdTokenValue }}">
                    <input type="hidden" id="ppd_id" name="ppd_id" value="{{ postList[i].id }}">
                    {# No need to set ppdSubmit because after success state post doesn't exist anymore! In fact ppdSubmit will always be set to "0". -#}
                    {% set ppdSubmit = 0 -%}
                    <button type="submit" class="btn btn-neutral text-danger" name="ppd_submit" title="Confirm post deleting" value="{{ ppdSubmit }}"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;CONFIRM</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Validate post -->
<div class="modal fade modal-mini modal-warning" id="ppv-modal-{{ postList[i].id }}" tabindex="-1" role="dialog" aria-labelledby="Validation action about post #{{ postList[i].id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header justify-content-center">
                <div class="modal-profile">
                    <i class="now-ui-icons ui-1_check"></i>
                </div>
            </div>
            <div class="modal-body">
                <p>Please confirm<br><strong>validation</strong> action<br>about <strong>post #{{ postList[i].id }}</strong>.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-neutral text-muted" data-dismiss="modal" title="Cancel post validation">CANCEL</button>
                <form method="post" action="/admin/validate-post/{{ postList[i].id }}">
                    <input type="hidden" id="ppv_slide_rank" name="ppv_slide_rank" value="{{ rank }}">
                    <input type="hidden" id="ppv_check" name="{{ ppvTokenIndex }}" value="{{ ppvTokenValue }}">
                    <input type="hidden" id="ppv_id" name="ppv_id" value="{{ postList[i].id }}">
                    {% if success['post']['id'] ==  postList[i].id -%}
                        {% set ppvSubmit = 1 -%}
                    {% else -%}
                        {% set ppvSubmit = 0 -%}
                    {% endif -%}
                    <button type="submit" class="btn btn-neutral text-warning" name="ppv_submit" title="Confirm post validation" value="{{ ppvSubmit }}"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;CONFIRM</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Publish post -->
<div class="modal fade modal-mini modal-success" id="ppp-modal-{{ postList[i].id }}" tabindex="-1" role="dialog" aria-labelledby="publication action about post #{{ postList[i].id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header justify-content-center">
                <div class="modal-profile">
                    <i class="now-ui-icons ui-1_calendar-60"></i>
                </div>
            </div>
            <div class="modal-body">
                <p>Please confirm<br>front-end <strong>publication</strong> action<br>about <strong>post #{{ postList[i].id }}</strong>.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-neutral text-muted" data-dismiss="modal" title="Cancel post publication">CANCEL</button>
                <form method="post" action="/admin/publish-post/{{ postList[i].id }}">
                    <input type="hidden" id="ppp_slide_rank" name="ppp_slide_rank" value="{{ rank }}">
                    <input type="hidden" id="ppp_check" name="{{ pppTokenIndex }}" value="{{ pppTokenValue }}">
                    <input type="hidden" id="ppp_id" name="ppp_id" value="{{ postList[i].id }}">
                    {% if success['post']['id'] ==  postList[i].id -%}
                        {% set pppSubmit = 1 -%}
                    {% else -%}
                        {% set pppSubmit = 0 -%}
                    {% endif -%}
                    <button type="submit" class="btn btn-neutral text-success" name="ppp_submit" title="Confirm post publication" value="{{ pppSubmit }}"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;CONFIRM</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Cancel post publication -->
<div class="modal fade modal-mini modal-danger" id="ppu-modal-{{ postList[i].id }}" tabindex="-1" role="dialog" aria-labelledby="Publication cancelation action about post #{{ postList[i].id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header justify-content-center">
                <div class="modal-profile">
                    <i class="now-ui-icons ui-1_calendar-60"></i>
                    <i class="now-ui-icons ui-1_simple-remove"></i>
                </div>
            </div>
            <div class="modal-body">
                <p>Please confirm<br>front-end <strong>publication cancelation</strong> action<br>about <strong>post #{{ postList[i].id }}</strong>.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-neutral text-muted" data-dismiss="modal" title="Cancel post publication cancelation">CANCEL</button>
                <form method="post" action="/admin/unpublish-post/{{ postList[i].id }}">
                    <input type="hidden" id="ppu_slide_rank" name="ppu_slide_rank" value="{{ rank }}">
                    <input type="hidden" id="ppu_check" name="{{ ppuTokenIndex }}" value="{{ ppuTokenValue }}">
                    <input type="hidden" id="ppu_id" name="ppu_id" value="{{ postList[i].id }}">
                    {% if success['post']['id'] ==  postList[i].id -%}
                        {% set ppuSubmit = 1 -%}
                    {% else -%}
                        {% set ppuSubmit = 0 -%}
                    {% endif -%}
                    <button type="submit" class="btn btn-neutral text-danger" name="ppu_submit" title="Confirm post publication cancelation" value="{{ ppuSubmit }}"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;CONFIRM</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- End validation modals -->
{% endfor -%}
{# End of if postList is defined -#}
{% endif -%}