<?php
namespace Core\Database;

/**
 * Create a PDO factory
 */
class PDOFactory
{
    /**
     * Establish a MySQL connection with PDO instance
     * This method is used in App!
     * @param string $dbHost
     * @param string $dbName
     * @param string $dbUser
     * @param string $dbPwd
     * @return PDO: an instance of PDO connector
     */
    public static function getMySQLConnection($dbHost, $dbName, $dbUser, $dbPwd)
    {
        $db = new \PDO('mysql:host=' . $dbHost . ';dbname=' . $dbName, $dbUser, $dbPwd, array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $db;
    }

    /**
     * Establish a PostgreSQL connection with PDO instance
     * Example to show factory utility
     * @param string $dbHost
     * @param string $dbName
     * @param string $dbUser
     * @param string $dbPwd
     * @return PDO: an instance of PDO connector
     */
    public static function getPostgreSQLConnection($dbHost, $dbName, $dbUser, $dbPwd)
    {
    	$db = new \PDO('pgsql:host=' . $dbHost . ';dbname=' . $dbName, $dbUser, $dbPwd, array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $db;
    }
}