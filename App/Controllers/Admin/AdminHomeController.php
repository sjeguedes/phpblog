<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use Core\AppPage;
use Core\AppHTTPResponse;
use Core\Routing\AppRouter;
use Core\Config\AppConfig;
/**
 *
 */
class AdminHomeController extends BaseController
{
	public function __construct(AppHTTPResponse $httpResponse, AppRouter $router, AppConfig $config)
	{
		parent::__construct($page, $httpResponse, $router, $config);
		$this->currentModel = $this->getCurrentModel(__CLASS__);
	}

	public function getContactList()
	{
		$getAllContacts = $this->currentModel->selectAll('contacts');
		return $getAllContacts;
	}
}