<?php
namespace App\Controllers;
use Core\AppPage;
use Core\AppHTTPResponse;
use Core\Routing\AppRouter;
use Core\Config\AppConfig;

/**
 * Create a parent controller to use in each controller
 */
abstract class BaseController
{
	/**
	 * @var AppPage instance
	 */
	protected $page;
	/**
	 * @var AppHTTPResponse instance
	 */
	protected $httpResponse;
	/**
	 * @var AppRouter instance
	 */
	protected $router;
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
	 * @param AppPage $page: an instance of AppPage
	 * @param AppHTTPResponse $httpResponse: an instance of AppHTTPResponse
	 * @param AppRouter $router: an instance of AppRouter
	 * @param AppConfig $config: an instance of AppConfig
	 * @return void
	 */
	public function __construct(AppPage $page, AppHTTPResponse $httpResponse, AppRouter $router, AppConfig $config)
	{
        // Initialize router
        $this->router = $router;
        // Get page instance used by router
        $this->page = $this->router->getPage();
        // Set router instance for page instance
        $this->page::setRouter($this->router);
        // Get http response instance used by router
        $this->httpResponse = $this->router->getHTTPResponse();
        // Set page instance used by router for http response instance
        $this->httpResponse::setPage($this->page);
        // Get config instance used by router
        $this->config = $this->router->getConfig();
        // Get session instance used by router
        $this->session = $this->router->getSession();
        // Set router instance for session instance
        $this->session::setRouter($this->router);
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
		$currentModel = new $className($this->config);
		return $currentModel;
	}
}