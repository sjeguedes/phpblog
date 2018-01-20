<?php
namespace App\Models\Admin;
use App\Models\BaseModel;
use Core\Config\AppConfig;
use App\Models\Blog\Entity\User;

/**
 * Create an admin model to manage posts
 */
class AdminPostModel extends AdminModel
{
	/**
	 * Constructor
	 * @param AppConfig $config: an instance of AppConfig
     * @return void
	 */
	public function __construct(AppConfig $config)
	{
        parent::__construct($config);
	}
}