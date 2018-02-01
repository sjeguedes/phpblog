{% if postComments is not empty %}
<div class="section section-comment-post text-center">
    <div class="container">
        <h2 class="title">Post comments</h2>
        <p class="description">Here, you can have a look at all the reactions about this article:</p>
        <div class="row">
            <div class="col-lg-10 col-md-12 ml-auto mr-auto">
                {% for item in postComments %}
                <article class="comment-item card" data-background-color="yellow">
                    <hr>
                    <h3 class="comment-title">{{ item.title|raw|nl2br }}</h3>
                    <div class="separator separator-primary"></div>
                    <div class="comment-header">
                        <ul class="comment-header-infos">
                            <li>
                                <i class="fa fa-user">&nbsp;</i>by {{ item.nickName|raw }}
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
            {% endfor %}
            </div>
        </div>
    </div>
</div>
{% endif %}