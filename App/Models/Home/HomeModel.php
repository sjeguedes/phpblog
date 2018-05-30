<?php
namespace App\Models\Home;
use App\Models\BaseModel;
use Core\Database\AppDatabase;
use Core\Config\AppConfig;

/**
 * Create a Homepage model
 */
class HomeModel extends BaseModel
{
	/**
	 * Constructor
	 * @param AppConfig $config: an instance of AppConfig
	 * @return void
	 */
	public function __construct(AppConfig $config)
	{
		parent::__construct(AppDatabase::getInstance(), $config);
	}

	/**
	 * Insert a contact entity in database
	 * @param array $contactDatas: an array of contact message datas
	 * @return void
	 */
	public function insertContact($contactDatas)
	{
		$query = $this->dbConnector->prepare('INSERT INTO contacts
											  (contact_sendingDate, contact_familyName, contact_firstName, contact_email, contact_message)
											  VALUES (NOW(), ?, ?, ?, ?)');
		$query->bindParam(1, $familyName);
		$query->bindParam(2, $firstName);
		$query->bindParam(3, $email);
		$query->bindParam(4, $message);

		// Insertion
		$familyName = $contactDatas['cf_familyName'];
		$firstName = $contactDatas['cf_firstName'];
		$email = $contactDatas['cf_email'];
		$message = $contactDatas['cf_message'];
		$query->execute();
	}
}