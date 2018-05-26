{% if contactList is defined -%}
<div class="section section-admin-contact-list">
    <div class="container">
        <div class="row">
            <div id="bloc-contact-list" class="col-md-12 ml-auto mr-auto">
                <h2 class="title">Admin contact list</h2>
                <p class="description">All the messages sent with contact form are saved in database!</p>
                <!-- User notice message -->
                <div class="row">
                    <div class="col-lg-8 text-center col-md-10 ml-auto mr-auto">
                        <p class="alert alert-success form-success{{ success['contact']['state'] == 0 ? ' form-hide' }}" role="alert">
                            <i class="now-ui-icons ui-2_like"></i>&nbsp;&nbsp;<strong>WELL DONE!</strong>&nbsp;{% if success['contact'] is defined %}{{ success['contact']['message']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>{% endif %}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">
                                    <i class="now-ui-icons ui-1_simple-remove"></i>
                                </span>
                            </button>
                        </p>
                        <p class="alert alert-danger form-error{{ errors['contact']['state'] == 0 ? ' form-hide' }}" role="alert">
                            <i class="now-ui-icons ui-1_bell-53"></i>&nbsp;&nbsp;<strong>ERRORS!</strong>&nbsp;Change a few things up and try submitting again.{% if errors['haf_check'] is defined %}<br><br>{{ errors['haf_check']|raw }}{% endif %}
                            {% if errors['haf_failed']['contact']['message'] is defined %}<br><br>{{ errors['haf_failed']['contact']['message']|raw }}&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>{% endif %}
                            {% if errors['haf_failed']['contact']['message2'] is defined %}<br><br>{{ errors['haf_failed']['contact']['message2']|raw }}{% endif %}
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
                <div class="contact-box card">
                    {# Store last slide where contact action was performed! -#}
                    {% if (success['contact'] is defined) and (success['contact']['slideRank'] != 1) -%}
                        {# Success state -#}
                        {% set slideRank = success['contact']['slideRank'] -%}
                    {% elseif (slideRankAfterSubmit is defined) and (slideRankAfterSubmit != 1) -%}
                        {# Error state: there is no redirection after submission -#}
                        {% set slideRank = slideRankAfterSubmit -%}
                    {% else %}
                        {% set slideRank = 1 -%}
                    {% endif -%}
                    <div class="contact-list-paging slider-paging" data-slide-rank="{{ slideRank|e('html_attr') }}">
                    {% set rank = 0 -%}
                    {% for i in 0..contactList|length - 1 -%}
                        {# Begin slider item "div" if (i == 0) or (i % contactPerSlide == 0) -#}
                        {% if (i == 0) or (i % contactPerSlide == 0) -%}
                        {% set rank = rank + 1 -%}
                        <!-- Begin Slick slider contact list slide item if (i == 0) or (i % contactPerSlide == 0) -->
                        <div class="slide-item" data-slide-item="{{ rank }}">
                    {# // -#}
                        <!-- // -->
                            {% endif -%}
                            <div class="p-2 mb-0">
                                <div class="flex-table">
                                    <div class="flex-col">
                                        <div class="flex-header bg-info">
                                            <span class="flex-label">Contact</span>
                                        </div>
                                        <p class="flex-content list-element">
                                            <span class="btn btn-default btn-icon btn-round" target="_blank" title="Contact #{{ contactList[i].id }}"><small>NUMBER</small><br>#{{ contactList[i].id }}</span>
                                        </p>
                                    </div>
                                    <div class="flex-col">
                                        <div class="flex-header bg-info">
                                            <span class="flex-label">Sent on</span>
                                        </div>
                                       <p class="flex-content"><i class="fa fa-hourglass-end" aria-hidden="true"></i>&nbsp;{{ contactList[i].sendingDate }}</p>
                                    </div>
                                    <div class="flex-col">
                                        <div class="flex-header bg-info">
                                            <span class="flex-label">Sender</span>
                                        </div>
                                        <p class="flex-content">
                                            <i class="fa fa-user-circle" aria-hidden="true"></i>&nbsp;<strong>{{ contactList[i].firstName }} {{ contactList[i].familyName }}</strong>
                                        </p>
                                    </div>
                                    <div class="flex-col">
                                        <div class="flex-header bg-info">
                                            <span class="flex-label">Contact details</span>
                                        </div>
                                        <p class="flex-content">
                                            <button class="btn btn-info" data-toggle="modal" data-target="#modal-contact-content-{{ i + 1 }}" title="Show message">SHOW MESSAGE</button>
                                        </p>
                                    </div>
                                    <div class="flex-col">
                                        <div class="flex-header bg-info">
                                            <span class="flex-label">ACTION</span>
                                        </div>
                                        <p class="flex-content">
                                            <button data-toggle="modal" data-target="#cd-modal-{{ contactList[i].id }}" class="btn btn-danger btn-sm" title="Delete contact"><i class="now-ui-icons ui-1_simple-remove"></i></button>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            {# Add "hr" tag if ((i + 1) % contactPerSlide != 0) and (i < contactList|length - 1) -#}
                            {% if ((i + 1) % contactPerSlide != 0) and (i < contactList|length - 1) -%}
                            <hr>
                            {% endif %}
                        {#  End slider item "div" if (i + 1) % contactPerSlide == 0 or last i -#}
                        {% if ((i + 1) % contactPerSlide == 0) or (i == contactList|length - 1) -%}
                        <!-- End Slick slider contact list slide item -->
                        {# Interval of "contactPerSlide" items -#}
                        </div>
                        {# // -#}
                        <!-- // -->
                        {% endif -%}
                    {% endfor -%}
                    <!-- End Slick slider contact list paging -->
                    </div>
                    <!-- // -->
                </div>
            </div>
        </div>
    </div>
</div>
{% endif -%}
{# Second same loop to create contact modals functionalities, to avoid overflow hidden issue with slider -#}
{# Use rank as reminder to know which slide comment item belongs to! -#}
{% set rank = 0 -%}
{% for i in 0..contactList|length - 1 -%}
    {% if (i == 0) or (i % contactPerSlide == 0) -%}
        {% set rank = rank + 1 -%}
    {% endif -%}
<!-- Contact content modal -->
<div class="modal fade" id="modal-contact-content-{{ i + 1 }}" tabindex="-1" role="dialog" aria-labelledby="Contact content">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header justify-content-center">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    <i class="now-ui-icons ui-1_simple-remove"></i>
                </button>
                <h4 class="title title-up">Contact #{{ contactList[i].id }}<br>Message content</h4>
            </div>
            <div class="modal-body">
                <p><strong>Sender first name:</strong> {{ contactList[i].firstName }}<br>
                <strong>Sender family name:</strong> {{ contactList[i].familyName }}<br>
                <strong>Sender email:</strong> {{ contactList[i].email }}</p>
                <p class="text-left">{{ contactList[i].message|raw|nl2br }}</p>
            </div>
            <div class="modal-footer">
                &nbsp;
                <button type="button" class="btn btn-danger" data-dismiss="modal" title="Close viewer">CLOSE</button>
            </div>
        </div>
    </div>
</div>
<!-- End contact content modal -->
<!-- Validation modals -->
<!-- Delete contact -->
<div class="modal fade modal-mini modal-danger" id="cd-modal-{{ contactList[i].id }}" tabindex="-1" role="dialog" aria-labelledby="Deleting action about contact #{{ contactList[i].id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header justify-content-center">
                <div class="modal-profile">
                    <i class="now-ui-icons ui-1_simple-remove"></i>
                </div>
            </div>
            <div class="modal-body">
                <p>Please confirm<br><strong>deleting</strong> action<br>about <strong>contact #{{ contactList[i].id }}</strong>.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-neutral text-muted" data-dismiss="modal" title="Cancel contact deleting">CANCEL</button>
                <form method="post" action="/admin/delete-contact/{{ contactList[i].id }}">
                    <input type="hidden" id="cd_slide_rank" name="cd_slide_rank" value="{{ rank }}">
                    <input type="hidden" id="cd_check" name="{{ cdTokenIndex }}" value="{{ cdTokenValue }}">
                    <input type="hidden" id="cd_id" name="cd_id" value="{{ contactList[i].id }}">
                    {# No need to set cdSubmit because after success state contact doesn't exist anymore! In fact cdSubmit will always set to "0". -#}
                    {% set cdSubmit = 0 -%}
                    <button type="submit" class="btn btn-neutral text-danger" name="cd_submit" title="Confirm contact deleting" value="{{ cdSubmit }}"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;CONFIRM</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- End validation modals -->
{% endfor -%}