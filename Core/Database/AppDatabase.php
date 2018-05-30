<?php
namespace Core\Database;
use Core\Database\PDOFactory;
use Core\Config\AppConfig;

class AppDatabase
{
	private static $_instance;
	private static $_config;

	public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new AppDatabase();
        }
        return self::$_instance;
    }

	private function __construct()
	{
		self::$_config = AppConfig::getInstance();
	}

	protected function __clone()
	{ 
	}

	public static function getPDOWithMySQl()
	{
		return PDOFactory::getMySQLConnexion(self::$_config::$_dbHost, self::$_config::$_dbName, self::$_config::$_dbUser, self::$_config::$_dbPwd);
	}

	public static function getDatabase($database)
	{
		return $database;
	}
}