<?php
namespace Core\Database;

class PDOFactory
{
    public static function getMySQLConnexion($dbHost, $dbName, $dbUser, $dbPwd)
    {
      $db = new \PDO('mysql:host=' . $dbHost . ';dbname=' . $dbName, $dbUser, $dbPwd, array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
      $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
      return $db; 
    }

    public static function getPostgreSQLConnexion()
    {
    	//Do stuff here
    }
}