{% extends "layout.tpl" %}
{% block content %}
{# <div class="container"> #}
    <div class="row">
        <div class="col-md-8 ml-auto mr-auto text-center">
		    <h1>{% if status != 0  %}<strong>{{ status }}</strong> {% endif %}Uncaught HTTP response</h1>
		    <p class="important">
		        {{ message|raw }}<br>
		        <a class="normal-link" href="{{ homeURL }}" title="Back to home"><i class="now-ui-icons arrows-1_minimal-left"></i>&nbsp;Back to home</a>
		    </p>
		</div>
	</div>
{# </div> #}
{% endblock %}

