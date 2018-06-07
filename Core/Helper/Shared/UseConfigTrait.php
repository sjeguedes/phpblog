<?php
namespace Core\Helper\Shared;

use Core\Config\AppConfig;

/**
 * Manage use of AppConfig instance
 */
trait UseConfigTrait
{
    /**
     * @var AppConfig instance
     */
    private static $_config;

    /**
     * Get AppConfig instance
     *
     * @return object: an AppConfig instance
     */
    public static function getConfig()
    {
        return self::$_config;
    }

    /**
     * Set AppConfig instance
     *
     * @param AppConfig $config: an AppConfig instance
     *
     * @return void
     */
    public static function setconfig(AppConfig $config)
    {
        self::$_config = $config;
    }
}
