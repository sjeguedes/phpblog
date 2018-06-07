<?php
namespace Core\Helper\Shared;

use Core\Session\AppSession;

/**
 * Manage use of AppSession instance
 */
trait UseSessionTrait
{
    /**
     * @var AppSession instance
     */
    private static $_session;

    /**
     * Get AppSession instance
     *
     * @return object: an AppSession instance
     */
    public static function getSession()
    {
        return self::$_session;
    }

    /**
     * Set AppSession instance
     *
     * @param AppSession $session: an AppSession instance
     *
     * @return void
     */
    public static function setSession(AppSession $session)
    {
        self::$_session = $session;
    }
}
