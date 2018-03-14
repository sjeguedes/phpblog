<?php
namespace Core\Page;
use Core\Page\AppTwig;

/**
 * Load template engine to show front-end and set its parameters:
 * Example: here Twig template engine is used
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
     * @return void
     */
    private function __construct()
    {
        // WARNING: don't initialize properties from helpers "Traits" here!
    }

    /**
    * Magic method __clone
    * @return void
    */
    public function __clone()
    {
        self::$_httpResponse->set404ErrorResponse(self::$_config::isDebug('Technical error [Debug trace: Don\'t try to clone singleton ' . __CLASS__ . '!]'), self::$_router);
        exit();
    }

    /**
     * Add essential session parameters for a template
     * @param array $vars: template engine variables
     * @return array: entire array with added sesion parameters
     */
    public static function addSessionTemplateParams($vars) {
        self::$_session::start(true);
        $sessionDatas = self::$_session::getDatas();
        // Did session expire?
        $expiredSession = isset($sessionDatas['expiredSession']) ? true : false;
        if ($expiredSession) {
            $vars['expiredSession'] = $expiredSession;
        }
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
	 * Render Entirely a particular Twig template
	 * @param string $view: path for template to load
	 * @param array $vars: parameters to use in template
	 * @return string: HTML content type
	 */
	public function renderTemplate($view, $vars = [])
	{
        // Instanciate AppTwig
        self::$_templateEngine = new AppTwig();
        // Pass session datas to template
        $vars = self::addSessionTemplateParams($vars);
        // Render template
        return self::$_templateEngine->renderTwigTemplate($view, $vars);
	}

	/**
	 * Render only a part of a particular Twig template
	 * @param string $view: path for template to load
	 * @param string $blockName: block name in template
	 * @param array $vars: parameters to use in template
	 * @return string: HTML content type
	 */
	public function renderBlock($view, $blockName, $vars = [])
	{
		// Instanciate AppTwig
        self::$_templateEngine = new AppTwig();
        // Pass session datas to template
        $vars = self::addSessionTemplateParams($vars);
        // Render block
		return self::$_templateEngine->renderTwigBlock($blockName, $vars);
	}
}