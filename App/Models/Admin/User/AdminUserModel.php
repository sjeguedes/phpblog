<?php
namespace App\Models\Admin\User;
use App\Models\Admin\AdminModel;
use Core\Config\AppConfig;
use App\Models\Admin\Entity\User;

/**
 * Create an admin user model for his actions
 */
class AdminUserModel extends AdminModel
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
     * Get User entity with its id
     * @param string $userId
     * @return object: a User entity instance
     */
    public function getUserById($userId)
    {
        $query = $this->dbConnector->prepare('SELECT *
                                            FROM users
                                            WHERE user_id = :id');
        $query->bindParam(':id', $userId, \PDO::PARAM_INT);
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
     * Get User entity with its email (1 account equals 1 email)
     * This value comes from form user input.
     * @param string $userEmail
     * @return object|boolean: a User entity instance or false
     */
    public function getUserByEmail($userEmail)
    {
        $query = $this->dbConnector->prepare('SELECT *
                                              FROM users
                                              WHERE user_email = :email');
        $query->bindParam(':email', $userEmail, \PDO::PARAM_STR);
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
     * Get all User entities
     * @return array: an array which contains all User entities instances
     */
    public function getUserList()
    {
        $users = [];
        $query = $this->dbConnector->query('SELECT *
                                            FROM users');
        while($datas = $query->fetch(\PDO::FETCH_ASSOC)) {
            $users[] = new User($datas);
        }
        return $users;
    }
}