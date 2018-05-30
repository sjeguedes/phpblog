<?php
namespace App\Controllers\Admin\Post;
use App\Controllers\Admin\AdminController;
use Core\AppPage;
use Core\AppHTTPResponse;
use Core\Routing\AppRouter;
use Core\Config\AppConfig;

/**
 * Manage all actions as concerns Post entity in back-end
 */
class AdminPostController extends AdminController
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
}