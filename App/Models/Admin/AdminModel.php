<?php
namespace App\Models\Admin;
use App\Models\BaseModel;
use App\Models\Blog\Post\PostModel;
use Core\Routing\AppRouter;

/**
 * Create a parent class for admin models
 */
abstract class AdminModel extends BaseModel
{
    /**
     * @var array: an array of models used by admin model classes
     */
    protected $externalModels;

    /**
     * Constructor
     * @param AppRouter $router: an instance of AppRouter
     * @return void
     */
    public function __construct(AppRouter $router)
    {
        parent::__construct($router);
        // Store an instance of PostModel
        $this->externalModels['postModel'] = new PostModel($router);
    }

    /**
     * Delete an entity in database with its id
     * @param string $entityId: entity id
     * @param array $datas: an array which contains only entity type
     * to delete entity
     * @return void
     */
    public function deleteEntity($entityId, $datas)
    {
        $table = $datas['entity'] . 's';
        $columnPrefix = $datas['entity'] . '_';
        $query = $this->dbConnector->prepare("DELETE
                                              FROM $table
                                              WHERE ${columnPrefix}id = ?");
        $query->bindParam(1, $entityId, \PDO::PARAM_INT);
        $query->execute();
    }

    /**
     * Update an entity in database with its id
     * @param string $entityId: entity id
     * @param array $newDatas: an array of parameters (entity type and values)
     * to update entity
     * @return void
     */
    public function updateEntity($entityId, $newDatas)
    {
        // Declare type to secure (add other params in array if necessary)
        $PDOParams = [\PDO::PARAM_INT, \PDO::PARAM_STR, \PDO::PARAM_BOOL, \PDO::PARAM_NULL];
        $table = $newDatas['entity'] . 's';
        $columnPrefix = $newDatas['entity'] . '_';
        $set = '';
        $newValues = $newDatas['values'];
        for ($i = 0; $i < count($newValues); $i ++) {
            if ($i == 0) {
                $set .= $columnPrefix . $newValues[$i]['column'] . ' = :' . $newValues[$i]['column'];
            } else {
                $set .= ', ' . $columnPrefix . $newValues[$i]['column'] . ' = :' . $newValues[$i]['column'];
            }
        }
        $query = $this->dbConnector->prepare("UPDATE $table
                                              SET $set
                                              WHERE ${columnPrefix}id = :entityId");
        for ($i = 0; $i < count($newValues); $i ++) {
             $query->bindParam(':' . $newValues[$i]['column'], $newValues[$i]['value'], $PDOParams[$newValues[$i]['type']] - 1);
        }
        $query->bindParam(':entityId', $entityId, \PDO::PARAM_INT);
        $query->execute();
    }
}