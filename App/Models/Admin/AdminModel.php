<?php
namespace App\Models\Admin;
use App\Models\BaseModel;
use Core\Database\AppDatabase;
use Core\Config\AppConfig;
use App\Models\Blog\Entity\User;

/**
 * Create a parent class for admin models
 */
abstract class AdminModel extends BaseModel
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
}
