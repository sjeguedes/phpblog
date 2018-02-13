<?php
namespace App\Models\Admin;
use App\Models\BaseModel;
use App\Models\Blog\Post\PostModel;
use Core\Database\AppDatabase;
use Core\Config\AppConfig;
use App\Models\Blog\Entity\User;

/**
 * Create a parent class for admin models
 */
abstract class AdminModel extends BaseModel
{
    /**
     * @var array: an array of models used by admin classes
     */
    protected $externaldModels;

    /**
     * Constructor
     * @param AppConfig $config: an instance of AppConfig
     * @return void
     */
    public function __construct(AppConfig $config)
    {
        parent::__construct(AppDatabase::getInstance(), $config);
        // Store an instance of PostModel
        $this->externaldModels['postModel'] = new PostModel($config);
    }

    /**
     * Get User entity thanks to his id
     * @param string $userId
     * @return object: a User entity instance
     */
    public function getUserById($userId)
    {
        $query = $this->dbConnector->query('SELECT * FROM users
                                            WHERE user_id = ' . $userId);
        $datas = $query->fetch(\PDO::FETCH_ASSOC);
        return new User($datas);
    }

    /**
     * Generate activation code for new user to validate his account
     * @param string $UserId
     * @param string $UserNickName
     * @see http://php.net/manual/fr/function.hash.php
     * @return string: a part of long hash
     */
    private function generateUserActivationCode($UserId, $UserNickName)
    {
        $salt = substr(md5(microtime()), rand(0, 5), rand(5, 10));
        $activationCode = substr(hash('sha256', $UserId . $salt . $UserNickName), 0, 45);
        return $activationCode;
    }

    /**
     * Generate encrypted password for user password
     * @param string $password
     * @see http://php.net/manual/fr/function.password-hash.php
     * @return string: an encrypted password
     */
    private function generateUserPasswordEncryption($password)
    {
        $options = [
            'cost' => 8,
        ];
        $encryptedPassword = password_hash($password, PASSWORD_BCRYPT, $options);
        return $encryptedPassword;
    }

    /**
     * Delete an entity in database with its id
     * @param string $entityId: entity id
     * @param array $datas: an array which contains only entity type
     * to delete entity
     * @return void
     */
    public function deleteEntity($entityId, $datas)
    {
        $table = $datas['entity'] . 's';
        $columnPrefix = $datas['entity'] . '_';
        $query = $this->dbConnector->prepare("DELETE
                                              FROM $table
                                              WHERE ${columnPrefix}id = ?");
        $query->bindParam(1, $entityId, \PDO::PARAM_INT);
        $query->execute();
    }

    /**
     * Update an entity in database with its id
     * @param string $entityId: entity id
     * @param array $newDatas: an array of parameters (entity type and values)
     * to update entity
     * @return void
     */
    public function updateEntity($entityId, $newDatas)
    {
        // Declare type to secure (add other params in array if necessary)
        $PDOParams = [\PDO::PARAM_INT];
        $table = $newDatas['entity'] . 's';
        $columnPrefix = $newDatas['entity'] . '_';
        $set = '';
        $newValues = $newDatas['values'];
        for ($i = 0; $i < count($newValues); $i ++) {
            if ($i == 0) {
                $set .= $columnPrefix . $newValues[$i]['column'] . '=' .  $newValues[$i]['value'];
            } else {
                $set .= ', ' . $columnPrefix . $newValues[$i]['column'] . '=' .  $newValues[$i]['value'];
            }
        }

        $query = $this->dbConnector->prepare("UPDATE $table
                                              SET $set
                                              WHERE ${columnPrefix}id = ?");
        $query->bindParam(1, $entityId, \PDO::PARAM_INT);
        $query->execute();
    }
}