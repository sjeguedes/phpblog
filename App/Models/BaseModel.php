<?php
namespace App\Models;
use Core\Database\AppDatabase;
use Core\Database\PDOFactory;
use Core\AppHTTPResponse;
use Core\Routing\AppRouter;
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
	 * @var AppHTTPResponse instance
	 */
	protected $httpResponse;
	/**
	 * @var AppRouter instance
	 */
	protected $router;
	/**
	 * @var AppConfig instance
	 */
	protected $config;

	/**
	 * Constructor
	 * @param AppDatabase instance
	 * @param AppHTTPResponse instance
	 * @param AppRouter instance
	 * @param AppConfig instance
	 */
	public function __construct(AppDatabase $dbConnector, AppHTTPResponse $httpResponse, AppRouter $router, AppConfig $config)
	{	
		$this->dbConnector = $dbConnector::getPDOWithMySQl();
		$this->httpResponse = $httpResponse;
		$this->router = $router;
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