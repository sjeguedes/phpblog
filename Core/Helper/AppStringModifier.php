<?php
namespace Core\Helper;

/**
 * Class to format a string
 */
class AppStringModifier
{
	/**
	 * @var object: unique instance of AppStringModifier
	 */
	private static $_instance;

	/**
	 * Singleton
	 * @return object AppStringModifier
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