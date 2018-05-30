<?php
namespace Core\Routing;
use Core\AppPage;
use Core\AppHTTPResponse;
use Core\Config\AppConfig;

/**
 * Create a router to:
* - load routes from external YAML file.
* - create objects routes and store them in array.
* - find corresponding controller and action if URL matches with a route.
* - send a 404 response if URL doesn't match.
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
		$this->router = $this;
		// TODO: use DIC to instantiate AppConfig object!
		$this->config = AppConfig::getInstance();
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
	 * @return callable: method to call a controller action or 404 error response
	 */
	private function checkRoutes()
	{
		try {
			if(isset($this->routes[$_SERVER['REQUEST_METHOD']])) {
				$noRoute = true;
				try {
					// Loop only if a route exists with this method
					foreach($this->routes[$_SERVER['REQUEST_METHOD']] as $route) {
						// isMatched method returns true: url matched.
						if($route->isMatched($this->url)) {
							$noRoute = false;
							// Does action exist?
							$result = $route->getControllerAction($this->page, $this->httpResponse, $this->router, $this->config);
							break;
							if(is_string($result)) {
								// Show error view
								return $this->httpResponse->set404ErrorResponse($result, $this->router);
							}
							else {
								// Show right template view
								return $result;
							}
						}		
					}
					if($noRoute) {
						throw new RoutingException("No content is available for your request. [Debug trace: No route matches url!]");
					}
				}
				catch(RoutingException $e) {
					// Show error view
					return $this->httpResponse->set404ErrorResponse($this->config::isDebug($e->getMessage()), $this->router);
				}
			}
			else {
				throw new RoutingException("No content is available for your request. [Debug trace: REQUEST_METHOD is not being used by any routes!]");
			}

		}
		catch(RoutingException $e) {
			// Show error view
			return $this->httpResponse->set404ErrorResponse($this->config::isDebug($e->getMessage()), $this->router);
		}
	}

	/**
	 * Generate a complete URL with route name and path params
	 * Example: $router->useURL('post|single', ['slug' => 'article-intro', 'id' => '5']);
	 * @param string $name 
	 * @param array $params 
	 * @throws RoutingException
	 * @return string|callable: corresponding URL or 404 error response
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
				throw new RoutingException("No content is available for your request. [Debug trace: No route matches this name!]");
			}

		}
		catch(RoutingException $e) {
			// Show error view
			return $this->httpResponse->set404ErrorResponse($this->config::isDebug($e->getMessage()), $this->router);
		}
	}
}