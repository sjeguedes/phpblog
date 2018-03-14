{% extends "layout.tpl" %}
{% block content %}

{% if postListOnPage['postsOnPage'] is not empty %}
    <!-- Paginated posts -->
    {% set postsDatas =  postListOnPage['postsOnPage'] %}
{% else %}
    <!-- Simple list of all posts -->
    {% set postsDatas =  postList %}
{% endif %}
<div class="container">
    <div class="row">
        <div class="col-md-10 ml-auto mr-auto text-center">
            <section class="posts-list">
                <h1>Posts list</h1>
                <p class="important">
                    <strong>Here, you can have a look at all the published posts.</strong>
                </p>
            {% for item in postsDatas %}
                <article class="post-item card px-3 py-4" ">
                    <h2 class="post-title">{{ item.title|raw }}</h2>
                    <div class="separator separator-primary"></div>
                    <div class="post-header">
                        <ul class="post-header-infos">
                            <li>
                                {% if item.author is defined %} <i class="fa fa-user">&nbsp;</i>by {{ item.author.nickName|raw }} {% endif %}
                                <i class="fa fa-calendar">&nbsp;</i>Published on {{ item.creationDate }}&nbsp;-&nbsp;Updated on {{ item.updateDate }}
                            </li>
                            {% if item.temporaryParams['postComments'] is defined %}
                            <li>
                                {% set countedComments = 0 %}
                                {% for comment in item.temporaryParams['postComments'] if comment.isPublished == 1 %}
                                    {% set countedComments = loop.index %}
                                {% endfor %}
                                {% if countedComments != 0 %}
                                <i class="fa fa-comment">&nbsp;</i>{{ countedComments }} comment(s)
                                {% endif %}
                            </li>
                            {% endif %}
                        </ul>
                    </div>
                    <div class="separator separator-neutral"></div>
                    <div class="row">
                        <div class="post-thumbnail col-md-12 col-lg-3 mb-4">
                            <img class="rounded img-raised" src="http://placehold.it/320x240" alt="{{ item.title|e('html_attr') }}">
                        </div>
                        <div class="post-intro text-left col-md-12 col-lg-9">
                            <p class="px-3">{{ item.intro|raw }}</p>
                            <div class="text-right px-3"><a href="/post/{{ item.slug|e('url') }}-{{ item.id }}" title="{{ item.title|e('html_attr') }}">Read more +</a></div>
                        </div>
                    </div>
                    <hr>
                </article>
            {% endfor %}
            {% if postListOnPage %}
                <!-- Pagination -->
                {% set pageQuantity = postListOnPage['pageQuantity'] %}
                {% set currentPage = postListOnPage['currentPage'] %}
                <nav class="post-list-nav" aria-label="Post list navigation">
                    <ul class="pagination">
                        {% if (currentPage - 1) > 0 %}
                        <li class="page-item">
                            <a class="page-link" href="/posts/page/{{ currentPage - 1 }}" title="Previous page" aria-label="Previous page">
                                <span aria-hidden="true"><i class="fa fa-angle-double-left" aria-hidden="true"></i></span>
                            </a>
                        </li>
                        {% endif %}
                        {% for i in 1..pageQuantity %}
                        <li class="page-item{{ (currentPage == i) ? ' active' : '' }}">
                            <a class="page-link" href="/posts/page/{{ i }}" title="page {{ i }}" aria-label="page {{ i }}">{{ i }}</a>
                        </li>
                        {% endfor %}
                        {% if (currentPage + 1) <= pageQuantity %}
                        <li class="page-item">
                            <a class="page-link" href="/posts/page/{{ currentPage + 1 }}" title="Next page" aria-label="Next page">
                                <span aria-hidden="true"><i class="fa fa-angle-double-right" aria-hidden="true"></i></span>
                            </a>
                        </li>
                        {% endif %}
                    </ul>
                </nav>
                <!-- End Pagination -->
            {% endif %}
            </section>
        </div>
    </div>
</div>
{% endblock %}