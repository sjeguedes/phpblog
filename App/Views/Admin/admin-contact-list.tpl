{% if contactList %}
<div class="section section-contact-list">
	<div class="container">
	    <div class="row">
	        <div class="col-md-12 ml-auto mr-auto text-center">
				<h2 class="title">Admin contact list</h2>
				
				<div class="flex-table">
					<div class="flex-col bg-primary">
						Sending Date
					</div>
					<div class="flex-col bg-primary">
						Family name
					</div>
					<div class="flex-col bg-primary">
						First name
					</div>
					<div class="flex-col bg-primary">
						Contact email
					</div>
					<div class="flex-col bg-primary">
						<p>Message sent</p>
					</div>
				</div>
				{% for item in contactList %}
				<div class="flex-table">
					<div class="flex-col">
						{{ item.contact_sendingDate }}
					</div>
					<div class="flex-col">
						{{ item.contact_familyName }}
					</div>
					<div class="flex-col">
						{{ item.contact_firstName }}
					</div>
					<div class="flex-col">
						{{ item.contact_email }}
					</div>
					<div class="flex-col">
						<p>{{ item.contact_message }}</p>
					</div>
				</div>
				{% endfor %}
			</div>
		</div>
	</div>
</div>
{% endif %}