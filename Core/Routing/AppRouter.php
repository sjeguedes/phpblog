<?php
namespace Core\Routing;
use Core\Routing\RoutingException;
use Core\Routing\AppRoute;
use Core\Config\AppConfig;
use Core\HTTP\AppHTTPResponse;
use Core\Service\AppContainer;
use Core\Page\AppPage;
use Core\Session\AppSession;

/**
 * Create a router class to:
* - instantiate all necessary objects to run application (to render correctly error responses, controllers actions)
*   (AppConfig, AppHTTPResponse, AppContainer, AppPage, AppSession and AppRoute)
* - load routes from external YAML file.
* - create objects routes and store them in array.
* - find corresponding controller and action if URL matches with a route.
* - send a 404 response (with or without particular error redirection)
*   if URL doesn't match, or anything wrong happens.
* - create URL with route thanks to a name and defined path parameters.
*/
class AppRouter
{
	/**
     * @var object: a unique instance of AppRouter
     */
    private static $_instance;
    /**
	 * @var string: URL called
	 */
	private $url;
	/**
	 * @var array: empty array will store routes
	 */
	private $routes = [];
	/**
	 * @var array: will store routes names
	 */
	private $namedRoutes = [];
    /**
     * @var object: store the current unique AppRouter instance
     */
    private $router;
    /**
     * @var AppContainer instance
     */
    private $container;
	/**
	 * @var AppPage instance
	 */
	private $page;
	/**
	 * @var AppHTTPResponse instance
	 */
	private $httpResponse;
	/**
	 * @var AppConfig instance
	 */
	private $config;
    /**
     * @var AppSession instance
     */
    private $session;

