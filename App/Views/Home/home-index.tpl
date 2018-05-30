{% extends 'layout.tpl' %}
{% block content %}
<div class="section section-about-us">
	<div class="container">
	    <div class="row">
	        <div class="col-md-8 ml-auto mr-auto text-center">
	        	<h1>Welcome on board!</h1>
	            <h2 class="title">Who i am?</h2>
	            <h3 class="description">{{ profileTitleDesc|raw }}</h3>
	            <p class="important">
			        {{ profileIntro|raw }}
			    </p>
			    <p class="separator separator-primary"></p>
                <img class="rounded-circle img-raised" src="/assets/images/phpblog/{{ profileImage|e('html_attr') }}" alt="{{ profileImageDesc|e('html_attr') }}" >
                <p class="category my-4">{{ profileImageLabel|e('html_attr') }}</p>
                <p>
                	<a class="btn btn-info" target="_blank" href="{{ onlineCVResume|e('html_attr') }}" title="Look at my online CV resume."><i class="fa fa-id-card-o fa-lg"></i><strong>&nbsp;&nbsp;I invite you to look at my online CV resume, if you are inspired.</strong></a>
                </p>
                <p>
                	<a class="btn btn-primary" target="_blank" href="/assets/files/{{ pdfCVResume|e('html_attr') }}" title="Download my CV resume."><i class="fa fa-file-pdf-o fa-lg"></i><strong>&nbsp;&nbsp;You are able to download a PDF version here!</strong></a>
                </p>
                <div class="separator separator-primary"></div>
	        </div>
	    </div>
	</div>
</div>
{% block contactForm %}
{{ include("Home/home-contact-form.tpl") }}
{% endblock %}
{% endblock %}