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
     * Select all datas in a particular database table
     * @param string $table: table name
     * @return null|array: an array of datas from the database
     */
    public function selectAll($table)
    {
        if ($this->dbConnector !== null) {
            $query = $this->dbConnector->query('SELECT * FROM ' . $table);
            return $query->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            return null;
        }
    }
}
