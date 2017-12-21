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
		return PDOFactory::getMySQLConnexion(self::$_config::DB_HOST, self::$_config::DB_NAME, self::$_config::DB_USER, self::$_config::DB_PWD);
	}

	public static function getDatabase($database)
	{
		return $database;
	}

}



		// $dbConnection = false;
		// try {
		// 	$this->dbConnector = $dbConnector::getDatabase(PDOFactory::getMysqlConnexion());
		// 	//var_dump($this->dbConnector);
		// 	$dbConnection = true;
		// }
	 //    catch(\RuntimeException $e) {
	 //    	die($httpResponse->set404ErrorResponse($e->getMessage(), $router));
	 //    }

	 //    if($dbConnection === false) {
	 //    	//Database connection issue
	 //    	throw new \RuntimeException('Technical error [Debug trace: database connection failed!]');
	 //    }