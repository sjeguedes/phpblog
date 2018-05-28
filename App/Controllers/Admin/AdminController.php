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
        $this->controlAdminAccess();
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
    protected function controlAdminAccess() {
        // Check access to admin pages but not for user login, user request new password, user renew password, user register pages
        $adminPageRequest = preg_match('#^/?admin((?!/login|/request-new-password|/renew-password|/register)(?=.*[\d\w-/]).*)?$#', $_GET['url']);
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
                        foreach (array_keys($_COOKIE) as $key) {
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
     * Validate (or not) admin actions forms on entity (deleting, validation, publication, publication cancelation)
     * @param array $params: an array of parameter to validate a simple form
     * @param object $formValidatorObject: an instance of form validator
     * @param string $redirectUrl: url to redirect after validation success
     * @return array: an array which contains result of validation (errors, form values, ...)
     */
    protected function validateEntityForms($params, $formValidatorObject, $redirectUrl = null)
    {
        // Use value as var name
        $tokenIndex = $params['tokenIndex'];
        $entityIdIndex = $params['tokenIdentifier'] . '_id';
        $entity = $params['datas']['entity'];
        $entityName = ucfirst($params['datas']['entity']);
        $getEntityByid = "get${entityName}ById";
        $action = $params['action'] . 'Entity';
        $arguments = [$_POST[$entityIdIndex], $params['datas']];
        // Check token to avoid CSRF
        $tokenValue = isset($_POST[$tokenIndex]) ? $_POST[$tokenIndex] : false;
        $tokenPrefix = $params['tokenIdentifier'] . '_';
        // Form validator actions
        $formIdentifier = $formValidatorObject->getFormIdentifier();
        $formValidatorObject->validateToken($tokenValue, $tokenPrefix);
        // Get validation result
        $result = $formValidatorObject->getResult();
        // Additional error message in case of form errors
        if (!empty($result[$formIdentifier . 'errors'])) {
            // Check wrong entity id used in form
            if ((int) $_POST[$entityIdIndex] > 0 && $this->currentModel->$getEntityByid($_POST[$entityIdIndex]) == false) {
                $result[$formIdentifier . 'errors'][$formIdentifier . 'failed'][$entity]['message'] = $params['errorMessage'] . htmlentities($_POST[$entityIdIndex]) . '.';
            }
        }
        // Submit: entity form is correctly filled.
        if (isset($result) && empty($result[$formIdentifier . 'errors']) && isset($result[$formIdentifier . 'check']) && $result[$formIdentifier . 'check']) {
            // Perform desired action in database
            try {
                // Check entity id used in form
                // Is there an existing entity with this id?
                if ((int) $_POST[$entityIdIndex] > 0 && $this->currentModel->$getEntityByid($_POST[$entityIdIndex]) != false) {
                    // Delete or validate or publish or unpublish entity
                    call_user_func_array([$this->currentModel, $action], $arguments);
                    $performed = true;
                } else {
                    $result[$formIdentifier . 'errors'][$formIdentifier . 'failed'][$entity]['message2'] = $this->config::isDebug('<span class="form-check-notice">Sorry an error happened! <strong>Wrong ' . $entity . ' id</strong> is used.<br>Action [Debug trace: <strong>' . $params["action"] . '</strong>] on ' . $entity . ' can not be performed correctly.<br>[Debug trace: ' . $entity . ' id "<strong>' . htmlentities($_POST[$entityIdIndex]) . '</strong>" doesn\'t exist in database!]</span>');
                    $performed = false;
                }
            } catch (\PDOException $e) {
                $result[$formIdentifier . 'errors'][$formIdentifier . 'failed'][$entity]['message2'] = $this->config::isDebug('<span class="form-check-notice">Sorry a technical error happened! Please try again later.<br>Action [Debug trace: <strong>' . $params["action"] . '</strong>] on ' . $entity . ' [Debug trace: <strong> ' . $entity . ' id ' . htmlentities($_POST[$entityIdIndex]) . '</strong>] was not performed correctly.<br>[Debug trace: <strong>' . htmlentities($e->getMessage()) . '</strong>]</span>');
                $performed = false;
            }
            // Action was performed successfully on entity!
            if ($performed) {
                // Reset form associated datas
                $result = [];
                // Initialize success state
                $_SESSION[$formIdentifier . 'success'][$entity] = [
                    'state' => true, // show success message (not really useful)
                    'id' => htmlentities($_POST[$entityIdIndex]), // retrieve entity id
                    'message' => $params['successMessage'] . htmlentities($_POST[$entityIdIndex]), // customize success message as regards action
                    'slideRank' => htmlentities($_POST[$tokenPrefix . 'slide_rank']) // last slide item reminder to position slide after redirection
                ];
                // Redirect to admin home action (to reset submitted form)
                if (!is_null($redirectUrl)) {
                    $this->httpResponse->addHeader('Location: ' . $redirectUrl);
                    exit();
                }
            }
        }
        // Update error notice messages and form values
        return $result;
    }

    /**
     * Regenerate all existing form tokens
     * @return void
     */
    protected function regenerateAllFormTokens() {
        // Change token dynamic key and value for each existing token
        foreach (array_keys($_SESSION) as $key) { // no use of $value
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
        $activationCode = substr(hash('sha256', $salt . $UserEmail), 0, 40);
        return $activationCode;
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
     * Check if user generated session token matches token in $_COOKIE value
     * @param string $cookieToken: token value stored in $_COOKIE
     * @param string $sessionToken: token value stored in $_SESSION
     * @return boolean
     */
    protected function checkUserSessionTokenValue($cookieToken, $sessionToken)
    {
        return $cookieToken === $sessionToken;
    }

    /**
     * Check if user generated password renewal authentication token matches token in $_POST/$_GET value
     * @param string $inputToken: user input token value
     * @param string $generatedToken: token value stored in database
     * @return boolean
     */
    protected function checkPasswordUpdateTokenValue($inputToken, $generatedToken)
    {
        return $inputToken === $generatedToken;
    }
}