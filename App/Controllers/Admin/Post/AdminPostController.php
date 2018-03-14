<?php
namespace App\Controllers\Admin\Post;
use App\Controllers\Admin\AdminController;
use Core\Routing\AppRouter;

/**
 * Manage all actions as concerns Post entity in back-end
 */
class AdminPostController extends AdminController
{
	/**
     * Constructor
     * @param AppRouter $router
     * @return void
     */
    public function __construct(AppRouter $router)
    {
        parent::__construct($router);
		$this->currentModel = $this->getCurrentModel(__CLASS__);
	}
}