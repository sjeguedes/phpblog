<?php
namespace App\Models\Admin;
use App\Models\BaseModel;
use Core\Database\AppDatabase;
use Core\AppHTTPResponse;
use Core\Routing\AppRouter;
use Core\Config\AppConfig;
use App\Models\Admin\Entity\Contact;
use App\Models\Blog\Entity\User;

/**
 * Create an admin Homepage model
 */
class AdminHomeModel extends AdminModel
{
	/**
	 * Constructor
	 * @param AppHTTPResponse instance
	 * @param AppRouter instance
	 * @param AppConfig instance
	 */
	public function __construct(AppHTTPResponse $httpResponse, AppRouter $router, AppConfig $config)
	{
		parent::__construct($httpResponse, $router, $config);
	}

	/**
	 * Get Contact entities ordered by sending date
	 * @return array: an array of objects
	 */
	public function getContactList()
	{
		$contacts = [];
    	$query = $this->dbConnector->query('SELECT *
    										FROM contacts	 
    										ORDER BY contact_sendingDate DESC');
	    while ($datas = $query->fetch(\PDO::FETCH_ASSOC)) {
	      	$contacts[] = new Contact($datas);
	    }
    	return $contacts;
    }
}