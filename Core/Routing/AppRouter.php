<?php
namespace Core\Routing;
use Core\Routing\RoutingException;
use Core\Routing\AppRoute;
use Core\AppPage;
use Core\AppHTTPResponse;
use Core\Config\AppConfig;

/**
 * Create a router class to:
* - load routes from external YAML file.
* - create objects routes and store them in array.
* - find corresponding controller and action if URL matches with a route.
* - send a 404 response (with or without particular error redirection)
* if URL doesn't match, or anything wrong happens.
* - create URL with route thanks to a name and defined path parameters.
*/
class AppRouter
{
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
	 * @var object: store AppPage instance
	 */
	private $page;
	/**
	 * @var object: store AppHTTPResponse instance
	 */
	private $httpResponse;
	/**
	 * @var object: store the current unique AppRouter instance
	 */
	private $router;
	/**
	 * @var object: store unique AppConfig instance
	 */
	private $config;

	/**
	 * Constructor
	 * @param string $url
	 * @return void
	 */
	public function __construct($url)
	{
		// Receive $_GET['url'] value
		$this->url = $url;
		// TODO: use DIC to instantiate AppPage object!
		$this->page = new AppPage();
		// TODO: use DIC to instantiate HTTPResponse object!
		$this->httpResponse = new AppHTTPResponse();
		// Store the same instance
		$this->router = &$this;
		// TODO: use DIC to instantiate AppConfig object!
		$this->config = AppConfig::getInstance();
		// Get existing routes
		$this->getRoutesConfig();
    }

    /**
     * Get used "url" parameter
     * @return string: url to route
     */
    public function getUrl() {
        return $this->url;
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
	 * @param string|null $name: route name
	 * @param string $method: http request method ('POST', 'GET')
	 * @return void
	 */
	private function createRoute($path, $name = null, $method)
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
				$noRoute = true;
				// Loop only if a route exists with this method
				foreach ($this->routes[$_SERVER['REQUEST_METHOD']] as $route) {
					// isMatched method returns true: url matched.
					if ($route->isMatched($this->url)) {
                        $noRoute = false;

                        // Call controller and its appropriate action if both exist.
                        $result = $route->getControllerAction($this->page, $this->httpResponse, $this->router, $this->config);

                        // Does action exist?
                        if (is_string($result)) {
                            // Show error view with 404 error
                            $this->httpResponse->setError(404, $this->config::isDebug($result), $this->router);
                        } else {
                            // Show right template view
                            return $result;
                        }
                        // Stop loop
                        break;
					}
				}
				if ($noRoute) {
                    $request = '<strong>' . htmlentities($this->httpResponse->getWrongUrl($this->url)) . '</strong>';
					throw new RoutingException("No content is available for your request \"${request}\". [Debug trace: no route matches url!]");
				}
			} else {
				throw new RoutingException("No content is available for request. [Debug trace: REQUEST_METHOD is not being used by any routes!]");
			}

		} catch (RoutingException $e) {
			// Show error view with 404 error
            $this->httpResponse->setError(404, $this->config::isDebug($e->getMessage()), $this->router);
		}
	}

	/**
	 * Generate a complete URL with route name and path parameters
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
		}
	}
}