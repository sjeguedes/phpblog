<?php
namespace Core\Session;
use Core\Routing\AppRouter;

/**
 * Manage a user session
 * @see: particular session methods are inspired from https://gist.github.com/Nilpo/5449999
 */
class AppSession
{
    /**
     * @var object: a unique instance of AppSession
     */
    private static $_instance;
    /**
     * @var AppRouter instance
     */
    private static $_router;
    /**
     * @var integer: Inactivity duration (in seconds) before a session expires.
     */
    private const SESSION_TIME_LIMIT = 1800;
    /**
     * @var integer: Duration (in seconds) before a session id is regenerated.
     */
    private const SESSION_ID_TIME_LIMIT = 600;

    /**
     * Instanciate a unique AppSession object (Singleton)
     * @return AppSession: a unique instance of AppSession
     */
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new AppSession();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     * @return void
     */
    private function __construct()
    {
    }

    /**
    * Magic method __clone
    * @return void
    */
    public function __clone()
    {
        self::$_router->getHTTPResponse()->set404ErrorResponse(self::$_router->getConfig()::isDebug('Technical error [Debug trace: Don\'t try to clone singleton ' . __CLASS__ . '!]'), self::$_router);
    }

    /**
     * Set router AppRouter instance
     * @param AppRouter $currentRouter: an AppRouter instance
     * @return void
     */
    public static function setRouter(AppRouter $currentRouter) {
        self::$_router = $currentRouter;
    }

    /**
     * Initialize a new session or resume an existing session.
     * @param boolean $regenerateSessionId: must regenerate session id with a timer rule
     * @return boolean: return true upon success and false upon failure.
     * @throws Exception: sessions are disabled.
     */
    private static function init($regenerateSessionId)
    {
        try {
            if (function_exists('session_status')) {
                // PHP 5.4.0+
                if (session_status() == PHP_SESSION_DISABLED) {
                    throw new \Exception(self::$_router->getConfig()::isDebug('Sorry, a session error happened! [Debug trace: session is disabled.]'));
                }
            }
            if ('' === session_id()) {
                // Disallow session passing as a GET parameter.
                if (ini_set('session.use_only_cookies', 1) === false) {
                    throw new \Exception(self::$_router->getConfig()::isDebug('Sorry, a session error happened! [Debug trace: session is passed as a GET parameter.]'));
                }
                // Mark the cookie as accessible only through the HTTP protocol.
                if (ini_set('session.cookie_httponly', 1) === false) {
                    throw new \Exception(self::$_router->getConfig()::isDebug('Sorry, a session error happened! [Debug trace: cookie is accessible only through the HTTP protocol.]'));
                }
                // Set default session cookie params
                $params = [
                    'lifetime' => self::SESSION_TIME_LIMIT,
                    'path' => '/',
                    'domain' => preg_replace('#^https?://#', '', self::$_router->getConfig()::getParam('domain')),
                    'secure' => self::$_router->getConfig()::getParam('https') ? 1 : 0, // true or false
                    'httponly' => 1 // true
                ];
                session_set_cookie_params($params['lifetime'], $params['path'], $params['domain'],  $params['secure'], $params['httponly']);

            }
            // Start an effective session
            session_start();
            // Check if session must expire in case of no page loaded.
            self::expire();
            // Help prevent session hijacking by resetting the session id each time a particular delay is reached.
            if ($regenerateSessionId) {
                // Store current timestamp only for the first time
                $now = time();
                // Initialize session id time base
                isset($_SESSION['newSID']['timeBase']) ? $_SESSION['newSID']['timeBase'] : $_SESSION['newSID']['timeBase'] = $now;
                // Page is loaded for the first time, or session id time limit is reached: session id must be regenerated!
                if (($_SESSION['newSID']['timeBase'] == $now) || ($now > $_SESSION['newSID']['timeBase'] + self::SESSION_ID_TIME_LIMIT)) {
                    // If a previous custom user session token cookie exists, reset it
                    if (isset($_SESSION['newSID']['indexSalt'])) {
                        self::resetCookie('UPDATEDUST' . $_SESSION['newSID']['indexSalt']);
                    }
                    // Regenerate session id
                    session_regenerate_id(true);
                    // Prepare session values for next custom user session token cookie
                    $_SESSION['newSID'] = [
                        'state' => true,
                        'mustUpdateTokens' => true,
                        'timeBase' => time(), // manage custom user session token cookie time validity
                        'indexSalt' => bin2hex(openssl_random_pseudo_bytes(8)) // salt for custom user session token cookie name
                    ];
                    $newSID = true;
                } else {
                    $newSID = false;
                }
            } else {
                $newSID = false;
            }
            // With this test, self::isSessionIdRegenerated() can return a correct result
            if (!$newSID && isset($_SESSION['newSID'])) {
                $_SESSION['newSID']['state'] = false;
            }
            return $newSID;
        } catch (\Exception $e) {
            // Show error view with 403 error
            self::$_router->getHTTPResponse()->set403ErrorResponse(self::$_router->getConfig()::isDebug($e->getMessage()), self::$_router);
        }
    }

