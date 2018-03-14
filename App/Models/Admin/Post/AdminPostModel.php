<?php
namespace App\Models\Admin;
use App\Models\BaseModel;
use Core\Routing\AppRouter;
use App\Models\Admin\Entity\User;

/**
 * Create an admin model to manage posts
 */
class AdminPostModel extends AdminModel
{
	/**
     * Constructor
     * @param AppRouter $router: an instance of AppRouter
     * @return void
     */
    public function __construct(AppRouter $router)
    {
        parent::__construct($router);
    }
}