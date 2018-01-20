<?php
namespace App\Models\Admin;
use App\Models\BaseModel;
use Core\Config\AppConfig;
use App\Models\Admin\Entity\Contact;
use App\Models\Blog\Entity\User;

/**
 * Create an admin model for admin homepage
 */
class AdminHomeModel extends AdminModel
{
    /**
     * Constructor
     * @param AppConfig $config: an instance of AppConfig
     * @return void
     */
    public function __construct(AppConfig $config)
    {
        parent::__construct($config);
    }

    /**
     * Get Contact entities ordered by sending date
     * @return array: an array of Contact entities objects
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
