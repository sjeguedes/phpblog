<?php
namespace App\Controllers\Home;
use App\Controllers\BaseController;
use Core\AppPage;
use Core\AppHTTPResponse;
use Core\Routing\AppRouter;
/**
 *
 */
class HomeController extends BaseController
{
	public function __construct(AppHTTPResponse $httpResponse, AppRouter $router)
	{
		parent::__construct(new AppPage(), $httpResponse, $router);
		$this->currentModel = $this->getCurrentModel(__CLASS__);
	}

	public function showHome($matches)
	{
		$varsArray = [
			'metaTitle' => 'Blog made with OOP in PHP code',
			'metaDescription' => 'This blog aims at showing and manage articles.',
			'imgBannerCSSClass' => 'home',
			'contactList' => $this->getContactList()
		];
		echo $this->page->renderTemplate('Home/home-index.tpl', $varsArray);
	}

	public function sendContactMessage($matches)
	{
		$homeURL = $this->router->useURL('Home\Home|showHome', null);

		$varsArray = [
			'metaTitle' => 'Please contact me by filling the dedicated form.',
			'metaDescription' => 'You are able to send me a message directly, for work or simple exchange.',
			'imgBannerCSSClass' => 'home'
		];
		$this->httpResponse->addHeader('Location:' . $homeURL);
		echo $this->page->renderTemplate('Home/home-index.tpl', $varsArray);
	}

	public function getContactList()
	{
		$getAllContacts = $this->currentModel->selectAll('contacts');
		//var_dump($getAllContacts);
		return $getAllContacts;
	}

}