<?php
namespace App\Models\Home;

use App\Models\BaseModel;
use Core\Routing\AppRouter;

/**
 * Create a Homepage model
 */
class HomeModel extends BaseModel
{
    /**
     * Constructor
     *
     * @param AppRouter $router: an instance of AppRouter
     *
     * @return void
     */
    public function __construct(AppRouter $router)
    {
        parent::__construct($router);
    }

    /**
     * Insert a Contact entity in database
     *
     * @param array $contactDatas: an array of contact message datas
     *
     * @return void
     */
    public function insertContact($contactDatas)
    {
        // Secure query
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
