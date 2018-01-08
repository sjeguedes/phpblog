<?php
namespace Core\Routing;
use Core\AppPage;
use Core\AppHTTPResponse;
use Core\Routing\AppRouter;
use Core\Config\AppConfig;
/**
 * Define a route used by router
 * Enable access to url matching route
 */
class AppRoute
{
	/**
	 * $path is a path to check
	 * @var string
	 */
	private $path;
	/**
	 * $matches stores matching value(s) in url
	 * @var array
	 */
	/**
	 * $name is used to call a route by a name which identify controller and action
	 * @var string
	 */
	private $name;
	/**
	private $matches = [];
	/**
	 * $params stores params used in route with attached regex to match url
	 * @var array
	 */
	private $params = [];

	/**
	 * Initialise $path and $name
	 * @param string $path: path to check
	 * @param string $name: name given to route
	 * @return void
	 */
	public function __construct($path, $name)
	{
		$this->path = $path;
		$this->name = $name;
	}

	/**
	 * Try to match url with defined path
	 * @param  string $url: URL we want to access
	 * @return boolean 
	 */
	public function isMatched($url)
	{
		$url = trim($url, '/');
		$path = trim($this->path, '/');
		$path = preg_replace_callback('/\:([\w]+)/', [$this, 'matchParameter'] , $path);
		$regex = "#^$path$#i";

		// $url doesn't match
		if(!preg_match($regex, $url, $matches))
		{
			return false;
		}
		// Delete first value $matches[0] not to keep complete url
		array_shift($matches);

		// Initialise array
		$this->matches = $matches;
		return true;
	}

	/**
	 * matchParameter is a callback function to manage different matching parameters
	 * @param  array: $match contains captured matched parameters 
	 * @return string: pattern
	 */
	private function matchParameter($match)
	{
		if(!empty($match)) {	
			switch ($match[1]) {
				case 'id':
					$this->useParameter($match[1], '[\d]+');
					break;
				case 'slug':
					$this->useParameter($match[1], '[a-z0-9-]+');
					break;
				case 'pageId':
					$this->useParameter($match[1], '[\d]+');
					break;
				default:
					$this->useParameter($match[1], '[^/]+');
					break;
			}
			
			// Don't forget '()' to use a group in pattern
			return '(' . $this->params[$match[1]] . ')';
		}
		else {
			// All the characters which are not a "/"
			return '([^/]+)';
		}
	}

	/**
	 * Stores parameters we want to match
	 * @param  string $param: defined param to find
	 * @param  string $regex: pattern to match this parameter
	 * @return void
	 */
	private function useParameter($param, $regex)
	{
		// Escape "()" to prevent these groups from being matched
		$paramRegex = str_replace('(','(?:', $regex);
		$this->params[$param] = $paramRegex;
	}

	/**
	 * Call and execute a Closure or controller method with its arguments
	 * @return object controller method is executed
	 */
	public function getControllerAction(AppPage $page, AppHTTPResponse $httpResponse, AppRouter $router, AppConfig $config)
	{
		// We use parameters as a string to call and execute a controller method
		$explode = explode('|', $this->name);
		$controllerPath = $explode[0];
		$controllerClass = 'App\Controllers\\' . $controllerPath . 'controller';

		try {
			if(class_exists($controllerClass)) {
				$controller = new $controllerClass($page, $httpResponse, $router, $config);
				$action = $explode[1];

				// is this action callable? If true, it exists : call it!
				if($controller->checkAction($action) === true) {
					return call_user_func_array([$controller, $action], [$this->matches]);
				}
				else {
					// Action called doesn't exist
					$errorMessage = $controller->checkAction($action);
				}
			}
			else {
				// Controller called doesn't exist
				$controllerClass = preg_replace('/(.*)\\\\(.*)$/', '$2', $controllerClass); // a literal backslash needs to be escaped twice: once for the string, and once for the regex engine
				throw new \RuntimeException('Controller called (' . $controllerClass . ') doesn\'t exist!');
			}
		}
		catch(\RuntimeException $e) {
			$errorMessage = 'Technical error - Sorry, something wrong happened. [Debug trace: ' . get_class($e) . ' - ' . $e->getMessage() . ']';
		}
		return $errorMessage;		
	}

	public function generateURL($params = null)
	{
		$smartURL = $this->path;
		if(!empty($params)) {
			foreach ($params as $key => $value) {
				$smartURL = str_replace(":$key", $value, $smartURL);
			}
		}
		return $smartURL;
	}

}