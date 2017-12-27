<?php
namespace App\Models\Admin;
use App\Models\BaseModel;
use Core\Database\AppDatabase;
use Core\AppHTTPResponse;
use Core\Routing\AppRouter;
use Core\Config\AppConfig;
use App\Models\Blog\Entity\User;

abstract class AdminModel extends BaseModel
{
	/**
	 * Constructor
	 * @param AppHTTPResponse instance
	 * @param AppRouter instance
	 * @param AppConfig instance
	 */
	public function __construct(AppHTTPResponse $httpResponse, AppRouter $router, AppConfig $config)
	{
		parent::__construct(AppDatabase::getInstance(), $httpResponse, $router, $config);
	}

	public function getUserById($userId)
	{
	    $query = $this->dbConnector->query('SELECT * FROM users
	    									WHERE user_id = ' . $userId);
	    $datas = $query->fetch(\PDO::FETCH_ASSOC);
	    return new User($datas);
	}

	public function generateUserActivationCode($UserId, $UserPseudo)
	{
			// Do stuff! -> use for instance hash('sha256' , string: user_id + user_name )
			// http://php.net/manual/fr/function.hash.php
			$salt = substr(md5(microtime()), rand(0, 5), rand(5, 10));
			$activationCode = substr(hash('sha256', $UserId . $salt . $UserPseudo), 0, 45);
			return $activationCode;
			
	}

	public function generateUserPasswordEncryption($password)
	{
			// Do stuff! -> use password_hash( string $password chosen , integer $algo [, array $options ] )
			// http://php.net/manual/fr/function.password-hash.php
			$options = [
			    'cost' => 8,
			];
			$encryptedPassword = password_hash($password, PASSWORD_BCRYPT, $options);
			return $encryptedPassword;
	}
}