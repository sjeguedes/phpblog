<?php
namespace App\Models\Admin\User;
use App\Models\Admin\AdminModel;
use Core\Routing\AppRouter;
use App\Models\Admin\Entity\User;

/**
 * Create an admin user model for his actions
 */
class AdminUserModel extends AdminModel
{
    /**
     * Constructor
     * @param AppRouter $router: an instance of AppRouter
     * @return void
     */
    public function __construct(AppRouter $router)
    {
        parent::__construct($router);
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

    /**
     * Insert a User entity in database
     * @param array $userDatas: an array of user datas
     * @return void
     */
    public function insertUser($userDatas)
    {
        // Secure query
        $query = $this->dbConnector->prepare('INSERT INTO users
                                              (user_creationDate, user_familyName, user_firstName, user_nickName, user_email, user_password, user_activationCode,   user_activationDate, user_isActivated, user_userTypeId)
                                              VALUES (NOW(), :familyName, :firstName, :nickName, :email, :password, :activationCode, :activationDate, :isActivated, :userTypeId)');
        $query->bindParam(':familyName', $familyName, \PDO::PARAM_STR);
        $query->bindParam(':firstName', $firstName, \PDO::PARAM_STR);
        $query->bindParam(':nickName', $nickName, \PDO::PARAM_STR);
        $query->bindParam(':email', $email, \PDO::PARAM_STR);
        $query->bindParam(':password', $password, \PDO::PARAM_STR);
        $query->bindParam(':activationCode', $activationCode, \PDO::PARAM_STR);
        $query->bindParam(':activationDate', $activationDate, \PDO::PARAM_STR);
        $query->bindParam(':isActivated', $isActivated, \PDO::PARAM_INT);
        $query->bindParam(':userTypeId', $userTypeId, \PDO::PARAM_INT);
        // Insertion
        $familyName = $userDatas['ref_familyName'];
        $firstName = $userDatas['ref_firstName'];
        $nickName = $userDatas['ref_nickName'];
        $email = $userDatas['ref_email'];
        $password = $userDatas['ref_password'];
        $activationCode = $userDatas['ref_activationCode'];
        $activationDate = ''; // no activation at this level
        $isActivated = 0; // false
        $userTypeId = 2; // type 1: admin, type 2: member (default)
        $query->execute();
    }
}