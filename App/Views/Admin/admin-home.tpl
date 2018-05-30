{% extends "layout.tpl" %}
{% block content %}
<div class="container">
    <div class="row">
        <div class="col-md-10 ml-auto mr-auto text-center">
        	{{ include('Admin/admin-contact-list.tpl') }}
		</div>
	</div>
</div>
{% endblock %}