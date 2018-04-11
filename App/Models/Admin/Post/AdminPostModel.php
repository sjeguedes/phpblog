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
     * Get a Post entity by id
     * Use an external model: PostModel
     * @return array: an array of Post entity datas
     */
    public function getPostById($postId) {
        return $this->externalModels['postModel']->getSingleById($postId);
    }

    /**
     * Get a Comment entity by id
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
     * @param boolean $published: true (only published post) or false
     * @return array: an array of Post entities instances
     */
    public function getPostList($published = false) {
        return $this->externalModels['postModel']->getList($published); // param "false" means all posts (with all states)
    }

    /**
     * Get Post entities ordered by creation date with their author data
     * Use an external model: PostModel
     * @param boolean $published: true (only published post) or false
     * @return array: an array of Post entities instances with author data
     */
    public function getPostListWithAuthor($published = false) {
        return $this->externalModels['postModel']->getListWithAuthor($published); // param "false" means all posts (with all states)
    }
}