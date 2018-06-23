# phpblog
Minimalist PHP Blog system and MVC structure

- Use "Web/" directory as your document root (routes and assets)
- Define PSR-4 autoloader in composer.json and update it (look at file content)
- Add these components in composer.json with PHP Composer in "Libs/" (look at file content): 
---- twig/twig
---- symfony/yaml
---- google/recaptcha
---- phpmailer/phpmailer
---- voku/urlify
---- ezyang/htmlpurifier
- Add these components to improve code quality (only for development):
---- squizlabs/php_codesniffer
---- phpmd/phpmd
---- friendsofphp/php-cs-fixer
- Rename "Core/Config/config-example.yml" into "Core/Config/config.yml" and declare your own parameters
- Add your own CV resume pdf file in "Web/assets/files/" defined in config.yml
- Minify CSS and JS files for production

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/853761373ad943a6882aea3e89008af8)](https://www.codacy.com/app/sje.guedes/phpblog?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=sjeguedes/phpblog&amp;utm_campaign=Badge_Grade)
