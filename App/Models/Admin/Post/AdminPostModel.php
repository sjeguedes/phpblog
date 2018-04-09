<?php
namespace App\Models\Admin\Post;
use App\Models\Admin\AdminModel;
use Core\Routing\AppRouter;
use App\Models\Admin\Entity\User;
use App\Models\Admin\Entity\Comment;

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

    /**
     * Get a Comment entity
     * Use an external model: PostModel
     * @return array: an array of Comment entity datas
     */
    public function getCommentById($commentId) {
        return $this->externalModels['postModel']->getCommentById($commentId);
    }

    /**
     * Get all Comment entities ordered by creation date and by post id
     * @return array: an array which contains all Comment entities instances
     */
    public function getCommentList()
    {
        $comments = [];
        $query = $this->dbConnector->query('SELECT *
                                            FROM comments
                                            ORDER BY comment_creationDate
                                            DESC, comment_postId');
        $query->execute();

        while($datas = $query->fetch(\PDO::FETCH_ASSOC)) {
            $comments[] = new Comment($datas);
        }
        return $comments;
    }

    /**
     * Get Post entities ordered by creation date
     * Use an external model: PostModel
     * @return array: an array of Post entities instances
     */
    public function getPostList() {
        return $this->externalModels['postModel']->getList();
    }
}