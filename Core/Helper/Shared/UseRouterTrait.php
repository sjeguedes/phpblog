<?php
namespace Core\Helper\Shared;

use Core\Routing\AppRouter;

/**
 * Manage use of AppRouter instance
 */
trait UseRouterTrait
{
    /**
     * @var AppRouter instance
     */
    private static $_router;

    /**
     * Get AppPage instance
     *
     * @return object: an AppPage instance
     */
    public static function getRouter()
    {
        return self::$_router;
    }

    /**
     * Set AppRouter instance
     *
     * @param AppRouter $router: an AppRouter instance
     *
     * @return void
     */
    public static function setRouter(AppRouter $router)
    {
        self::$_router = $router;
    }
}
