{% set rank = 0 %}
{% set i = 0 %}
{% set isPublishedComment = 0 %}
{% for item in postComments if (postComments is not empty) and (item.isPublished == 1) %}
{# set isPublishedComment = 1 if conditions are true for loop #}
{% set isPublishedComment = 1 %}
{# set firstFoundPublishedComment = 1 for the first found published comment and generate HTML opened tags for global section #}
{% if (item.isPublished == 1) and (firstFoundPublishedComment is not defined) %}
{% set firstFoundPublishedComment = 1 %}
<div class="section section-post-comment-list text-center">
    <div class="container">
        <h2 class="title">Post comments</h2>
        <p class="description">Here, you can have a look at all the reactions about this article:</p>
        <div class="row">
            <div class="col-md-12 col-lg-10 ml-auto mr-auto">
                <!-- Do not change tags order for ".slider-navigation"
                and ".slider-paging" to avoid Javascript issue -->
                <div class="slider-navigation mt-2 mb-2">&nbsp;</div>
                <div class="post-comment-list-paging slider-paging">
{% endif %}
                    {# Begin slider item "div" if (i == 0) or (i % 5 == 0) #}
                    {% if (i == 0) or (i % 5 == 0) %}
                    {% set rank = rank + 1 %}
                    <!-- Begin Slick slider post comment list slide item if (i == 0) or (i % 5 == 0) -->
                    <div class="slide-item" id="slide-item-{{ rank }}">
                    {# // #}
                    <!-- // -->
                    {% endif %}
                        <article id="comment-{{ item.id }}" class="comment-item card" data-background-color="yellow">
                            <hr>
                            <span class="badge badge-default">-&nbsp;COMMENT&nbsp;-</span>
                            <h3 class="comment-title">{{ item.title|raw|nl2br }}</h3>
                            <div class="separator separator-primary"></div>
                            <div class="comment-header">
                                <ul class="comment-header-infos">
                                    <li class="list-element">
                                        <span class="btn btn-default btn-icon btn-round"><small>NUMBER</small><br>#{{ item.id }}</span>&nbsp;&nbsp;
                                        <i class="fa fa-user">&nbsp;</i>by {{ item.nickName|raw }}&nbsp;
                                        <i class="fa fa-calendar">&nbsp;</i>Added on {{ item.creationDate }}
                                    </li>
                                </ul>
                            </div>
                            <div class="separator separator-neutral"></div>
                            <div class="row">
                                <div class="comment-content text-left">
                                    <p class="px-3">{{ item.content|raw|nl2br }}</p>

                                </div>
                            </div>
                            <hr>
                        </article>
                    {#  End slider item "div" if (i + 1) % 5 == 0 or last i -#}
                    {% if ((i + 1) % 5 == 0) or (i == postComments|length - 1) -%}
                    <!-- End Slick slider post comment list slide item -->
                    {# Interval of 5 items -#}
                    </div>
                    {# // -#}
                    <!-- // -->
                    {% endif -%}
                    {% set i = i + 1 %}
{% endfor %}
                <!-- End Slick slider post comment list paging -->
                </div>
                <!-- // -->
{# Close generated HTML opened tags for global section if there is at least one found published comment #}
{% if isPublishedComment == 1 %}
            </div>
        </div>
    </div>
</div>
{% endif %}