<?php
namespace Core;
use Core\Routing\AppRouter;
/**
* 
*/
class AppHTTPResponse
{
	private $page;

	public function __construct()
	{
		$this->page = new AppPage(); 
	}

	public function addHeader($header)
	{
	    header($header);
	}

	public function set404ErrorResponse($message, AppRouter $router = null)
	{
		$this->addHeader('HTTP/1.0 404 Not Found');
		$homeURL = $router->useURL('Home\Home|showHome', null);

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