<?php
namespace Core\Helper;
use Core\AppHTTPResponse;

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
     * Instanciate a unique AppStringModifier object (Singleton)
     * @return AppStringModifier: a unique instance of AppStringModifier
     */
	public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new AppStringModifier();
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
        self::$_httpResponse->set404ErrorResponse(self::isDebug('Technical error [Debug trace: Don\'t try to clone singleton ' . __CLASS__ . '!]'));
    }

    /**
     * Use trim() function
     * @param string $string
     * @return string
     */
    public static function trimStr($string)
    {
    	return trim($string);
    }

    /**
     * Use strtolower() function
     * @param string $string
     * @return string
     */
    public static function strtolowerStr($string)
    {
    	return strtolower($string);
    }

    /**
     * Use strtoupper() function
     * @param string $string
     * @return string
     */
    public static function strtoupperStr($string)
    {
    	return strtoupper($string);
    }

    /**
     * Use ucfirst() function
     * @param string $string
     * @return string
     */
    public static function ucfirstStr($string)
    {
    	return ucfirst($string);
    }

    /**
     * Use ucwords() function
     * @param string $string
     * @return string
     */
    public static function ucwordsStr($string)
    {
    	return ucwords($string);
    }
}