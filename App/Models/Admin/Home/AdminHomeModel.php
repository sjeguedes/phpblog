<?php
namespace App\Models\Admin\Home;

use App\Models\Admin\AdminModel;
use Core\Routing\AppRouter;
use App\Models\Admin\Entity\Contact;
use App\Models\Admin\Entity\User;

/**
 * Create an admin model for admin homepage
 */
class AdminHomeModel extends AdminModel
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
     * Get a single contact with its id
     *
     * @param string $contactId
     *
     * @return object|boolean: a Contact entity instance or false
     */
    public function getContactById($contactId)
    {
        $query = $this->dbConnector->prepare('SELECT *
                                              FROM contacts
                                              WHERE contact_id = :contactId');
        $query->bindParam(':contactId', $contactId, \PDO::PARAM_INT);
        $query->execute();
        $datas = $query->fetch(\PDO::FETCH_ASSOC);
        // Is there a result?
        if ($datas != false) {
            $contact = new Contact($datas);
            return $contact;
        } else {
            return false;
        }
    }

    /**
     * Get a single user with its id
     *
     * @param string $userId
     *
     * @return object|boolean: a User entity instance or false
     */
    public function getUserById($userId)
    {
        $query = $this->dbConnector->prepare('SELECT *
                                              FROM users
                                              WHERE user_id = :userId');
        $query->bindParam(':userId', $userId, \PDO::PARAM_INT);
        $query->execute();
        $datas = $query->fetch(\PDO::FETCH_ASSOC);
        // Is there a result?
        if ($datas != false) {
            $user = new User($datas);
            return $user;
        } else {
            return false;
        }
    }

    /**
     * Get Post entities ordered by creation date with their author data
     * Use an external model: PostModel
     *
     * @param boolean $published: true (only published post) or false
     *
     * @return array: an array of Post entities instances with author data
     */
    public function getPostListWithAuthor($published = false)
    {
        return $this->externalModels['postModel']->getListWithAuthor($published); // param "false" means all posts (with all states)
    }

    /**
     * Get Contact entities ordered by sending date
     *
     * @return array: an array of Contact entities instances
     */
    public function getContactList()
    {
        $contacts = [];
        $query = $this->dbConnector->query('SELECT *
                                            FROM contacts
                                            ORDER BY contact_sendingDate
                                            DESC');
        while ($datas = $query->fetch(\PDO::FETCH_ASSOC)) {
            $contacts[] = new Contact($datas);
        }
        return $contacts;
    }

    /**
     * Get User entities ordered by creation date
     *
     * @return array: an array of User entities instances
     */
    public function getUserList()
    {
        $users = [];
        $query = $this->dbConnector->query('SELECT *
                                            FROM users
                                            ORDER BY user_creationDate
                                            DESC');
        while ($datas = $query->fetch(\PDO::FETCH_ASSOC)) {
            $users[] = new User($datas);
        }
        return $users;
    }

    /**
     * Get all Image entities ordered by creation date
     *
     * @return array: an array which contains all Image entities instances
     */
    public function getImageList()
    {
        return $this->externalModels['postModel']->getImageList();
    }

    /**
     * Get User entity user type label by id
     *
     * @param integer $userTypeId: user type id (corresponds to "administrator", "member", ...)
     *
     * @return array: an array which contains user type label (administrator, member...) or false
     */
    public function getUserTypeLabelById($userTypeId)
    {
        $query = $this->dbConnector->prepare('SELECT ut.userType_label
                                              FROM users u
                                              INNER JOIN userTypes ut ON (u.user_userTypeId = ut.userType_id)
                                              WHERE u.user_id = :userTypeId');
        $query->bindParam(':userTypeId', $userTypeId, \PDO::PARAM_INT);
        $query->execute();
        $data = $query->fetch(\PDO::FETCH_ASSOC);
        if ($data != false) {
            return $data;
        } else {
            return false;
        }
    }
}
