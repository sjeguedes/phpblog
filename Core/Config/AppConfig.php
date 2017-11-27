<?php
namespace Core\Config;

class AppConfig
{
	private static $_instance;

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
	}

	protected function __clone()
	{ 
	}

	// debug
	public static function isDebug($string) {
		//var_dump('isDebug', 'APP_DEBUG', self::APP_DEBUG);
		if(!self::APP_DEBUG) {
			$string = preg_replace('/\[Debug trace:.*\]$/', '', $string);
		}
		return $string;
	}
}