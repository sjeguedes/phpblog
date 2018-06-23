<?php
namespace Core\Helper\Shared;

use Core\Page\AppPage;

/**
 * Manage use of AppPage instance
 */
trait UsePageTrait
{
    /**
     * @var AppPage instance
     */
    private static $_page;

    /**
     * Get AppPage instance
     *
     * @return object: an AppPage instance
     */
    public static function getPage()
    {
        return self::$_page;
    }

    /**
     * Set AppPage instance
     *
     * @param AppPage $page: an AppPage instance
     *
     * @return void
     */
    public static function setPage(AppPage $page)
    {
        self::$_page = $page;
    }
}
