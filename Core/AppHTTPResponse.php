<?php
namespace Core;
use Core\Routing\AppRouter;
use Core\AppPage;

/**
 * Create a HTTP response class
 */
class AppHTTPResponse
{
	/**
     * @var object: a instance of AppPage
     */
    private $page;

	/**
     * Constructor
     * @return void
     */
    public function __construct()
	{
		$this->page = new AppPage();
	}

    /**
     * Get wrong used "url" parameter
     * @return string: url requested by user
     */
    public function getWrongUrl($url) {
        if (preg_match('#^/?http_error/[0-9]{3}/(.*)$#', $url, $matches)) {
            // Refreshed url: HTTP error is added to url.
            return $matches[1];
        } else {
            // Wrong url is not refreshed.
            return $url;
        }
    }

	/**
     * Add (send) a HTTP header
     * @param string $string
     * @param bool $replace: should replace previous header of the same type
     * @param int|null $http_response_code
     * @return void
     */
    public function addHeader($string, $replace = true, $http_response_code = null)
	{
	    header($string);
 	}

    /**
     * Refresh URL with http response code
     * @param int $httpResponseCode
     * @param string $message: message to inform user
     * @param AppRouter $router: unique instance of router
     * @param boolean $isRefreshed: should use refresh HTTP header
     * @return void
     */
    public function setError($httpResponseCode, $message, AppRouter $router, $isRefreshed = true)
    {
        if ($isRefreshed) {
            // Use refresh HTTP header
            if (is_int($httpResponseCode) && !preg_match('#^/?http_error/' . $httpResponseCode . '/.*$#', $router->getUrl())) {
                $this->addHeader('Refresh: 0; url=/http_error/' . $httpResponseCode . '/' . $router->getUrl());
            } else {
                call_user_func_array([$this, "set${httpResponseCode}ErrorResponse"], [$message, $router]);
            }
        } else {
            // Render error directly
            call_user_func_array([$this, "set${httpResponseCode}ErrorResponse"], [$message, $router]);
        }
    }

	/**
     * Display 404 error customized page
     * @param string $message: message to inform user
     * @param AppRouter $router
     * @return void
     */
    public function set404ErrorResponse($message, AppRouter $router)
	{
        // Send "not found" HTTP headers
        $this->addHeader('Status: 404 Not Found');
        $this->addHeader('HTTP/1.1 404 Not Found');

		// Prepare permalink to website homepage
        $homeURL = $router->useURL('Home\Home|isCalled', null);

		// Render template
        $varsArray = [
			'metaTitle' => '404 Error',
			'metaDescription' => '',
			'imgBannerCSSClass' => 'notfound-404',
			'message' => $message,
			'homeURL' => $homeURL
		];
		echo $this->page->renderTemplate('HTTPErrors/404-error.tpl', $varsArray);
	}
}