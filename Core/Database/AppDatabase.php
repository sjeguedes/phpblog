<?php
namespace Core\Database;

use Core\Routing\AppRouter;
use Core\Database\PDOFactory;

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
     * @var AppRouter instance
     */
    private static $_router;
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
     *
     * @param object: an AppRouter instance
     *
     * @return AppDatabase: a unique instance of AppDatabase
     */
    public static function getInstance(AppRouter $router)
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new AppDatabase($router);
        }
        return self::$_instance;
    }

    /**
     * Constructor
     *
     * @param object: an AppRouter instance
     *
     * @return void
     */
    private function __construct(AppRouter $router)
    {
        // Set AppRouter instance
        self::$_router = $router;
        // Set AppHTTPResponse instance used router!
        self::$_httpResponse = self::$_router->getHTTPResponse();
        // Set AppConfig instance used by router!
        self::$_config = self::$_router->getConfig();
    }

    /**
    * Magic method __clone
    *
    * @return void
    */
    public function __clone()
    {
        self::$_httpResponse->set404ErrorResponse(self::$_config::isDebug('Technical error [Debug trace: Don\'t try to clone singleton ' . __CLASS__ . '!]'), self::$_router);
        exit();
    }

    /**
     * Get a MySQL connection with PDO
     *
     * @return PDO: an instance of PDO with MySQL connector
     */
    public static function getPDOWithMySQl()
    {
        return PDOFactory::getMySQLConnection(self::$_config::getParam('database.dbHost'), self::$_config::getParam('database.dbName'), self::$_config::getParam('database.dbUser'), self::$_config::getParam('database.dbPwd'));
    }

    /**
     * Get a PostgreSQL connection with PDO
     *
     * @return PDO: an instance of PDO with PostgreSQL connector
     */
    public static function getPDOWithPostgreSQL()
    {
        return PDOFactory::getPostgreSQLConnection(self::$_config::getParam('database.dbHost'), self::$_config::getParam('database.dbName'), self::$_config::getParam('database.dbUser'), self::$_config::getParam('database.dbPwd'));
    }
}
