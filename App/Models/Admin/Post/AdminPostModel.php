<?php
namespace App\Models\Admin;
use App\Models\BaseModel;
use Core\Database\AppDatabase;
use Core\AppHTTPResponse;
use Core\Routing\AppRouter;
use Core\Config\AppConfig;
use App\Models\Blog\Entity\User;

class AdminPostModel extends AdminModel
{
	/**
	 * Constructor
	 * @param AppHTTPResponse instance
	 * @param AppRouter instance
	 * @param AppConfig instance
	 */
	public function __construct(AppHTTPResponse $httpResponse, AppRouter $router, AppConfig $config)
	{
	}
}