<?php
namespace App\Models\Home;
use App\Models\BaseModel;
use Core\Database\AppDatabase;
use Core\AppHTTPResponse;
use Core\Routing\AppRouter;
use Core\Config\AppConfig;

/**
 * Create a Homepage model
 */
class HomeModel extends BaseModel
{
	/**
	 * Constructor
	 * @param AppHTTPResponse instance
	 * @param AppRouter instance
	 * @param AppConfig instance
	 * @return void
	 */
	public function __construct(AppHTTPResponse $httpResponse, AppRouter $router, AppConfig $config)
	{
		parent::__construct(AppDatabase::getInstance(), $httpResponse, $router, $config);
	}

	/**
	 * Insert a contact entity in database
	 * @param object $contactEntity: an instance of Contact entity object 
	 * @return void
	 */
	public function insertContact($contactEntity) 
	{
		$query = $this->dbConnector->prepare('INSERT INTO contacts 
											  (contact_sendingDate, contact_familyName, contact_firstName, contact_email, contact_message) 
											  VALUES (NOW(), ?, ?, ?, ?)');
		$query->bindParam(1, $familyName);
		$query->bindParam(2, $firstName);
		$query->bindParam(3, $email);
		$query->bindParam(4, $message);

		// Insertion
		$familyName = $contactEntity['cf_familyName'];
		$firstName = $contactEntity['cf_firstName'];
		$email = $contactEntity['cf_email'];
		$message = $contactEntity['cf_message'];
		$query->execute();
	}
}