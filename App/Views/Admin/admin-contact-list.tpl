{% if contactList is defined %}
<div class="section section-contact-list">
	<div class="container">
	    <div class="row">
	        <div id="bloc-contactList" class="col-md-12 ml-auto mr-auto text-center">
				<h2 class="title">Admin contact list</h2>
				<p class="description">All the messages sent with contact form are saved in database!</p>
				<div class="contact-box card">
				{% for i in 0..contactList|length - 1 %}
					<div class="p-2 mb-0">
						<div class="flex-table">
							<div class="flex-col">
								<div class="flex-header bg-primary">
									<span class="flex-label">Sending Date</span>
								</div>
								<span class="flex-content">{{ contactList[i].sendingDate }}</span>
							</div>
							<div class="flex-col">
								<div class="flex-header bg-primary">
									<span class="flex-label">Family name</span>
								</div>
								<span class="flex-content">{{ contactList[i].familyName|raw }}</span>
							</div>
							<div class="flex-col">
								<div class="flex-header bg-primary">
									<span class="flex-label">First name</span>
								</div>
								<span class="flex-content">{{ contactList[i].firstName|raw }}</span>
							</div>
							<div class="flex-col">
								<div class="flex-header bg-primary">
									<span class="flex-label">Contact email</span>
								</div>
								<span class="flex-content">{{ contactList[i].email }}</span>
							</div>
							<div class="flex-col">
								<div class="flex-header bg-primary">
									<span class="flex-label">Message sent</span>
								</div>
								<p class="flex-content">{{ contactList[i].message|raw }}</p>
							</div>
						</div>
					</div>
					{% if i != contactList|length - 1 %}
					<hr>
					{% endif %}
				{% endfor %}
				</div>
			</div>
		</div>
	</div>
</div>
{% endif %}