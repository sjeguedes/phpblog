<?php
namespace Core\Routing;
use Core\AppHTTPResponse;
use Core\Config\AppConfig;
/**
 * This "Router" class can:
* - load routes from external XML file.
* - create objects routes and store them in array.
* - find corresponding controller and action if an URL matches a route.
* - send a 404 response if URL doesn't match.
* - create an URL with a "Route" thanks to a name and defined params.
*/
class AppRouter
{
	private $url;
	private $routes = [];
	private $namedRoutes = [];
	private $httpResponse;
	private $routerInstance;
	private $config;

	
	public function __construct($url)
	{
		$this->url = (string) $url;
		$this->config = AppConfig::getInstance();
		$this->getRoutesConfig();
	}

	private function getRoutesConfig()
	{
		// Get routes from yaml file
		$yaml = $this->config::parseYAMLFile(__DIR__ . '/routing.yml');
		//var_dump($yaml);

		foreach ($yaml['routes'] as $route) {
    		$path = $route['path'];
    		$name = $route['name'];
    		$method = $route['method'];

    		$this->createRoute($path, $name, $method);
    	}
    	$this->checkRoutes();
	}

	private function createRoute($path, $name = null, $method)
	{
		$route = new AppRoute($path, $name);
		$this->routes[$method][] = $route;
		$this->namedRoutes[$name] = $route;
	}

	private function checkRoutes()
	{
		// Instanciate a HTTPResponse object
		$this->httpResponse = new AppHTTPResponse();

		// Store the same instance;
		$routerInstance  = clone $this;
		$this->routerInstance = $routerInstance;

		try {
			if(isset($this->routes[$_SERVER['REQUEST_METHOD']])) {
				$noRoute = true;
				try {
					// Loop only if a route exists with this method
					foreach($this->routes[$_SERVER['REQUEST_METHOD']] as $route) {

						// isMatched method returns true: url matched
						if($route->isMatched($this->url)) {
							$noRoute = false;
							// Does action exist?
							$result = $route->getControllerAction($this->httpResponse, $this->routerInstance);
							if(is_string($result)) {
								//var_dump($result);
								// show error view
								return $this->httpResponse->set404ErrorResponse($result, $this->routerInstance);
							}
							else {
								// show right template view
								return $result;
							}
						}		
					}
					if($noRoute) {
						throw new RoutingException("No content is available for your request. [Debug trace: No route matches url!]");
					}
				}
				catch(RoutingException $e) {
					// show error view
					return $this->httpResponse->set404ErrorResponse($this->config::isDebug($e->getMessage()), $this->routerInstance);
				}
			}
			else {
				throw new RoutingException("No content is available for your request. [Debug trace: REQUEST_METHOD is not being used by any routes!]");
				
			}

		}
		catch(RoutingException $e) {
			// show error view
			return $this->httpResponse->set404ErrorResponse($this->config::isDebug($e->getMessage()), $this->routerInstance);
		}
		//var_dump($this->routes);
		
	}

	/* Example: $router->useURL('post|single', ['slug' => 'article-intro', 'id' => '5']); */
	public function useURL($name, $params = [])
	{
			try {
				if(isset($this->namedRoutes[$name])) {
					// $this->namedRoutes[$name] is an instance of "Route"
					$route = $this->namedRoutes[$name];
					return $route->generateURL($params);
				}
				else {
					throw new RoutingException("No content is available for your request. [Debug trace: No route matches this name!]");
				}

			}
			catch(RoutingException $e) {
				// show error view
				return $this->httpResponse->set404ErrorResponse($this->config::isDebug($e->getMessage()), $this->routerInstance);
			}
	}
}