    /**
     * Instanciate a unique AppRouter object (Singleton)
     * @param string $url
     * @return AppRouter: a unique instance of AppRouter
     */
    public static function getInstance($url)
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new AppRouter($url);
        }
        return self::$_instance;
    }

	/**
	 * Constructor
	 * @param string $url
	 * @return void
	 */
	private function __construct($url)
	{
		// Store $_GET['url'] value
		$this->url = $url;
        // Store the same instance
        $this->router = $this;
        // Instantiate AppConfig object!
        // This instance needs a http response instance
        $this->config = AppConfig::getInstance();
        $this->config::setRouter($this->router);
        // Instantiate HTTPResponse object!
        // This instance needs router, config, httpResponse, page (see below) instances
        $this->httpResponse = AppHTTPResponse::getInstance();
        $this->httpResponse::setRouter($this->router);
        $this->config::setHTTPResponse($this->httpResponse);
        $this->httpResponse::setConfig($this->config);
        // DIC is instantiated.
        // This instance needs router, config, httpResponse, instances
        $this->container = AppContainer::getInstance();
        $this->container::setRouter($this->router);
        $this->container::setConfig($this->config);
        $this->container::setHTTPResponse($this->httpResponse);
        $this->container::init();
        // Instantiate AppSession object!
        // This instance needs router, config, httpResponse, instances
        $this->session = AppSession::getInstance();
        $this->session::setRouter($this->router);
        $this->session::setHTTPResponse($this->httpResponse);
        $this->session::setConfig($this->config);
		// Instantiate AppPage object!
        // This instance needs router, config, httpResponse, session instances
		$this->page = AppPage::getInstance();
        $this->page::setRouter($this->router);
        $this->page::setHTTPResponse($this->httpResponse);
        $this->page::setConfig($this->config);
        $this->page::setSession($this->session);
        // Set page instance for http response
        $this->httpResponse::setPage($this->page);
    }

    /**
    * Magic method __clone
    * @return void
    */
    public function __clone()
    {
        $this->httpResponse->set404ErrorResponse($this->config->isDebug('Technical error [Debug trace: Don\'t try to clone singleton ' . __CLASS__ . '!]'), $this->router);
        exit();
    }

    /**
     * Get used "url" parameter
     * @return string: url to route
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Get router AppContainer instance
     * @return object: an AppContainer instance
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Get router AppPage instance
     * @return object: an AppPage instance
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Get router page AppHTTPResponse
     * @return object: an AppHTTPResponse instance
     */
    public function getHTTPResponse()
    {
        return $this->httpResponse;
    }

    /**
     * Get router AppConfig instance
     * @return object: an AppConfig instance
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Get router AppSession instance
     * @return object: an AppSession instance
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Init application router getting routes
     * @return void
     */
    public function init()
    {
        // Get existing routes
        $this->getRoutesConfig();
    }

	/**
	 * Parse routes configuration, call routes creation and call routes checking
	 * @return void
	 */
	private function getRoutesConfig()
	{
		// Get routes from yaml file
		$yaml = $this->config::parseYAMLFile(__DIR__ . '/routing.yml');
        foreach ($yaml['routing'] as $route) {
    		$path = $route['path'];
    		$name = $route['name'];
    		$method = $route['method'];
    		$this->createRoute($path, $name, $method);
    	}
    	$this->checkRoutes();
	}

	/**
	 * Create routes and their names
	 * @param string $path: a path to compare with url which may contain parameters
	 * @param string $name: route name
	 * @param string $method: http request method ('POST', 'GET')
	 * @return void
	 */
	private function createRoute($path, $name, $method)
	{
		// TODO: use DIC to instantiate AppRoute object!
		$route = new AppRoute($path, $name);
		$this->routes[$method][] = $route;
		$this->namedRoutes[$name] = $route;
	}

	/**
	 * Check if a route matches with called url
	 * @throws RoutingException
	 * @return callable|void: method to call a controller action
	 */
	private function checkRoutes()
	{
        try {
			if(isset($this->routes[$_SERVER['REQUEST_METHOD']])) {
				$isNoRoute = true;
				// Loop only if a route exists with this method
				foreach ($this->routes[$_SERVER['REQUEST_METHOD']] as $route) {
                    // isMatched method returns true: url matched
					if ($route->isMatched($this->url)) {
                        $isNoRoute = false;
                        // Call controller and its appropriate action if both exist.
                        $result = $route->getControllerAction($this->router);
                        // Does action exist or does exception happen when action is called (PDOException, ...)?
                        if (is_string($result)) {
                            // Get initial requested URL
                            $request = htmlentities('/' . rtrim($this->url, '/'));
                            // No HTTP response refresh case
                            throw new RoutingException("exception=$result<br>Your initial request was \"<strong>$request</strong>\".");
                        } else {
                            // Action is correctly called, so show right template view!
                            return $result;
                        }
                        // Stop loop
                        break;
					}
				}
                // No existing route
                if ($isNoRoute) {
                    // Get initial requested URL
                    $request = htmlentities('/' . rtrim($this->httpResponse->getWrongUrl($this->url), '/'));
                    // Technical exception with custom error URL refresh (So initial route is not found but exists!)
                    if (isset($_GET['refreshException']) && $_GET['refreshException']) {
                        // Manage exception after refresh for "isRefreshed" parameter in HTTPResponse setError() method
                        // An exception happens when action is called (PDOException, ...)
                        $result = $route->getControllerAction($this->router);
                        // HTTP response refresh case
                        throw new RoutingException((string) $result . "<br>Your initial request was \"<strong>$request</strong>\".");
                    // No existing route! (with or without custom error URL refresh)
                    } else {
                        throw new RoutingException("No content is available for your request \"<strong>$request</strong>\". [Debug trace: no route matches url!]");
                    }
                }
			} else {
				throw new RoutingException("No content is available for request method. [Debug trace: request method \"<strong>{$_SERVER['REQUEST_METHOD']}</strong>\" is not being used by any routes!]");
			}
		} catch (RoutingException $e) {
			// Show error view with 404 error
            $this->httpResponse->setError(404, $this->config::isDebug($e->getMessage()), $this->router);
            exit();
		}
	}

	/**
	 * Retrieve a complete URL with route name and path parameters
	 * Example: $router->useURL('Blog\Post|showSingle', ['slug' => 'article-intro', 'id' => '5']);
	 * @param string $name
	 * @param array $params
	 * @throws RoutingException
	 * @return string|void: corresponding URL
	 */
	public function useURL($name, $pathParams = [])
	{
        try {
			if(isset($this->namedRoutes[$name])) {
				// $this->namedRoutes[$name] is an instance of "Route".
				$route = $this->namedRoutes[$name];
				return $route->generateURL($pathParams);
			}
			else {
				throw new RoutingException("No content is available for request. [Debug trace: no route matches this name!]");
			}
		}
		catch(RoutingException $e) {
			// Show error view with 404 error
            $this->httpResponse->setError(404, $this->config::isDebug($e->getMessage()), $this->router);
            exit();
		}
	}
}