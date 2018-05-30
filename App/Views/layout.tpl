<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <link rel="icon" href="/assets/images/favicon.ico">
    <link rel="apple-touch-icon" sizes="76x76" href="/assets/images/apple-icon.png">
    <link rel="icon" type="image/png" href="/assets/images/favicon.png">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>phpBlog - {{ metaTitle }}</title>
    <meta name="description" content="{{ metaDescription }}">
    {% if metaRobots is defined %}
    <meta name="robots" content="{{ metaRobots }}">
    {% endif %}
    <meta name="author" content="Samuel GUEDES">
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no" name="viewport" />
    <!-- Fonts and icons -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
    <!-- CSS Files -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="/assets/css/now-ui-kit.css?v=1.1.0" rel="stylesheet" />
{% if CSS is defined %}
{% for item in CSS %}
    <!-- CSS for plugin: {{ item['pluginName'] }} -->
    <link href="{{ item['src'] }}" rel="stylesheet" />
{% endfor %}
{% endif %}
    <!-- Custom CSS -->
    <link href="/assets/css/phpblog.css?v=1.0" rel="stylesheet" />

{% if JS is defined %}
{% for item in JS %}
{% if item['placement'] == 'top' %}
    <!-- JS in head: {% if item['pluginName'] is defined %}{{ item['pluginName'] }}{% else %}{{ item['src'] }}{% endif %} -->
    <script src="{{ item['src'] }}"{{ item['attributes'] ? ' '~item['attributes'] : '' }}></script>
{% endif %}
{% endfor %}
{% endif %}

</head>

<body class="landing-page sidebar-collapse">
    <!-- Javascript disabled -->
    <noscript>
        <div class="no-js-box">
            <p class="alert alert-warning text-center" role="alert">
                <i class="now-ui-icons travel_info"></i>&nbsp;&nbsp;
                <strong>CAUTION!</strong>&nbsp;Javascript is disabled on your browser.<br>You can't use functionalities normally!<br>Please activate it.
            </p>
        </div>
    </noscript>
    <!-- End Javascript disabled -->
    {{ include('navigation.tpl') }}
    <div class="wrapper">
        {{ include('header.tpl') }}
        <div class="content">
            {{ DEBUG ? DEBUG|raw : '' }}
            {% block content %}{% endblock %}
        </div>
        {{ include('footer.tpl') }}
    </div>
</body>

<!--   Core JS Files   -->
<script src="/assets/js/core/jquery.3.2.1.min.js" type="text/javascript"></script>
<script src="/assets/js/core/popper.min.js" type="text/javascript"></script>
<script src="/assets/js/core/bootstrap.min.js" type="text/javascript"></script>
<script src="/assets/js/plugins/bootstrap-switch.js"></script>
<script src="/assets/js/plugins/nouislider.min.js" type="text/javascript"></script>
<script src="/assets/js/plugins/bootstrap-datepicker.js" type="text/javascript"></script>
<script src="/assets/js/now-ui-kit.js?v=1.1.0" type="text/javascript"></script>

{% if JS is defined %}
{% for item in JS %}
{% if item['placement'] == 'bottom' %}
<!-- JS in head: {% if item['pluginName'] is defined %}{{ item['pluginName'] }}{% else %}{{ item['src'] }}{% endif %} -->
<script src="{{ item['src'] }}"{{ item['attributes'] ? ' '~item['attributes'] : '' }}></script>
{% endif %}
{% endfor %}
{% endif %}

</html>