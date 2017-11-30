<?php
namespace Core\Config;
use Core\AppHTTPResponse;

// Symfony Yaml parser Component
require_once __DIR__ . '/../../Libs/vendor/autoload.php';
use Symfony\Component\Yaml\Yaml;

class AppConfig
{
	private static $_instance;
	private $httpResponse;

	// TODO : Create config.yml file to manage configuration
	public const APP_DEBUG = true; // Debug mode to show details of exception for instance
	public const POST_PER_PAGE = 1; // Post Quantity to show per page

	public const DB_HOST = 'localhost';
	public const DB_NAME = 'phpblog';
	public const DB_USER = 'root';
	public const DB_PWD = '';

	public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new AppConfig();
        }
        return self::$_instance;
    }

	private function __construct()
	{
		$this->httpResponse = new AppHTTPResponse;
	}

	protected function __clone()
	{ 
	}

	// debug
	public static function isDebug($string)
	{
		//var_dump('isDebug', 'APP_DEBUG', self::APP_DEBUG);
		if(!self::APP_DEBUG) {
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
}