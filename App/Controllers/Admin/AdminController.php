<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use Core\Routing\AppRouter;

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
     * @param AppRouter $router
     * @return void
     */
    public function __construct(AppRouter $router)
    {
        parent::__construct($router);
        // Session id was regenerated: so, update user tokens.
        if ($this->session::isSessionIdRegenerated()) {
            $this->updateUserTokens();
        }
        // Disallow admin access if user is not authenticated
        // or user cookie token index does not match, or user cookie/session token values don't match.
        $this->allowAdminAccess();
    }

    /**
     * Update user cookie and session tokens if session id was regenerated
     * @return boolean: true if tokens are updated, or false
     */
    private function updateUserTokens() {
        $authenticatedUser = $this->session::isUserAuthenticated();
        if ($authenticatedUser != false) {
            $newSID = session_id();
            // Set user session token value
            $_SESSION['user']['sessionId'] = $newSID;
            $updatedToken = $this->generateUserSessionTokenValue('user' . $_SESSION['user']['userId'] . '_token', $newSID);
            $_SESSION['user']['sessionToken'] = $updatedToken;
            // Set custom user session cookie value
            $this->session::setCustomCookie('UPDATEDUST' . $_SESSION['newSID']['indexSalt'], $updatedToken);
            $_SESSION['newSID']['request'] = 1;
            $_SESSION['newSID']['mustUpdateTokens'] = false;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if access to admin pages is allowed
     * @return void
     */
    private function allowAdminAccess() {
        // Check access to admin pages but not user login
        $adminPageRequest = preg_match('#^/?admin((?!/login|/request-new-password|/register)(?=.*[\d\w-/]).*)?$#', $_GET['url']);
        $authenticatedUser = $this->session::isUserAuthenticated();
        $matchedTokenValues = true;
        $matchedCookieTokenIndex = true;
        // User request an admin page
        if ($adminPageRequest) {
            // Check if user is authenticated
            if ($authenticatedUser != false) {
                !isset($_SESSION['newSID']['request']) ? $_SESSION['newSID']['request'] = 0 : $_SESSION['newSID']['request'];
                $_SESSION['newSID']['request'] >= 1 ? $_SESSION['newSID']['request'] ++ : $_SESSION['newSID']['request'];
                // Check if custom user token cookie is set.
                if (isset($_COOKIE['UPDATEDUST' . $_SESSION['newSID']['indexSalt']])) {
                    $cookieTokenValue = $_COOKIE['UPDATEDUST' . $_SESSION['newSID']['indexSalt']];
                    $sessionTokenValue = $_SESSION['user']['sessionToken'];
                    // Check identical user token $_COOKIE and $_SESSION values
                    if ($cookieTokenValue !== $sessionTokenValue) {
                        $matchedTokenValues = false;
                    }
                } else {
                    if ($_SESSION['newSID']['request'] >= 3) {
                        // Custom cookie is not set yet or does not exist!
                        foreach ($_COOKIE as $key => $value) {
                            // Similar cookie may be found
                            if (preg_match('#^UPDATEDUST((?=.\w)(?=.\d).*)$#', $key)) {
                                // Check wrong custom cookie index: this can be suspicious in case of session hijacking!
                                if ($key !== 'UPDATEDUST' . $_SESSION['newSID']['indexSalt']) {
                                    $matchedCookieTokenIndex = false;
                                }
                            }
                        }
                    }
                }
            }
            // Check several conditions:
            // User is not authenticated (not logged in or session expired),
            // or user cookie token index does not match, or user session/cookie tokens don't match:
            // So prevent admin access!
            if (!$authenticatedUser || !$matchedCookieTokenIndex || !$matchedTokenValues) {
                // Redirect to user login page if access is refused.
                $this->httpResponse->addHeader('Location: /admin/login');
                exit();
            }
        }
    }

    /**
     * Regenerate all existing form tokens
     * @return void
     */
    protected function regenerateAllFormTokens() {
        // Change token dynamic key and value for each existing token
        foreach ($_SESSION as $key => $value) {
            if (preg_match("#_check|_token#", $key)) {
                unset($_SESSION[$key]);
            }
        }
    }

    /**
     * Generate activation code (used in url GET parameter) for new user (User entity) to validate his account
     * @param string $UserEmail: user email address
     * @see http://php.net/manual/fr/function.hash.php
     * @return string: a part of long hash
     */
    protected function generateUserActivationCode($UserEmail)
    {
        $salt = substr(md5(microtime()), rand(0, 5), rand(5, 10));
        $activationCode = substr(hash('sha256', $salt . $UserEmail), 0, 45);
        return $activationCode;
    }

    /**
     * Generate password renewal authentication code (used in url GET parameter) for user (User entity)
     * to be recognized on website and be able to change his forgotten password
     * @param string $UserEmail: user email address
     * @see http://php.net/manual/fr/function.hash.php
     * @return string: a part of long hash
     */
    protected function generateUserPasswordUpdateCode($UserEmail)
    {
        $passwordAuthenticationCode = $this->generateUserActivationCode($UserEmail);
        return $passwordAuthenticationCode;
    }

    /**
     * Generate password renewal update token for user (User entity)
     * to update (change) his forgotten password with form
     * @param string $UserEmail: user email address
     * @see http://php.net/manual/fr/function.hash.php
     * @return string: a part of long hash
     */
    protected function generateUserPasswordUpdateToken($UserEmail)
    {
        $salt = substr(md5(microtime()), rand(0, 5), rand(5, 10));
        $passwordUpdateToken = strtoupper(substr(hash('sha256', $salt . $UserEmail), 0, 15));
        return $passwordUpdateToken;
    }

    /**
     * Generate encrypted password for user password
     * @param string $password
     * @see http://php.net/manual/fr/function.password-hash.php
     * @return string: an encrypted password
     */
    protected function generateUserPasswordEncryption($passwordString)
    {
        $options = [
            'cost' => 8,
        ];
        $encryptedPassword = password_hash($passwordString, PASSWORD_BCRYPT, $options);
        return $encryptedPassword;
    }

    /**
     * Check a password string against an existing password hash in database
     * @param string $passwordString: password which comes from user input
     * @param string $passwordHash: password hash which belongs to a User entity in database
     * @return boolean: true if password matches, or false
     */
    protected function verifyUserPassword($passwordString, $passwordHash)
    {
        // Compare password string which comes from user input with User entity password hash in database
        $isPassword = password_verify($passwordString, $passwordHash);
        if ($isPassword) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Create a session token value for a user, to fight against session hijacking)
     * @param string $varName: name which corresponds to token index
     * @param string: $sessionId: session id (may be regenerated)
     * @return string: hash value
     */
    protected function generateUserSessionTokenValue($varName, $sessionId = null)
    {
        is_null($sessionId) ? session_id() : $sessionId;
        $hash = hash('sha256', $varName . bin2hex(openssl_random_pseudo_bytes(8)) . $sessionId);
        return $hash;
    }

    /**
     * Check if user generated session token matches with token in $_COOKIE value
     * @param string $cookieToken: token value stored in $_COOKIE
     * @param string $sessionToken: token value stored in $_SESSION
     * @return boolean
     */
    protected function checkUserSessionTokenValue($cookieToken, $sessionToken)
    {
        return $cookieToken === $sessionToken;
    }
}