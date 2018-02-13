<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use Core\AppPage;
use Core\AppHTTPResponse;
use Core\Routing\AppRouter;
use Core\Config\AppConfig;

/**
 * Create a parent controller for common actions in back-end
 * This class extends BaseController parent controller for all controllers.
 */
class AdminController extends BaseController
{
    /**
     * @var string: value stored in $_SESSION to manage user admin session
     */
    protected $userToken;

    /**
     * Constructor
     * @param AppPage $page
     * @param AppHTTPResponse $httpResponse
     * @param AppRouter $router
     * @param AppConfig $config
     * @return void
     */
    public function __construct(AppPage $page, AppHTTPResponse $httpResponse, AppRouter $router, AppConfig $config)
    {
        parent::__construct($page, $httpResponse, $router, $config);
    }

    /**
     * Create a token value for a user, to fight against CSRF (XSRF)
     * @param string $varName: name which corresponds to token index
     * @return string: value stored in $_SESSION
     */
    protected function generateUserTokenValue($varName)
    {
        if (!isset($_SESSION[$varName])) {
            $_SESSION[$varName] = hash('sha256', $varName . bin2hex(openssl_random_pseudo_bytes(8)) . session_id());
        }
        return $_SESSION[$varName];
    }

    /**
     * Check if user created token matches with token in $_POST value
     * @param string $token: $_POST value
     * @param string $varName: name which corresponds to token index in $_SESSION
     * @return boolean
     */
    protected function checkUserTokenValue($token, $varName)
    {
        return $token === $this->generateUserTokenValue($varName);
    }
}