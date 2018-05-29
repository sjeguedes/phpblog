{% if commentList is defined %}
<div class="section section-admin-comment-list">
    <div class="container">
        <div class="row">
            <div id="bloc-comment-list" class="col-md-12 ml-auto mr-auto text-center">
                <h2 class="title">Admin comment list</h2>
                <p class="description">All the comments created by users in front-end have to be checked!<br>They need to be moderated before publication.</p>
                <!-- User notice message -->
                <div class="row">
                    <div class="col-lg-8 text-center col-md-10 ml-auto mr-auto">
                        <p class="alert alert-success form-success{{ success['comment']['state'] == 0 ? ' form-hide' }}" role="alert">
                            <i class="now-ui-icons ui-2_like"></i>&nbsp;&nbsp;<strong>WELL DONE!</strong>&nbsp;{% if success['comment'] is defined %}{{ success['comment']['message']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>{% endif %}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">
                                    <i class="now-ui-icons ui-1_simple-remove"></i>
                                </span>
                            </button>
                        </p>
                        <p class="alert alert-danger form-error{{ errors['comment']['state'] == 0 ? ' form-hide' }}" role="alert">
                            <i class="now-ui-icons ui-1_bell-53"></i>&nbsp;&nbsp;<strong>ERRORS!</strong>&nbsp;Change a few things up and try submitting again.{% if errors['paf_check'] is defined %}<br><br>{{ errors['paf_check']|raw }}{% endif %}
                            {% if errors['paf_failed']['comment']['message'] is defined %}<br><br>{{ errors['paf_failed']['comment']['message']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>{% endif %}
                            {% if errors['paf_failed']['comment']['message2'] is defined %}<br><br>{{ errors['paf_failed']['comment']['message2']|raw }}{% endif %}
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
                <div class="comment-box card">
                    <!-- Begin Slick slider comment list paging -->
                    {# Store last slide where comment action was performed! -#}
                    {% if (success['comment'] is defined) and (success['comment']['slideRank'] != 1) -%}
                        {# Success state -#}
                        {% set slideRank = success['comment']['slideRank'] -%}
                    {% elseif (slideRankAfterSubmit is defined) and (slideRankAfterSubmit != 1) -%}
                        {# Error state: there is no redirection after submission -#}
                        {% set slideRank = slideRankAfterSubmit -%}
                    {% else %}
                        {% set slideRank = 1 -%}
                    {% endif -%}
                    <div class="comment-list-paging slider-paging" data-slide-rank="{{ slideRank|e('html_attr') }}">
                    <!-- // -->
                    {% set rank = 0 -%}
                    {% for i in 0..commentList|length - 1 -%}
                        {# Begin slider item "div" if (i == 0) or (i % commentPerSlide == 0) -#}
                        {% if (i == 0) or (i % commentPerSlide == 0) -%}
                        {% set rank = rank + 1 -%}
                        <!-- Begin Slick slider comment list slide item if (i == 0) or (i % commentPerSlide == 0) -->
                        <div class="slide-item" data-slide-item="{{ rank }}">
                    {# // -#}
                        <!-- // -->
                            {% endif -%}
                            <div class="p-2 mb-0">
                                <div class="flex-table">
                                    <div class="flex-col">
                                        <div class="flex-header bg-primary">
                                            <span class="flex-label">Comment</span>
                                        </div>
                                        <p class="flex-content list-element">
                                            {% if commentList[i].isPublished == 1 -%}<a href="/post/{{ commentList[i].postId }}/#comment-{{ commentList[i].id }}" class="btn btn-default btn-icon btn-round" target="_blank" title="View post comment #{{ commentList[i].id }} on front page"><small>NUMBER</small><br>#{{ commentList[i].id }}</a>
                                            {% else -%}
                                            <span class="btn btn-default btn-icon btn-round" target="_blank" title="Comment #{{ commentList[i].id }}"><small>NUMBER</small><br>#{{ commentList[i].id }}</span>
                                            {% endif -%}
                                        </p>
                                    </div>
                                    <div class="flex-col">
                                        <div class="flex-header bg-primary">
                                            <span class="flex-label">Added on</span>
                                        </div>
                                        <p class="flex-content"><br><i class="fa fa-hourglass-end" aria-hidden="true"></i>&nbsp;{{ commentList[i].creationDate }}</p>
                                    </div>
                                    <div class="flex-col">
                                        <div class="flex-header bg-primary">
                                            <span class="flex-label">Comment details</span>
                                        </div>
                                        <p class="flex-content">
                                            <button class="btn btn-primary" data-toggle="modal" data-target="#modal-comment-content-{{ i + 1 }}" title="Show content">SHOW CONTENT</button>
                                        </p>
                                    </div>
                                    <div class="flex-col">
                                        <div class="flex-header bg-primary">
                                            <span class="flex-label">ACTIONS</span>
                                        </div>
                                        <p class="flex-content">
                                        {% if (connectedUser is not null) and (connectedUser.userTypeId == 1) %}
                                            <button data-toggle="modal" data-target="#pcd-modal-{{ commentList[i].id }}" class="btn btn-danger btn-sm" title="Delete comment"><i class="now-ui-icons ui-1_simple-remove"></i></button>
                                            {% if commentList[i].isValidated == 0 %}<button data-toggle="modal" data-target="#pcv-modal-{{ commentList[i].id }}" class="btn btn-warning btn-sm" title="Validate comment"><i class="now-ui-icons ui-1_check"></i></button>{% endif -%}
                                            {% if (commentList[i].isValidated == 1) and (commentList[i].isPublished == 0) %}<button data-toggle="modal" data-target="#pcp-modal-{{ commentList[i].id }}" class="btn btn-success btn-sm" title="Publish comment"><i class="now-ui-icons ui-1_calendar-60"></i></button>{% endif -%}
                                            {% if commentList[i].isPublished == 1 %}<button data-toggle="modal" data-target="#pcu-modal-{{ commentList[i].id }}" class="btn btn-danger btn-sm" title="Cancel comment publication"><i class="now-ui-icons ui-1_calendar-60"></i>&nbsp;<i class="now-ui-icons ui-1_simple-remove"></i></button>{% endif %}
                                        {% else %}
                                            {% if connectedUser.temporaryParams['noManagementAction'] is defined %}
                                                {% set noManagementAction = connectedUser.temporaryParams['noManagementAction']['message'] %}
                                            {% else %}
                                                {% set noManagementAction = '' %}
                                            {% endif %}
                                            <button class="btn btn-danger btn-deactivate btn-sm" title="Comment deleting is not allowed! {{ noManagementAction|e('html_attr') }}"><i class="now-ui-icons ui-1_simple-remove" disabled></i></button>
                                            {% if commentList[i].isValidated == 0 %}<button class="btn btn-warning btn-deactivate btn-sm" title="Comment validating is not allowed! {{ noManagementAction|e('html_attr') }}" disabled><i class="now-ui-icons ui-1_check"></i></button>{% endif -%}
                                            {% if (commentList[i].isValidated == 1) and (commentList[i].isPublished == 0) %}<button class="btn btn-success btn-deactivate btn-sm" title="Comment publication is not allowed! {{ noManagementAction|e('html_attr') }}" disabled><i class="now-ui-icons ui-1_calendar-60"></i></button>{% endif -%}
                                            {% if commentList[i].isPublished == 1 %}<button class="btn btn-danger btn-deactivate btn-sm" title="Comment publication Cancelation is not allowed! {{ noManagementAction|e('html_attr') }}" disabled><i class="now-ui-icons ui-1_calendar-60"></i>&nbsp;<i class="now-ui-icons ui-1_simple-remove"></i></button>{% endif %}
                                        {% endif %}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            {# Add "hr" tag if ((i + 1) % commentPerSlide != 0) and (i < commentList|length - 1) -#}
                            {% if ((i + 1) % commentPerSlide != 0) and (i < commentList|length - 1) -%}
                            <hr>
                            {% endif %}
                        {#  End slider item "div" if (i + 1) % commentPerSlide == 0 or last i -#}
                        {% if ((i + 1) % commentPerSlide == 0) or (i == commentList|length - 1) -%}
                        <!-- End Slick slider comment list slide item -->
                        {# Interval of "commentPerSlide" items -#}
                        </div>
                        {# // -#}
                        <!-- // -->
                        {% endif -%}
                    {% endfor -%}
                    <!-- End Slick slider comment list paging -->
                    </div>
                    <!-- // -->
                <!-- End of element .comment-box.card -->
                </div>
                <!-- // -->
            </div>
        </div>
    </div>
</div>
{# Second same loop to create comment modals functionalities, to avoid overflow hidden issue with slider -#}
{# Use rank as reminder to know which slide comment item belongs to! -#}
 {% set rank = 0 -%}
{% for i in 0..commentList|length - 1 -%}
    {% if (i == 0) or (i % commentPerSlide == 0) -%}
        {% set rank = rank + 1 -%}
    {% endif -%}
<!-- Comment content modal -->
<div class="modal fade" id="modal-comment-content-{{ i + 1 }}" tabindex="-1" role="dialog" aria-labelledby="Comment content">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header justify-content-center">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    <i class="now-ui-icons ui-1_simple-remove"></i>
                </button>
                <h4 class="title title-up">Comment #{{ commentList[i].id }}<br>{{ commentList[i].title|raw|nl2br }}</h4>
            </div>
            <div class="modal-body">
                <p><strong>Author nickname:</strong> {{ commentList[i].nickName }}<br>
                <strong>Author email:</strong> {{ commentList[i].email }}</p>
                <p class="text-left">{{ commentList[i].content|raw|nl2br }}</p>
            </div>
            <div class="modal-footer">
                <a href="/post/{{ commentList[i].postId }}" class="btn btn-default btn-xs" title="Look at single post #{{ commentList[i].postId }}" target="_blank">VIEW COMMENTED POST #{{ commentList[i].postId }}</a>
                <button type="button" class="btn btn-danger" data-dismiss="modal" title="Close viewer">CLOSE</button>
            </div>
        </div>
    </div>
</div>
<!-- End comment content modal -->
<!-- Validation modals -->
<!-- Delete comment -->
<div class="modal fade modal-mini modal-danger" id="pcd-modal-{{ commentList[i].id }}" tabindex="-1" role="dialog" aria-labelledby="Deleting action about comment #{{ commentList[i].id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header justify-content-center">
                <div class="modal-profile">
                    <i class="now-ui-icons ui-1_simple-remove"></i>
                </div>
            </div>
            <div class="modal-body">
                <p>Please confirm<br><strong>deleting</strong> action<br>about <strong>comment #{{ commentList[i].id }}</strong>.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-neutral text-muted" data-dismiss="modal" title="Cancel comment deleting">CANCEL</button>
                <form method="post" action="/admin/delete-comment/{{ commentList[i].id }}">
                    <input type="hidden" id="pcd_slide_rank" name="pcd_slide_rank" value="{{ rank }}">
                    <input type="hidden" id="pcd_check" name="{{ pcdTokenIndex }}" value="{{ pcdTokenValue }}">
                    <input type="hidden" id="pcd_id" name="pcd_id" value="{{ commentList[i].id }}">
                    {# No need to set pcdSubmit because after success state comment doesn't exist anymore! In fact pcdSubmit will always be set to "0". -#}
                    {% set pcdSubmit = 0 -%}
                    <button type="submit" class="btn btn-neutral text-danger" name="pcd_submit" title="Confirm comment deleting" value="{{ pcdSubmit }}"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;CONFIRM</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Validate comment -->
<div class="modal fade modal-mini modal-warning" id="pcv-modal-{{ commentList[i].id }}" tabindex="-1" role="dialog" aria-labelledby="Validation action about comment #{{ commentList[i].id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header justify-content-center">
                <div class="modal-profile">
                    <i class="now-ui-icons ui-1_check"></i>
                </div>
            </div>
            <div class="modal-body">
                <p>Please confirm<br><strong>validation</strong> action<br>about <strong>comment #{{ commentList[i].id }}</strong>.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-neutral text-muted" data-dismiss="modal" title="Cancel comment validation">CANCEL</button>
                <form method="post" action="/admin/validate-comment/{{ commentList[i].id }}">
                    <input type="hidden" id="pcv_slide_rank" name="pcv_slide_rank" value="{{ rank }}">
                    <input type="hidden" id="pcv_check" name="{{ pcvTokenIndex }}" value="{{ pcvTokenValue }}">
                    <input type="hidden" id="pcv_id" name="pcv_id" value="{{ commentList[i].id }}">
                    {% if success['comment']['id'] ==  commentList[i].id -%}
                        {% set pcvSubmit = 1 -%}
                    {% else -%}
                        {% set pcvSubmit = 0 -%}
                    {% endif -%}
                    <button type="submit" class="btn btn-neutral text-warning" name="pcv_submit" title="Confirm comment validation" value="{{ pcvSubmit }}"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;CONFIRM</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Publish comment -->
<div class="modal fade modal-mini modal-success" id="pcp-modal-{{ commentList[i].id }}" tabindex="-1" role="dialog" aria-labelledby="publication action about comment #{{ commentList[i].id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header justify-content-center">
                <div class="modal-profile">
                    <i class="now-ui-icons ui-1_calendar-60"></i>
                </div>
            </div>
            <div class="modal-body">
                <p>Please confirm<br>front-end <strong>publication</strong> action<br>about <strong>comment #{{ commentList[i].id }}</strong>.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-neutral text-muted" data-dismiss="modal" title="Cancel comment publication">CANCEL</button>
                <form method="post" action="/admin/publish-comment/{{ commentList[i].id }}">
                    <input type="hidden" id="pcp_slide_rank" name="pcp_slide_rank" value="{{ rank }}">
                    <input type="hidden" id="pcp_check" name="{{ pcpTokenIndex }}" value="{{ pcpTokenValue }}">
                    <input type="hidden" id="pcp_id" name="pcp_id" value="{{ commentList[i].id }}">
                    {% if success['comment']['id'] ==  commentList[i].id -%}
                        {% set pcpSubmit = 1 -%}
                    {% else -%}
                        {% set pcpSubmit = 0 -%}
                    {% endif -%}
                    <button type="submit" class="btn btn-neutral text-success" name="pcp_submit" title="Confirm comment publication" value="{{ pcpSubmit }}"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;CONFIRM</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Cancel comment publication -->
<div class="modal fade modal-mini modal-danger" id="pcu-modal-{{ commentList[i].id }}" tabindex="-1" role="dialog" aria-labelledby="Publication cancelation action about comment #{{ commentList[i].id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header justify-content-center">
                <div class="modal-profile">
                    <i class="now-ui-icons ui-1_calendar-60"></i>
                    <i class="now-ui-icons ui-1_simple-remove"></i>
                </div>
            </div>
            <div class="modal-body">
                <p>Please confirm<br>front-end <strong>publication cancelation</strong> action<br>about <strong>comment #{{ commentList[i].id }}</strong>.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-neutral text-muted" data-dismiss="modal" title="Cancel comment publication cancelation">CANCEL</button>
                <form method="post" action="/admin/unpublish-comment/{{ commentList[i].id }}">
                    <input type="hidden" id="pcu_slide_rank" name="pcu_slide_rank" value="{{ rank }}">
                    <input type="hidden" id="pcu_check" name="{{ pcuTokenIndex }}" value="{{ pcuTokenValue }}">
                    <input type="hidden" id="pcu_id" name="pcu_id" value="{{ commentList[i].id }}">
                    {% if success['comment']['id'] ==  commentList[i].id -%}
                        {% set pcuSubmit = 1 -%}
                    {% else -%}
                        {% set pcuSubmit = 0 -%}
                    {% endif -%}
                    <button type="submit" class="btn btn-neutral text-danger" name="pcu_submit" title="Confirm comment publication cancelation" value="{{ pcuSubmit }}"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;CONFIRM</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- End validation modals -->
{% endfor -%}
{# End of if commentList is defined -#}
{% endif -%}