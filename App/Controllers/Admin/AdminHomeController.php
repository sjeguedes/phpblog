<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use Core\AppPage;
use Core\AppHTTPResponse;
use Core\Routing\AppRouter;
use Core\Config\AppConfig;

/**
 * Manage admin homepage actions
 */
class AdminHomeController extends BaseController
{
	/**
	 * Constructor
	 * @param AppPage $page 
	 * @param AppHTTPResponse $httpResponse 
	 * @param AppRouter $router 
	 * @param AppConfig $config 
	 * @return void
	 */
	public function __construct(AppPage $page, AppHTTPResponse $httpResponse, AppRouter $router,  AppConfig $config)
	{
		parent::__construct($page, $httpResponse, $router, $config);
		$this->currentModel = $this->getCurrentModel(__CLASS__);
	}

	/**
	 * Render admin homepage template
	 * @return void
	 */
	public function showAdminHome()
	{
		// Get contact entities
		$contactList = $this->currentModel->getContactList();

		$jsArray = [
			0 => [
				'placement' => 'bottom',
				'src' => '/assets/js/admin-homepage.js'
			],
		];

		$varsArray = [
			'JS' => $jsArray,
			'metaTitle' => 'Admin homepage',
			'metaDescription' => 'Here, you can have a look at all the essential administration information and functionalities.',
			'metaRobots' => 'noindex, nofollow',
			'imgBannerCSSClass' => 'admin-home',
			'contactList' => $contactList
		];
		echo $this->page->renderTemplate('Admin/admin-home.tpl', $varsArray);
	}

	/**
	 * Get all contact entities datas
	 * @return array: an array which contains all the datas
	 */
	public function getContacts()
	{
		return $this->currentModel->selectAll('contacts');
	}
}