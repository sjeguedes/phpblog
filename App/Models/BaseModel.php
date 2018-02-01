<?php
namespace App\Models;
use Core\Database\AppDatabase;
use Core\Database\PDOFactory;
use Core\Config\AppConfig;

/**
 * Create a parent model to use in each model
 */
abstract class BaseModel
{
    /**
     * @var AppDatabase instance
     */
    protected $dbConnector;
    /**
     * @var AppConfig instance
     */
    protected $config;

    /**
     * Constructor
     * @param AppDatabase $dbConnector: an instance of AppDatabase
     * @param AppConfig $config: an instance of AppConfig
     * @return void
     */
    public function __construct(AppDatabase $dbConnector, AppConfig $config)
    {
        $this->dbConnector = $dbConnector::getPDOWithMySQl();
        $this->config = $config;
    }

    // Common queries

    /**
     * Check if a row id exists in a particular table
     * @param string $table: table name
     * @param string $columnPrefix: prefix column name
     * @param string $id: primary key
     * @return boolean
     */
    public function checkRowId($table, $columnPrefix, $id)
    {
        $query = $this->dbConnector->prepare("SELECT ${columnPrefix}_id
                                              FROM $table
                                              WHERE ${columnPrefix}_id = ?
                                              LIMIT 1");
        $query->bindParam(1, $id, \PDO::PARAM_INT);
        $query->execute();

        // Count result
        $numRows = $query->rowCount(); // this PDO method equals mysql_num_rows($query).
        if ($numRows > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Select all datas in a particular database table
     * @param string $table: table name
     * @return array: an array of datas from the database
     */
    public function selectAll($table)
    {
        $query = $this->dbConnector->query('SELECT *
                                            FROM ' . $table);
        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }
}
