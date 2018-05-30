{% extends "layout.tpl" %}
{% block content %}
<div class="container">
    <div class="row">
        <div class="col-md-10 ml-auto mr-auto text-center">
            <a class="normal-link" href="/posts/page/{{ post[0].temporaryParams['pagingNumber'] }}" title="Go back to post list!"><i class="now-ui-icons arrows-1_minimal-left">&nbsp;</i>Go back to post list!</a>
            <section class="post-detail">
                <article class="post-item card my-4 px-3 py-4" ">
                    <span class="badge badge-primary">-&nbsp;POST&nbsp;-</span>
                    <h1 class="post-title">{{ post[0].title|raw|nl2br }}</h1>
                    <div class="post-img">
                        <img class="rounded img-raised" src="http://placehold.it/480x360" alt="{{ post[0].title|e('html_attr') }}">
                    </div>
                    <div class="separator separator-primary"></div>
                    <div class="post-header">
                        <ul class="post-header-infos">
                            <li>
                                {% if post[0].temporaryParams['author'] %} <i class="fa fa-user">&nbsp;</i>by {{ post[0].temporaryParams['author'].pseudo|raw }}<br>{% endif %}
                                <i class="fa fa-calendar">&nbsp;</i>Published on {{ post[0].creationDate }}&nbsp;-&nbsp;Updated on {{ post[0].updateDate }}
                            </li>
                            <li>
                                {% set countedComments = 0 %}
                                {% for comment in postComments if comment.isPublished == 1 %}
                                    {% set countedComments = loop.index %}
                                {% endfor %}
                                {% if countedComments != 0 %}
                                <i class="fa fa-comment">&nbsp;</i>{{ countedComments }} comment(s)
                                {% endif %}
                            </li>
                        </ul>
                    </div>
                    <div class="separator separator-neutral"></div>
                    <div class="post-intro text-left">
                        <p><strong>{{ post[0].intro|raw|nl2br }}</strong></p>
                        <p>{{ post[0].content|raw|nl2br }}</p>
                        <div class="text-right"><a class="normal-link" href="/admin/update-post/{{ post[0].id }}" title="Update post: {{ post[0].title|e('html_attr') }}"><i class="now-ui-icons arrows-1_minimal-right">&nbsp;</i>Update this post</a></div>
                    </div>
                    <hr>
                </article>
            </section>
        </div>
    </div>
</div>
{% block commentListOnPost %}
{% include("Blog/Post/post-comment-list.tpl") %}
{% endblock %}
{% block commentFormOnPost %}
{% include("Blog/Post/post-comment-form.tpl") %}
{% endblock %}
{% endblock %}