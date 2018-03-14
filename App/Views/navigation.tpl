<!-- Navbar -->
<nav class="navbar navbar-expand-lg bg-phpblog-neutral fixed-top navbar-transparent" color-on-scroll="50">
    <div class="container">
        <div class="navbar-translate">
            <a class="navbar-brand" href="/" title="phpBlog">
                <span class="btn btn-neutral btn-icon btn-round btn-lg"><img class="custom-logo" src="/assets/images/phpblog/custom-logo.png" alt="phpBlog" ></span>
                <span title="phpBlog" class="navbar-brand-title"><strong>&nbsp;phpBlog&nbsp;</strong></span>
            </a>
            <button class="navbar-toggler navbar-toggler" type="button" data-toggle="collapse" data-target="#navigation" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-bar bar1"></span>
                <span class="navbar-toggler-bar bar2"></span>
                <span class="navbar-toggler-bar bar3"></span>
            </button>
        </div>
        <div class="collapse navbar-collapse justify-content-end" id="navigation">

            <ul class="navbar-nav">
                {% if authenticatedUser is defined %}
                <!-- Show user profile -->
                <li class="navbar-nav-profile">
                    <a href="/admin/logout/?userKey={{ authenticatedUser['userKey']|e('html_attr') }}" title="Logout">
                        <strong><small>CONNECTED USER</small><span>{{ authenticatedUser['userName'][0]|title ~' '~ authenticatedUser['userName'][1]|upper }}&nbsp;<i class="fa fa-user fa-lg" aria-hidden="true"></i></span></strong>
                    </a>
                </li>
                <!-- End show ser profile -->
                <li class="nav-item dropdown admin-menu">
                    <a href="#" class="nav-link dropdown-toggle" id="navbarDropdown" data-toggle="dropdown">
                        <i class="fa fa-cog fa-lg" aria-hidden="true"></i>&nbsp;Admin&nbsp;menu
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <span class="dropdown-header">Back-end pages</span>
                        <a class="dropdown-item" href="/admin" title="Admin homepage"><i class="fa fa-chevron-right" aria-hidden="true"></i>&nbsp;Admin homepage</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="/admin/#contact-list" title="Look at contact message list">&nbsp;&nbsp;&nbsp;<i class="fa fa-angle-right fa-lg" aria-hidden="true"></i>&nbsp;Look at contact message list</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="/admin/#comment-list" title="Look at comment list">&nbsp;&nbsp;&nbsp;<i class="fa fa-angle-right fa-lg" aria-hidden="true"></i>&nbsp;Look at comment list</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="/admin/add-post" title="Add a new post"><i class="fa fa-chevron-right" aria-hidden="true"></i>&nbsp;Add a new post</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="/admin/#post-list" title="Edit/Delete a post"><i class="fa fa-chevron-right" aria-hidden="true"></i>&nbsp;Edit/Delete a post</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="/admin/logout/?userKey={{ authenticatedUser['userKey']|e('html_attr') }}" title="Logout"><i class="fa fa-chevron-right" aria-hidden="true"></i>&nbsp;<strong>Session logout</strong>&nbsp;<i class="fa fa-user-times fa-lg" aria-hidden="true"></i></a>
                    </div>
                </li>
                {% endif %}
                <li class="nav-item">
                    <a class="nav-link" href="/" title="Home">
                        <i class="fa fa-dot-circle-o fa-lg" aria-hidden="true"></i>&nbsp;Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/posts/page/1" title="Blog">
                        <i class="fa fa-bullhorn fa-lg" aria-hidden="true"></i>&nbsp;Blog
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<!-- End Navbar -->