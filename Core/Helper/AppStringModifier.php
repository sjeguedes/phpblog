<?php
namespace Core\Helper;

use Core\Routing\AppRouter;
use voku\helper\URLify;

/**
 * Format a string with helpers
 */
class AppStringModifier
{
    /**
     * @var object: unique instance of AppStringModifier
     */
    private static $_instance;
    /**
     * @var AppRouter instance
     */
    private static $_router;
    /**
     * @var object: an instance of AppConfig
     */
    private static $_config;
    /**
     * @var object: an instance of AppHTTPResponse
     */
    private static $_httpResponse;

    /**
     * Instanciate a unique AppStringModifier object (Singleton)
     *
     * @param object: an AppRouter instance
     *
     * @return AppStringModifier: a unique instance of AppStringModifier
     */
    public static function getInstance(AppRouter $router)
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new AppStringModifier($router);
        }
        return self::$_instance;
    }

    /**
     * Constructor
     *
     * @param object: an AppRouter instance
     *
     * @return void
     */
    private function __construct(AppRouter $router)
    {
        // Set AppRouter instance
        self::$_router = $router;
        // Set AppHTTPResponse instance used router!
        self::$_httpResponse = self::$_router->getHTTPResponse();
        // Set AppConfig instance used by router!
        self::$_config = self::$_router->getConfig();
    }

    /**
    * Magic method __clone
    *
    * @return void
    */
    public function __clone()
    {
        self::$_httpResponse->set404ErrorResponse(self::$_config::isDebug('Technical error [Debug trace: Don\'t try to clone singleton ' . __CLASS__ . '!]'), self::$_router);
        exit();
    }

    /**
     * Use trim() function
     *
     * @param string $string
     *
     * @return string
     */
    public static function trimStr($string)
    {
        return trim($string);
    }

    /**
     * Use strtolower() function
     *
     * @param string $string
     *
     * @return string
     */
    public static function strtolowerStr($string)
    {
        return strtolower($string);
    }

    /**
     * Use strtoupper() function
     *
     * @param string $string
     *
     * @return string
     */
    public static function strtoupperStr($string)
    {
        return strtoupper($string);
    }

    /**
     * Use ucfirst() function
     *
     * @param string $string
     *
     * @return string
     */
    public static function ucfirstStr($string)
    {
        // HTML tags
        if (preg_match('#^<[\w\d]+>(\w){1}#', $string)) {
            return preg_replace('#<[\w\d]+>(\w){1}#', ucfirst('$1'), $string);
        } else {
            return ucfirst($string);
        }
    }

    /**
     * Use ucwords() function
     *
     * @param string $string
     *
     * @return string
     */
    public static function ucwordsStr($string)
    {
        return ucwords($string);
    }

    /**
     * Use URLify::filter() function
     * Create a slug for pretty url
     *
     * @param string $string
     *
     * @return string: formated slug
     */
    public static function slugStr($string)
    {
        return URLify::filter($string);
    }
}
