<?php
namespace Core\Helper\Shared;

use Core\HTTP\AppHTTPResponse;

/**
 * Manage use of AppHTTPResponse instance
 */
trait UseHTTPResponseTrait
{
    /**
     * @var AppHTTPResponse instance
     */
    private static $_httpResponse;

    /**
     * Get AppHTTPResponse instance
     *
     * @return object: an AppHTTPResponse instance
     */
    public static function getHTTPResponse()
    {
        return self::$_httpResponse;
    }

    /**
     * Set AppHTTPResponse instance
     *
     * @param AppHTTPResponse $httpResponse: an AppHTTPResponse instance
     *
     * @return void
     */
    public static function setHTTPResponse(AppHTTPResponse $httpResponse)
    {
        self::$_httpResponse = $httpResponse;
    }
}
