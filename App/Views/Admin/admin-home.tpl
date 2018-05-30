{% extends "layout.tpl" %}
{% block content %}
<div class="container">
    <div class="row admin-list">
        <div class="col-md-12 col-lg-10 ml-auto mr-auto text-center">
        	{{ include('Admin/admin-contact-list.tpl') }}
		</div>
        <div class="col-md-12 col-lg-10 ml-auto mr-auto text-center">
            {{ include('Admin/admin-comment-list.tpl') }}
        </div>
	</div>
</div>
{% endblock %}