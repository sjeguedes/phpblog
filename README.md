# phpblog
Minimalist PHP Blog system and MVC structure
- Define PSR4 autoloader in composer.json and update it (look at the file content)
- Add these components with PHP composer in Libs/: 
---- twig/twig
---- symfony/yaml
---- google/recaptcha
---- phpmailer/phpmailer
- Rename Core/Config/config-example.yml into Core/Config/config.yml and declare you own parameters
- Add your own CV resume pdf file in Web/assets/files and update its name concerning link placed in App/Views/Home/home-index.tpl

