<footer class="footer footer-default">
    <div class="container">
        <div class="text-center">
            <ul class="social-personal-links">
                <li>
                    <a href="{{ linkedInProfile|e('html_attr') }}" title="Linked In profile" class="btn btn-warning btn-icon btn-round" target="_blank">
                        <i class="fa fa-linkedin fa-lg" aria-hidden="true"></i>
                    </a>
                </li>
                <li>
                    <a href="{{ githubProfile|e('html_attr') }}" title="Github Profile" class="btn btn-warning btn-icon btn-round" target="_blank">
                        <i class="fa fa-github fa-lg" aria-hidden="true"></i>
                    </a>
                </li>
                <li>
                    <a href="{{ stackoverflowProfile|e('html_attr') }}" title="Stack Overflow Profile" class="btn btn-warning btn-icon btn-round" target="_blank">
                        <i class="fa fa-stack-overflow fa-lg" aria-hidden="true"></i>
                    </a>
                </li>
                <li>
                     <a href="{{ viadeoProfile|e('html_attr') }}" title="Viadeo profile" class="btn btn-warning btn-icon btn-round" target="_blank">
                        <i class="fa fa-viadeo fa-lg" aria-hidden="true"></i>
                    </a>
                </li>
            </ul>
            <div class="copyright">
                <p>
                    <i class="fa fa-lock fa-lg" aria-hidden="true"></i>&nbsp;&nbsp;<a class="btn-neutral btn-link" href="/admin/login" title="Member access">Member access</a>
                </p>
                <p>
                    &copy; {{ "now"|date("Y") }} <a class="btn-neutral btn-link" href="https://www.dotprogs.com" title="Samuel GUEDES" target="_blank">Samuel GUEDES</a> - Coded by
                    <a class="btn-neutral btn-link" href="https://www.dotprogs.com" title="Coded by himself" target="_blank">himself</a>
                </p>
            </div>
        </div>
    </div>
</footer>