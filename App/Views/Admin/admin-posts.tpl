{% extends 'layout.tpl' %}
{% block content %}
<div class="container">
    <div class="row admin-posts">
        <div class="col-lg-10 col-md-12 ml-auto mr-auto text-center">
            {{ include('Admin/admin-post-list.tpl') }}
            {{ include('Admin/admin-comment-list.tpl') }}
        </div>
    </div>
</div>
{% endblock %}