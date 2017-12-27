<?php
namespace App\Models;
use Core\Database\AppDatabase;
use Core\Database\PDOFactory;
use Core\AppHTTPResponse;
use Core\Routing\AppRouter;
use Core\Config\AppConfig;

abstract class BaseModel 
{
	protected $dbConnector;
	protected $httpResponse;
	protected $router;
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

	// Helper: insert escaped datas in database

	public function avoidSQLInjection() {
		if(ctype_digit($string)) { // string is an int
			$string = intval($string);
		}
		else { // other types: avoid SQL injection
			$string = mysql_real_escape_string($string);
			$string = addcslashes($string, '%_');
		}
		return $string;
	}

	// Helper: show escaped datas on website

	public function avoidXSS($string)
	{ 
		return nl2br(htmlentities($string), ENT_QUOTES); // avoid XSS on rich text or tag attribute
	}

	public function escapeHTML($string)
	{ 
		return trim(strip_tags($string)); // simple text
	}

	public function escapeTagAttribute($string)
	{ 
		return addslashes(trim(strip_tags($string))); // clean tag attribute
	}

	// Common queries

	public function selectAll($table)
	{
		if($this->dbConnector !== null) {
			$query = $this->dbConnector->query('SELECT * FROM ' . $table);
			return $query->fetchAll(\PDO::FETCH_ASSOC);
		}
	}
}