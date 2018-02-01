<?php
namespace Core\Config;
use Core\AppHTTPResponse;
// Symfony Yaml parser Component
use Symfony\Component\Yaml\Yaml;

class AppConfig
{
	private static $_instance;

	public static $_params; // Stores config params
	public static $_appDebug; // Debug mode to show details of exception for instance
	public static $_postPerPage; // Post Quantity to show per page
	public static $_dbHost;
	public static $_dbName;
	public static $_dbUser;
	public static $_dbPwd;

	private $httpResponse;

	public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new AppConfig();
        }
        return self::$_instance;
    }

	private function __construct()
	{
		self::$_params = $this->getConfigParams();
		self::$_appDebug = self::$_params['appDebug'];
		self::$_postPerPage = self::$_params['posts']['postPerPage'];
		self::$_dbHost = self::$_params['database']['dbHost'];
		self::$_dbName = self::$_params['database']['dbName'];
		self::$_dbUser = self::$_params['database']['dbUser'];
		self::$_dbPwd = self::$_params['database']['dbPwd'];
		$this->httpResponse = new AppHTTPResponse();
	}

	protected function __clone()
	{
	}

	private function getConfigParams()
	{
		// Get params from yaml file
		$yaml = self::parseYAMLFile(__DIR__ . '/config.yml');
		return $yaml['config'];
	}

	// Debug
	public static function isDebug($string)
	{
		if(!self::$_appDebug) {
			$string = preg_replace('/\[Debug trace:.*\]$/', '', $string);
		}
		return $string;
	}

	public static function parseYAMLFile($filePath)
	{
		try {
    		$fileDatas = Yaml::parse(file_get_contents($filePath));
		}
		catch(ParseException $e) {
			// show error view
    		$errorMessage = printf("Unable to parse the YAML string - %s", $e->getMessage());
    		$this->httpResponse->set404ErrorResponse(self::isDebug('Technical error [Debug trace: ' . $errorMessage . ']'));
		}
		return $fileDatas;
	}

	public static function dumpYAMLFile($filePath, $array)
	{
		try {
    		$yaml = Yaml::dump($array);
    		file_put_contents($filePath, $yaml);
		}
		catch(DumpException $e) {
			// show error view
    		$errorMessage = printf("Unable to dump datas - %s", $e->getMessage());
    		$this->httpResponse->set404ErrorResponse(self::isDebug('Technical error [Debug trace: ' . $errorMessage . ']'));
		}
	}

	public static function renderDebug($element, $info = null) {
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