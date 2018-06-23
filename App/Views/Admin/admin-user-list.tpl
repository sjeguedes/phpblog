{% if userList is defined -%}
<div class="section section-admin-user-list">
    <div class="container">
        <div class="row">
            <div id="bloc-user-list" class="col-md-12 ml-auto mr-auto">
                <h2 class="title">Admin user list</h2>
                <p class="description">All the registered users are listed here!</p>
                <!-- User notice message -->
                <div class="row">
                    <div class="col-lg-8 text-center col-md-10 ml-auto mr-auto">
                        <p class="alert alert-success form-success{{ success['user']['state'] == 0 ? ' form-hide' }}" role="alert">
                            <i class="now-ui-icons ui-2_like"></i>&nbsp;&nbsp;<strong>WELL DONE!</strong>&nbsp;{% if success['user'] is defined %}{{ success['user']['message']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>{% endif %}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">
                                    <i class="now-ui-icons ui-1_simple-remove"></i>
                                </span>
                            </button>
                        </p>
                        <p class="alert alert-danger form-error{{ errors['user']['state'] == 0 ? ' form-hide' }}" role="alert">
                            <i class="now-ui-icons ui-1_bell-53"></i>&nbsp;&nbsp;<strong>ERRORS!</strong>&nbsp;Change a few things up and try submitting again.{% if errors['haf_check'] is defined %}<br><br>{{ errors['haf_check']|raw }}{% endif %}
                            {% if errors['haf_failed']['user']['message'] is defined %}<br><br>{{ errors['haf_failed']['user']['message']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>{% endif %}
                            {% if errors['haf_failed']['user']['message2'] is defined %}<br><br>{{ errors['haf_failed']['user']['message2']|raw }}{% endif %}
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
                <div class="user-box card">
                    {# Store last slide where user action was performed! -#}
                    {% if (success['user'] is defined) and (success['user']['slideRank'] != 1) -%}
                        {# Success state -#}
                        {% set slideRank = success['user']['slideRank'] -%}
                    {% elseif (slideRankAfterSubmit is defined) and (slideRankAfterSubmit != 1) -%}
                        {# Error state: there is no redirection after submission -#}
                        {% set slideRank = slideRankAfterSubmit -%}
                    {% else %}
                        {% set slideRank = 1 -%}
                    {% endif -%}
                    <div class="user-list-paging slider-paging" data-slide-rank="{{ slideRank|e('html_attr') }}">
                    {% set rank = 0 -%}
                    {% for i in 0..userList|length - 1 -%}
                        {# Begin slider item "div" if (i == 0) or (i % userPerSlide == 0) -#}
                        {% if (i == 0) or (i % userPerSlide == 0) -%}
                        {% set rank = rank + 1 -%}
                        <!-- Begin Slick slider user list slide item if (i == 0) or (i % userPerSlide == 0) -->
                        <div class="slide-item" data-slide-item="{{ rank }}">
                    {# // -#}
                        <!-- // -->
                            {% endif -%}
                            <div class="p-2 mb-0">
                                <div class="flex-table">
                                    <div class="flex-col">
                                        <div class="flex-header bg-info">
                                            <span class="flex-label">User</span>
                                        </div>
                                        <p class="flex-content list-element">
                                            <span class="btn btn-default btn-icon btn-round" target="_blank" title="User #{{ userList[i].id }}"><small>NUMBER</small><br>#{{ userList[i].id }}</span>
                                        </p>
                                    </div>
                                    <div class="flex-col">
                                        <div class="flex-header bg-info">
                                            <span class="flex-label">Registered on</span>
                                        </div>
                                       <p class="flex-content"><i class="fa fa-hourglass-end" aria-hidden="true"></i>&nbsp;{{ userList[i].creationDate }}</p>
                                    </div>
                                    <div class="flex-col">
                                        <div class="flex-header bg-info">
                                            <span class="flex-label">Identity</span>
                                        </div>
                                        <p class="flex-content">
                                            <i class="fa fa-user-circle" aria-hidden="true"></i>&nbsp;<strong>{{ userList[i].firstName }} {{ userList[i].familyName }}</strong>
                                        </p>
                                    </div>
                                    <div class="flex-col">
                                        <div class="flex-header bg-info">
                                            <span class="flex-label">User details</span>
                                        </div>
                                        <p class="flex-content">
                                            <button class="btn btn-info" data-toggle="modal" data-target="#modal-user-content-{{ i + 1 }}" title="Show message">SHOW INFOS</button>
                                        </p>
                                    </div>
                                    <div class="flex-col">
                                        <div class="flex-header bg-info">
                                            <span class="flex-label">ACTION</span>
                                        </div>
                                        <p class="flex-content">
                                            {% if userList[i].temporaryParams['noDeletingAction'] is defined %}
                                                <button class="btn btn-deactivate btn-sm" title="{{ userList[i].temporaryParams['noDeletingAction']['message']|e('html_attr') }}" disabled><i class="now-ui-icons ui-1_simple-remove"></i></button>
                                            {% else %}
                                                <button data-toggle="modal" data-target="#ud-modal-{{ userList[i].id }}" class="btn btn-danger btn-sm" title="Delete user"><i class="now-ui-icons ui-1_simple-remove"></i></button>
                                            {% endif %}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            {# Add "hr" tag if ((i + 1) % userPerSlide != 0) and (i < userList|length - 1) -#}
                            {% if ((i + 1) % userPerSlide != 0) and (i < userList|length - 1) -%}
                            <hr>
                            {% endif %}
                        {#  End slider item "div" if (i + 1) % userPerSlide == 0 or last i -#}
                        {% if ((i + 1) % userPerSlide == 0) or (i == userList|length - 1) -%}
                        <!-- End Slick slider user list slide item -->
                        {# Interval of "userPerSlide" items -#}
                        </div>
                        {# // -#}
                        <!-- // -->
                        {% endif -%}
                    {% endfor -%}
                    <!-- End Slick slider user list paging -->
                    </div>
                    <!-- // -->
                </div>
            </div>
        </div>
    </div>
</div>
{% endif -%}
{# Second same loop to create user modals functionalities, to avoid overflow hidden issue with slider -#}
{# Use rank as reminder to know which slide comment item belongs to! -#}
{% set rank = 0 -%}
{% for i in 0..userList|length - 1 -%}
    {% if (i == 0) or (i % userPerSlide == 0) -%}
        {% set rank = rank + 1 -%}
    {% endif -%}
<!-- Contact content modal -->
<div class="modal fade" id="modal-user-content-{{ i + 1 }}" tabindex="-1" role="dialog" aria-labelledby="Contact content">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header justify-content-center">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    <i class="now-ui-icons ui-1_simple-remove"></i>
                </button>
                <h4 class="title title-up">User #{{ userList[i].id }}<br>main information</h4>
            </div>
            <div class="modal-body">
                <p><strong>User first name:</strong> {{ userList[i].firstName }}<br>
                <strong>User family name:</strong> {{ userList[i].familyName }}<br>
                <strong>User nickname:</strong> {{ userList[i].nickName }}<br>
                 <strong>User email:</strong> {{ userList[i].email }}<br>
                <strong>User type:</strong> registered as <strong>{{ userList[i].temporaryParams.userTypeLabel|lower }}</strong>
                </p>
                {% if userList[i].isActivated == 1 %}
                <p>Account activated on <strong>{{ userList[i].activationDate }}</strong></p>
                {% else %}
                <strong class="text-primary"><i class="now-ui-icons ui-1_bell-53"></i>&nbsp;WARNING</strong>&nbsp;No account activation
                {% endif %}
            </div>
            <div class="modal-footer">
                &nbsp;
                <button type="button" class="btn btn-danger" data-dismiss="modal" title="Close viewer">CLOSE</button>
            </div>
        </div>
    </div>
</div>
<!-- End user content modal -->
<!-- Validation modals -->
<!-- Delete user -->
<div class="modal fade modal-mini modal-danger" id="ud-modal-{{ userList[i].id }}" tabindex="-1" role="dialog" aria-labelledby="Deleting action about user #{{ userList[i].id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header justify-content-center">
                <div class="modal-profile">
                    <i class="now-ui-icons ui-1_simple-remove"></i>
                </div>
            </div>
            <div class="modal-body">
                <p>Please confirm<br><strong>deleting</strong> action<br>about <strong>user #{{ userList[i].id }}</strong>.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-neutral text-muted" data-dismiss="modal" title="Cancel user deleting">CANCEL</button>
                <form method="post" action="/admin/delete-user/{{ userList[i].id }}">
                    <input type="hidden" id="ud_slide_rank" name="ud_slide_rank" value="{{ rank }}">
                    <input type="hidden" id="ud_check" name="{{ udTokenIndex }}" value="{{ udTokenValue }}">
                    <input type="hidden" id="ud_id" name="ud_id" value="{{ userList[i].id }}">
                    {# No need to set udSubmit because after success state user doesn't exist anymore! In fact udSubmit will always set to "0". -#}
                    {% set udSubmit = 0 -%}
                    <button type="submit" class="btn btn-neutral text-danger" name="ud_submit" title="Confirm user deleting" value="{{ udSubmit }}"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;CONFIRM</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- End validation modals -->
{% endfor -%}