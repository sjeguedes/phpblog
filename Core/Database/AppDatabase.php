<?php
namespace Core\Database;
use Core\Database\PDOFactory;
use Core\Config\AppConfig;
use Core\AppHTTPResponse;

/**
 * Manage a database connection with a PDOFactory
 */
class AppDatabase
{
	/**
     * @var object: a unique instance of AppDatabase
     */
    private static $_instance;
    /**
     * @var object: an instance of AppConfig
     */
	private static $_config;
    /**
     * @var object: an instance of AppHTTPResponse
     */
    private static $_httpResponse;

	/**
     * Instanciate a unique AppDatabase object (Singleton)
     * @return AppDatabase: a unique instance of AppDatabase
     */
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new AppDatabase();
        }
        return self::$_instance;
    }

	/**
     * Constructor
     * @return void
     */
    private function __construct()
	{
		self::$_config = AppConfig::getInstance();
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
     * Get a MySQL connection with PDO
     * @return PDO: an instance of PDO with MySQL connector
     */
    public static function getPDOWithMySQl()
	{
		return PDOFactory::getMySQLConnection(self::$_config::getParam('database.dbHost'), self::$_config::getParam('database.dbName'), self::$_config::getParam('database.dbUser'), self::$_config::getParam('database.dbPwd)'));
	}

	/**
     * Get a PostgreSQL connection with PDO
     * @return PDO: an instance of PDO with PostgreSQL connector
     */
    public static function getPDOWithPostgreSQL()
    {
        return PDOFactory::getPostgreSQLConnection(self::$_config::getParam('database.dbHost'), self::$_config::getParam('database.dbName'), self::$_config::getParam('database.dbUser'), self::$_config::getParam('database.dbPwd)'));
    }
}