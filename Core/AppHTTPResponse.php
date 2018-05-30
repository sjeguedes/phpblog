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
     * @var object: an instance of AppPage
     */
    private $page;

	/**
     * Constructor
     * @return void
     */
    public function __construct()
	{
		// TODO: use DIC to instantiate AppPage object!
        $this->page = new AppPage();
	}

    /**
     * Get wrong used "url" parameter
     * @param string $url: url requested by user
     * @return string: url requested by user or refreshed url with HTTP error
     */
    public function getWrongUrl($url) {
        if (preg_match('#^/?http-error/[0-9]{3}/(.*)/?#', $url, $matches)) {
            // Refreshed url: HTTP error is added to url.
            return $matches[1];
        } else {
            // Wrong url is not refreshed.
            return $url;
        }
    }

	/**
     * Add (send) a HTTP header
     * @param string $string: defined header
     * @param bool $replace: should replace previous header of the same type
     * @param int|null $http_response_code
     * @return void
     */
    public function addHeader($string, $replace = true, $http_response_code = null)
	{
	    header($string);
 	}

    /**
     * Catch HTTP redirect status to customize several errors
     * WARNING: must be used with serveur configuration to run correctly!
     * Some cases need to be declared in server configuration.
     * @param AppRouter $router
     * @return void
     */
    public function autoCatchErrorRedirectstatus(AppRouter $router)
    {
        $status = $_SERVER['REDIRECT_STATUS'];
        // Do not auto catch for several particular cases managed with application
        $excluded = [404, 301, 302, 200];
        if (!in_array((int) $status, $excluded)) {
            $codes = [
                400 => ['400 Bad Request', 'The request cannot be fulfilled due to bad syntax.'],
                403 => ['403 Forbidden', 'The server has refused to fulfil your request.'],
                404 => ['404 Not Found', 'The page you requested was not found on this server.'],
                405 => ['405 Method Not Allowed', 'The method specified in the request is not allowed for the specified resource.'],
                408 => ['408 Request Timeout', 'Your browser failed to send a request in the time allowed by the server.'],
                500 => ['500 Internal Server Error', 'The request was unsuccessful due to an unexpected condition encountered by the server.'],
                502 => ['502 Bad Gateway', 'The server received an invalid response while trying to carry out the request.'],
                504 => ['504 Gateway Timeout', 'The upstream server failed to send a request in the time allowed by the server.'],
            ];

            if (!isset($codes[(int) $status]) || strlen($status) != 3) {
                $message = 'Sorry, uncaught (or invalid) HTTP response happened.';
                $this->autoSetErrorResponse($message, $router, false, true);
            } else {
                $title = $codes[$status][0];
                $message = $codes[$status][1];
                $this->autoSetErrorResponse($message, $router, $status);
            }
            exit();
        }
    }

    /**
     * Display automatic error customized page with serveur HTTP status
     * Headers are sent automatically by server!
     * @param string $message: message to inform user
     * @param string|false $status:
     * @return void
     */
    public function autoSetErrorResponse($message, AppRouter $router, $status = false, $isUncaught = false)
    {
        // Prepare permalink to website homepage
        $homeURL = $router->useURL('Home\Home|isCalled', null);
        // Render template
        $varsArray = [
            'metaTitle' => $status . ' Error',
            'metaDescription' => '',
            'imgBannerCSSClass' => 'http-response',
            'status' => $status,
            'message' => $message,
            'homeURL' => $homeURL
        ];
        if ($isUncaught) {
            echo $this->page->renderTemplate('HTTPStatus/http-uncaught-response.tpl', $varsArray);
        } else {
            echo $this->page->renderTemplate('HTTPStatus/http-error-response.tpl', $varsArray);
        }
    }

    /**
     * Refresh URL with http response code
     * @param int $httpResponseCode
     * @param string $message: message to inform user
     * @param AppRouter $router: unique instance of router
     * @param boolean $isRefreshed: should use refresh HTTP header
     * @return void
     */
    public function setError($httpResponseCode, $message, AppRouter $router, $isRefreshed = false)
    {
        // Technical error exception case: "exception=" added to message (a bit tricky!) to treat refresh case.
        $exception = false;
        if (preg_match('#^exception=#', $message)) {
            $message = str_replace('exception=', '', $message);
            $exception = true;
        }
        // Refresh with custom error request URI
        if ($isRefreshed) {
            // Use refresh HTTP header
            if (!preg_match('#^/?http-error/' . $httpResponseCode . '/#', $router->getUrl())) {
                if ($exception) {
                    $this->addHeader('Refresh: 0; url=/http-error/' . $httpResponseCode . '/' . rtrim($router->getUrl(), '/')
                    . '/?refreshException=true');
                } else {
                    $this->addHeader('Refresh: 0; url=/http-error/' . $httpResponseCode . '/' . $router->getUrl());
                }
            } else {
                // Render error after refresh
                call_user_func_array([$this, "set${httpResponseCode}ErrorResponse"], [$message, $router]);
                exit();
            }
        // No refresh
        } else {
            // Render error directly
            call_user_func_array([$this, "set${httpResponseCode}ErrorResponse"], [$message, $router]);
            exit();
        }
    }

	/**
     * Display 404 error customized page
     * @param string $message: message to inform user
     * @param AppRouter|null $router
     * @return void
     */
    public function set404ErrorResponse($message, AppRouter $router = null)
	{
        // Send "not found" HTTP headers
        $this->addHeader('Status: 404 Not Found');
        $this->addHeader('HTTP/1.1 404 Not Found');
		// Prepare permalink to website homepage
        if (!is_null($router)) {
            $homeURL = $router->useURL('Home\Home|isCalled', null);
        } else {
            $homeURL = '/';
        }
		// Render template
        $varsArray = [
			'metaTitle' => '404 Error',
			'metaDescription' => '',
			'imgBannerCSSClass' => 'notfound-404',
            'status' => 404,
			'message' => $message,
			'homeURL' => $homeURL
		];
		echo $this->page->renderTemplate('HTTPStatus/http-error-response.tpl', $varsArray);
	}
}