<?php
namespace App\Models\Admin\Post;

use App\Models\Admin\AdminModel;
use Core\Routing\AppRouter;
use App\Models\Admin\Home\AdminHomeModel;
use App\Models\Admin\Entity\User;
use App\Models\Admin\Entity\Comment;

/**
 * Create an admin model to manage posts
 */
class AdminPostModel extends AdminModel
{
    /**
     * Constructor
     *
     * @param AppRouter $router: an instance of AppRouter
     *
     * @return void
     */
    public function __construct(AppRouter $router)
    {
        parent::__construct($router);
        // Store an instance of AdminHomeModel
        $this->externalModels['adminHomeModel'] = new AdminHomeModel($router);
    }

    /**
     * Insert a Post entity in database
     *
     * @param array $postDatas: an array of post datas
     *
     * @return integer: new post id
     */
    public function insertPost($postDatas)
    {
        // Secure query
        $query = $this->dbConnector->prepare('INSERT INTO posts
                                              (post_creationDate, post_updateDate, post_title, post_intro, post_content, post_slug, post_isSlugCustomized, post_isValidated, post_isPublished, post_userId)
                                              VALUES (NOW(), NOW(), :title, :intro, :content, :slug, :isSlugCustomized, :isValidated, :isPublished, :userId)');
        $query->bindParam(':title', $title, \PDO::PARAM_STR);
        $query->bindParam(':intro', $intro, \PDO::PARAM_STR);
        $query->bindParam(':content', $content, \PDO::PARAM_STR);
        $query->bindParam(':slug', $slug, \PDO::PARAM_STR);
        $query->bindParam(':isSlugCustomized', $isSlugCustomized, \PDO::PARAM_BOOL);
        $query->bindParam(':isValidated', $isValidated, \PDO::PARAM_BOOL);
        $query->bindParam(':isPublished', $isPublished, \PDO::PARAM_BOOL);
        $query->bindParam(':userId', $userId, \PDO::PARAM_INT);
        // Insertion
        $title = $postDatas['title'];
        $intro = $postDatas['intro'];
        $content = $postDatas['content'];
        $slug = $postDatas['slug'];
        $isSlugCustomized = $postDatas['isSlugCustomized'];
        $isValidated = 0;
        $isPublished = 0;
        $userId = $postDatas['userId'];
        $query->execute();
        // Return last inserted id
        return $this->dbConnector->lastInsertId();
    }

    /**
     * Insert a Image entity in database
     *
     * @param array $imageDatas: an array of image datas
     *
     * @return integer: new image id
     */
    public function insertImage($imageDatas)
    {
        // Secure query
        $query = $this->dbConnector->prepare('INSERT INTO images
                                              (image_creationDate, image_updateDate, image_name, image_extension, image_dimensions, image_size, image_creatorId, image_postId)
                                              VALUES (NOW(), NOW(), :name, :extension, :dimensions, :size, :creatorId, :postId)');
        $query->bindParam(':name', $name, \PDO::PARAM_STR);
        $query->bindParam(':extension', $extension, \PDO::PARAM_STR);
        $query->bindParam(':dimensions', $dimensions, \PDO::PARAM_STR);
        $query->bindParam(':size', $size, \PDO::PARAM_INT);
        $query->bindParam(':creatorId', $creatorId, \PDO::PARAM_INT);
        $query->bindParam(':postId', $postId, \PDO::PARAM_INT);
        // Insertion
        $name = $imageDatas['name'];
        $extension = $imageDatas['extension'];
        $dimensions = $imageDatas['dimensions'];
        $size = $imageDatas['size'];
        $creatorId = $imageDatas['creatorId'];
        $postId = $imageDatas['postId'];
        $query->execute();
        // Return last inserted id
        return $this->dbConnector->lastInsertId();
    }

    /**
     * Get a Post entity by id
     * Use an external model: PostModel
     *
     * @param int $postId
     *
     * @return object|boolean: a Post entity instance, or false
     */
    public function getPostById($postId)
    {
        return $this->externalModels['postModel']->getSingleById($postId);
    }

    /**
     * Get a Post entity by its slug
     * Use an external model: PostModel
     *
     * @param string $postSlug
     *
     * @return object|boolean: a Post entity instance, or false
     */
    public function getPostBySlug($postSlug)
    {
        return $this->externalModels['postModel']->getSingleBySlug($postSlug);
    }

    /**
     * Get a User (Author) entity by id
     * Use an external model: PostModel
     *
     * @param int $userId
     *
     * @return object|boolean: a User entity instance, or false
     */
    public function getUserAuthorById($userId)
    {
        return $this->externalModels['postModel']->getAuthorById($userId);
    }

    /**
     * Get a Comment entity by id
     * Use an external model: PostModel
     *
     * @param int $commentId
     *
     * @return object|boolean: a Comment entity instance, or false
     */
    public function getCommentById($commentId)
    {
        return $this->externalModels['postModel']->getCommentById($commentId);
    }

    /**
     * Get Post entities ordered by creation date
     * Use an external model: PostModel
     *
     * @param boolean $published: true (only published post) or false
     *
     * @return array: an array of Post entities instances
     */
    public function getPostList($published = false)
    {
        return $this->externalModels['postModel']->getList($published); // param "false" means all posts (with all states)
    }

    /**
     * Get Post entities ordered by creation date with their author data
     * Use an external model: PostModel
     *
     * @param boolean $published: true (only published post) or false
     *
     * @return array: an array of Post entities instances with author data
     */
    public function getPostListWithAuthor($published = false)
    {
        return $this->externalModels['postModel']->getListWithAuthor($published); // param "false" means all posts (with all states)
    }

    /**
     * Get Image entities for a particular post
     * Use an external model: PostModel
     *
     * @param int $postId
     *
     * @return array|boolean: an array of Image entities instances, or false
     */
    public function getPostImageList($postId)
    {
        return $this->externalModels['postModel']->getImageListForSingle($postId);
    }

    /**
     * Get all Comment entities ordered by creation date and by post id
     *
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

        while ($datas = $query->fetch(\PDO::FETCH_ASSOC)) {
            $comments[] = new Comment($datas);
        }
        return $comments;
    }

    /**
     * Get a single user with its id
     *
     * @param string $userId
     *
     * @return object|boolean: a User entity instance or false
     */
    public function getUserById($userId)
    {
        return $this->externalModels['adminHomeModel']->getUserById($userId);
    }

    /**
     * Get User entities ordered by creation date
     *
     * @return array: an array of User entities instances
     */
    public function getUserList()
    {
        return $this->externalModels['adminHomeModel']->getUserList();
    }
}
