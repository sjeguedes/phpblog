<?php
namespace App\Models\Home;
use App\Models\BaseModel;
use Core\Database\AppDatabase;
use Core\AppHTTPResponse;
use Core\Routing\AppRouter;

class HomeModel extends BaseModel
{
	/**
	 * Constructor
	 * @param HTTPResponse instance
	 * @param AppRouter instance
	 */
	public function __construct(AppHTTPResponse $httpResponse, AppRouter $router)
	{
		parent::__construct(AppDatabase::getInstance(), $httpResponse, $router);
	}

	// public function getPOSTDatas()
	// {

	// }

	// public function insertPOSTDatas()
	// {
		
	// }
}