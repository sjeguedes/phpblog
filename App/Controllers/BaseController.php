<?php
namespace App\Controllers;
use Core\Routing\AppRouter;

/**
 * Create a parent controller to use in each controller
 */
abstract class BaseController
{
	/**
     * @var AppRouter instance
     */
    protected $router;
    /**
     * @var AppContainer instance
     */
    protected $container;
    /**
	 * @var AppPage instance
	 */
	protected $page;
	/**
	 * @var AppHTTPResponse instance
	 */
	protected $httpResponse;
	/**
	 * @var AppConfig instance
	 */
	protected $config;
    /**
     * @var AppSession instance
     */
    protected $session;
	/**
	 * @var object: an instance of current model called by a particular controller
	 */
	protected $currentModel;

    /**
     * @var boolean: true if session id is regenerated, or false
     */
    protected $isSessionIdRegenerated;

	/**
	 * Constructor
	 * @param AppRouter $router: an instance of AppRouter
	 * @return void
	 */
	public function __construct(AppRouter $router)
	{
        // Initialize router
        $this->router = $router;
        // Initialize a service DIC
        $this->container = $this->router->getContainer();
        $this->page = $this->router->getPage();
        $this->httpResponse = $this->router->getHTTPResponse();
        $this->config = $this->router->getConfig();
        $this->session = $this->router->getSession();
        $this->session::start(true);
	}

	/**
	 * Check if called method exists
	 * @param callable $action
	 * @throws exception
	 * @return return boolean|string
	 */
	public function checkAction($action)
	{
	    try {
			if (is_callable([$this, $action])) {
				return true;
			} else {
				throw new \RuntimeException("Technical error: sorry, we cannot find a content for your request. [Debug trace: Action called doesn't exist!]");
			}
		}
		catch (\RuntimeException $e) {
			return $e->getMessage();
		}
	}

	/**
	 * Get model for a particular controller
	 * @param string $className: name of current class
	 * @return object: an instance of current model
	 */
	protected function getCurrentModel($className)
	{
		$className = str_replace('Controller', 'Model', $className);
		$currentModel = new $className($this->router);
		return $currentModel;
	}
}