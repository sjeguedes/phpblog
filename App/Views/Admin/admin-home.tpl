{% extends 'layout.tpl' %}
{% block content %}
<div class="container">
    <div class="row admin-home">
        <div class="col-lg-8 col-md-10 ml-auto mr-auto text-center">
            <!-- Login success message box is used here when a redirection is processed! -->
            <p class="alert alert-success form-success{{ loginSuccess == 0 ? ' form-hide'}}" role="alert">
                <i class="now-ui-icons ui-2_like"></i>&nbsp;&nbsp;<strong>WELL DONE!</strong>&nbsp;You authenticated successfully.<br>Welcome in admin area!
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">
                        <i class="now-ui-icons ui-1_simple-remove"></i>
                    </span>
                </button>
            </p>
        </div>
        <!-- End login success message box -->
        <div class="col-lg-10 col-md-12 ml-auto mr-auto text-center">
        	{{ include('Admin/admin-contact-list.tpl') }}
            {{ include('Admin/admin-user-list.tpl') }}
		</div>
	</div>
</div>
{% endblock %}