<?php
namespace Core\Page;

use Core\Page\AppTwig;

/**
 * Load template engine to show front-end and set its parameters:
 * Example: here Twig template engine is used, but others can be added (Smarty ...).
 */
class AppPage
{
    use \Core\Helper\Shared\UseRouterTrait;
    use \Core\Helper\Shared\UseConfigTrait;
    use \Core\Helper\Shared\UseHTTPResponseTrait;
    use \Core\Helper\Shared\UseSessionTrait;

    /**
     * @var object: a unique instance of AppPage
     */
    private static $_instance;
    /**
     * @var AppRouter instance
     */
    private static $_router;
    /**
     * @var AppConfig instance
     */
    private static $_config;
    /**
     * @var AppHTTPResponse instance
     */
    private static $_httpResponse;
    /**
     * @var AppSession instance
     */
    private static $_session;
    /**
     * @var object: template engine instance
     */
    private static $_templateEngine;

    /**
     * Instanciate a unique AppPage object (Singleton)
     *
     * @return AppPage: a unique instance of AppPage
     */
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new AppPage();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     *
     * @return void
     */
    private function __construct()
    {
        // WARNING: don't initialize properties from helpers "Traits" here!
    }

    /**
    * Magic method __clone
    *
    * @return void
    */
    public function __clone()
    {
        self::$_httpResponse->set404ErrorResponse(self::$_config::isDebug('Technical error [Debug trace: Don\'t try to clone singleton ' . __CLASS__ . '!]'), self::$_router);
        exit();
    }

    /**
     * Add essential session parameters for a template
     *
     * @param array $vars: template engine variables
     *
     * @return array: entire array with added sesion parameters
     */
    private static function addSessionTemplateParams($vars)
    {
        self::$_session::start(true);
        // Is user authenticated? if this is true, then get needed User infos.
        $user = self::$_session::isUserAuthenticated();
        if ($user != false) {
            // Pass authenticated user name and token datas to template
            $vars['authenticatedUser'] = [
                'userName' => $user['userName'],
                'userKey' => $user['userKey']
            ];
        }
        return $vars;
    }

    /**
     * Add essential common parameters for a template
     *
     * @param array $vars: template engine variables
     *
     * @return array: entire array with added common parameters
     */
    private static function addCommonTemplateParams($vars)
    {
        // Add homepage profile
        $vars['profileTitleDesc'] = self::$_config::getParam('home.profileTitleDesc');
        $vars['profileIntro'] = self::$_config::getParam('home.profileIntro');
        $vars['profileImage'] = self::$_config::getParam('home.profileImage');
        $vars['profileImageDesc'] = self::$_config::getParam('home.profileImageDesc');
        $vars['profileImageLabel'] = self::$_config::getParam('home.profileImageLabel');
        $vars['onlineCVResume'] = self::$_config::getParam('home.onlineCVResume');
        $vars['pdfCVResume'] = self::$_config::getParam('home.pdfCVResume');

        // Add footer profile
        $vars['linkedInProfile'] = self::$_config::getParam('footer.socialLinks.linkedInProfile');
        $vars['githubProfile'] = self::$_config::getParam('footer.socialLinks.githubProfile');
        $vars['stackoverflowProfile'] = self::$_config::getParam('footer.socialLinks.stackoverflowProfile');
        $vars['viadeoProfile'] = self::$_config::getParam('footer.socialLinks.viadeoProfile');
        return $vars;
    }

    /**
     * Render Entirely a particular Twig template
     *
     * @param string $view: path for template to load
     * @param array $vars: parameters to use in template
     *
     * @return string: HTML content type
     */
    public function renderTemplate($view, $vars = [])
    {
        // Instanciate AppTwig
        self::$_templateEngine = new AppTwig();
        // Pass session datas to template
        $vars = self::addSessionTemplateParams($vars);
        // Pass common datas to template
        $vars = self::addCommonTemplateParams($vars);
        // Render template
        return self::$_templateEngine->renderTwigTemplate($view, $vars);
    }

    /**
     * Render only a part of a particular Twig template
     *
     * @param string $view: path for template to load
     * @param string $blockName: block name in template
     * @param array $vars: parameters to use in template
     *
     * @return string: HTML content type
     */
    public function renderBlock($view, $blockName, $vars = [])
    {
        // Instanciate AppTwig
        self::$_templateEngine = new AppTwig();
        // Pass session datas to template
        $vars = self::addSessionTemplateParams($vars);
        // Pass common datas to template
        $vars = self::addCommonTemplateParams($vars);
        // Render block
        return self::$_templateEngine->renderTwigBlock($view, $blockName, $vars);
    }
}
