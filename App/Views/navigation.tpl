<!-- Navbar -->
<nav class="navbar navbar-expand-lg bg-phpblog-neutral fixed-top navbar-transparent " color-on-scroll="50">
    <div class="container">
        <div class="navbar-translate">
            <a class="navbar-brand" href="/" title="phpBlog">
                <span class="btn btn-neutral btn-icon btn-round btn-lg"><img class="custom-logo" src="/assets/images/phpblog/custom-logo.png" alt="Logo de Samuel GUEDES" ></span>
                <span rel="tooltip" title="phpBlog" data-placement="right" class="navbar-brand-title"><strong>&nbsp;phpBlog&nbsp;</strong></span>
            </a>
            
            <button class="navbar-toggler navbar-toggler" type="button" data-toggle="collapse" data-target="#navigation" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-bar bar1"></span>
                <span class="navbar-toggler-bar bar2"></span>
                <span class="navbar-toggler-bar bar3"></span>
            </button>
        </div>
        <!-- <div class="collapse navbar-collapse justify-content-end" id="navigation" data-nav-image="../assets/images/blurred-image-1.jpg"> -->
        <div class="collapse navbar-collapse justify-content-end" id="navigation">
            <ul class="navbar-nav">
                <li class="nav-item dropdown admin-menu">
                    <a href="#" class="nav-link dropdown-toggle" id="navbarDropdown" data-toggle="dropdown">
                        <i class="fa fa-cog fa-lg" aria-hidden="true"></i>&nbsp;Admin menu 
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-header">Back-end pages</a>
                        <a class="dropdown-item" href="/posts" title="View all posts">View all posts</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="index.php?v=blog&t=admin-create-post" title="Edit a new post">Edit a new post</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="index.php?v=blog&t=admin-update-post" title="Update a post">Update a post</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="index.php?v=blog&t=admin-delete-post" title="Delete a post">Delete a post</a>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/" title="Home">
                        <i class="fa fa-dot-circle-o fa-lg" aria-hidden="true"></i>&nbsp;- Home -
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/posts/page/1" title="Blog">
                        <i class="fa fa-bullhorn fa-lg" aria-hidden="true"></i>&nbsp;- Blog -
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<!-- End Navbar -->