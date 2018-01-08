<?php
namespace App\Models\Home;
use App\Models\BaseModel;
use Core\Database\AppDatabase;
use Core\AppHTTPResponse;
use Core\Routing\AppRouter;
use Core\Config\AppConfig;

class HomeModel extends BaseModel
{
	/**
	 * Constructor
	 * @param AppHTTPResponse instance
	 * @param AppRouter instance
	 * @param AppConfig instance
	 */
	public function __construct(AppHTTPResponse $httpResponse, AppRouter $router, AppConfig $config)
	{
		parent::__construct(AppDatabase::getInstance(), $httpResponse, $router, $config);
	}

	public function insertContact($contactEntity) 
	{
		$query = $this->dbConnector->prepare('INSERT INTO contacts 
											  (contact_sendingDate, contact_familyName, contact_firstName, contact_email, contact_message) 
											  VALUES (NOW(), ?, ?, ?, ?)');
		$query->bindParam(1, $familyName);
		$query->bindParam(2, $firstName);
		$query->bindParam(3, $email);
		$query->bindParam(4, $message);

		// insertion
		$familyName = $contactEntity['familyName'];
		$firstName = $contactEntity['firstName'];
		$email = $contactEntity['email'];
		$message = $contactEntity['message'];
		$query->execute();
	}
}