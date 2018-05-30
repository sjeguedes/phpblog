<?php
namespace Core\Config;
use Core\AppHTTPResponse;
// Symfony Yaml parser Component
use Symfony\Component\Yaml\Yaml;

/**
 * Manage App global configuration
 */
class AppConfig
{
	/**
     * @var object: a unique instance of AppConfig
     */
    private static $_instance;
    /**
     * @var array: an array of config yaml file parameters
     */
    private static $_params;
    /**
     * @var object: an instance of AppHTTPResponse
     */
	private static $_httpResponse;

    /**
     * Instanciate a unique AppConfig object (Singleton)
     * @return AppConfig: a unique instance of AppConfig
     */
	public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new AppConfig();
        }
        return self::$_instance;
    }

	/**
     * Constructor
     * @return void
     */
    private function __construct()
	{
		self::$_params = self::getConfigParams();
		self::$_httpResponse = new AppHTTPResponse();
	}

    /**
    * Magic method __clone
    * @return void
    */
    public function __clone()
    {
        self::$_httpResponse->set404ErrorResponse(self::isDebug('Technical error [Debug trace: Don\'t try to clone singleton ' . __CLASS__ . '!]'));
    }

	/**
     * Get all configuration parameters
     * @return array: an array which contains all the parameters
     */
    private static function getConfigParams()
	{
		// Get parameters from config yaml file
		$yaml = self::parseYAMLFile(__DIR__ . '/config.yml');
		return $yaml['config'];
	}

    /**
     * Get a parameter value from config yaml file
     * @param string $keys: array keys to explode in string argument with a "."
     * @return mixed|boolean: parameter value (type depends on returned value) or false (if no value exists in config yaml file)
     */
    public static function getParam($keys)
    {
        $keys = explode('.', $keys);
        // Check if first array key exists
        if (isset(self::$_params[$keys[0]])) {
            $currentValue = self::$_params[$keys[0]];
        } else {
            return false;
        }
        // Loop on exploded keys
        $countedKeys = count($keys);
        for ($i = 1; $i <= $countedKeys; $i ++) {
            // Return array value if it is the last key
            if ($i == $countedKeys) {
                return $currentValue;
            }
            $key = $keys[$i];
            // Explore multidimensional array if several keys exist and check if child array key exists
            if (isset($currentValue[$key])) {
                $currentValue = $currentValue[$key];
            } else {
                return false;
            }
        }
    }

	/**
     * Show complementary message if App personal debug is set to true
     * @param string $string: message to show
     * @return string: message or empty string
     */
	public static function isDebug($string)
	{
		// Debug is not activated
        if (!self::getParam('appDebug')) {
            // Don't show message
			$string = preg_replace('/\[Debug trace:.*\]$/', '', $string);
		}
		return $string;
	}

	/**
     * Parse a yaml file
     * @param string $filePath: path to access file
     * @return array: an array of datas declared in yaml file
     */
    public static function parseYAMLFile($filePath)
	{
		try {
    		$fileDatas = Yaml::parse(file_get_contents($filePath));
		} catch (ParseException $e) {
			// show error view
    		$errorMessage = printf("Unable to parse the YAML string - %s", $e->getMessage());
    		$this->httpResponse->set404ErrorResponse(self::isDebug('Technical error [Debug trace: ' . $errorMessage . ']'));
		}
		return $fileDatas;
	}

	/**
     * Dump datas in a yaml file
     * @param string $filePath: path to access file
     * @param array $array: datas to dump
     * @return void
     */
    public static function dumpYAMLFile($filePath, $array)
	{
		try {
    		$yaml = Yaml::dump($array);
    		file_put_contents($filePath, $yaml);
		} catch (DumpException $e) {
			// show error view
    		$errorMessage = printf("Unable to dump datas - %s", $e->getMessage());
    		$this->httpResponse->set404ErrorResponse(self::isDebug('Technical error [Debug trace: ' . $errorMessage . ']'));
		}
	}

	/**
     * Improve App personal debug printing on front-end
     * with a custom message notice box
     * @param mixed $element: type depends on data to debug
     * @param string|null $label: a marker to distinguish debug datas
     * @return string: html with debug datas
     */
    public static function renderDebug($element, $label = null) {
		return '<div class="alert alert-warning" role="alert" style="position:relative">
			<p class="h6">' . $info . '</p>
            <pre>' . print_r($element, true) . '</pre>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"style="position:absolute;top:10px;right:10px">
                <span aria-hidden="true">
                    <i class="now-ui-icons ui-1_simple-remove"></i>
                </span>
            </button>
        </div>';
	}
}