    /**
     * Start or resume a session by calling {@link AppSession::init()}.
     * @see AppSession::init()
     * @param boolean $regenerateSessionId: must regenerate session id with a timer rule
     * @throws Exception: sessions are disabled.
     * @return boolean: return true upon success and false upon failure.
     */
    public static function start($regenerateSessionId = true)
    {
        // Start session with init() method
        return self::init($regenerateSessionId);
    }

    /**
     * Return current session cookie parameters or an empty array.
     * @return array: an array of session cookie parameters.
     */
    public static function getSessionCookieParams()
    {
        $cookieParams = [];
        if ('' !== session_id()) {
            $cookieParams = session_get_cookie_params();
        }
        return $cookieParams;
    }

    /**
     * Set a custom cookie stored in $_COOKIE
     * @param string $cookieName : custom cookie name with uppercase letters and random number
     * @param type $cookieValue: custom cookie value to store
     * @return void
     */
    public static function setCustomCookie($cookieName, $cookieValue) {
        // Get parts of session cookie params
        $params = self::getSessionCookieParams();
        // Set custom cookie
        setcookie($cookieName, $cookieValue, 0, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    /**
     * Reset a custom or session cookie stored in $_COOKIE
     * @param string $cookieName : custom cookie name with uppercase letters and random number
     * @return void
     */
    public static function resetCookie($cookieName) {
        // Get parts of session cookie params
        $params = self::getSessionCookieParams();
        // Reset custom cookie
        setcookie($cookieName, '', time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    /**
     * Close the current session and release session file lock.
     * @return boolean: return true upon success and false upon failure.
     */
    public static function close()
    {
        if ('' !== session_id()) {
            return session_write_close();
        }
        return true;
    }

    /**
     * Expire a session if it has been inactive for a specified amount of time.
     * @return boolean: true if session expired, or false
     */
    private static function expire()
    {
        $last = isset($_SESSION['lastActive']) ? $_SESSION['lastActive'] : false;
        $isDestroyed = false;
        if (false !== $last && (time() - $last > self::SESSION_TIME_LIMIT)) {
            self::destroy();
            $isDestroyed = true;
        }
        $_SESSION['lastActive'] = time();
        return $isDestroyed;
    }

    /**
     * Remove session data and destroy the current session.
     * @return void
     */
    public static function destroy()
    {
        if ('' !== session_id()) {
            // If choice is to kill the session, also delete the session cookie.
            // Note: this will destroy the session, and not just the session data!
            if (ini_get('session.use_cookies')) {
                self::resetCookie(session_name());
            }
            $_SESSION = [];
            session_destroy();
        }
    }

    /**
     * Check if session id is renegerated
     * @return boolean: true if session id is regenerated, or false
     */
    public static function isSessionIdRegenerated()
    {
        return isset($_SESSION['newSID']['mustUpdateTokens']) && $_SESSION['newSID']['mustUpdateTokens'] ? true : false;
    }

    /**
     * Check if a user is authenticated
     * @return array|boolean: an array which contains essential datas from User entity stored in $_SESSION
     */
    public static function isUserAuthenticated()
    {
        return isset($_SESSION['user']) && !empty($_SESSION['user']) ? $_SESSION['user'] : false;
    }
}