<?php
namespace App\Models\Blog\Post;

use App\Models\BaseModel;
use Core\Routing\AppRouter;
use App\Models\Admin\Entity\Post;
use App\Models\Admin\Entity\Image;
use App\Models\Admin\Entity\User;
use App\Models\Admin\Entity\Comment;

/**
 * Create a class for front-end posts queries
 */
class PostModel extends BaseModel
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
    }

    /**
     * Get a post slug with its id
     *
     * @param string $postId
     *
     * @return return string: desired slug
     */
    public function getSlug($postId)
    {
        $query = $this->dbConnector->prepare('SELECT post_slug
                                              FROM posts
                                              WHERE post_id = ?
                                              LIMIT 1');
        $query->bindParam(1, $postId, \PDO::PARAM_INT);
        $query->execute();
        $data = $query->fetch(\PDO::FETCH_ASSOC);
        return $data['post_slug'];
    }

    /**
     * Get a single post with its id
     *
     * @param string $postId
     *
     * @return object|boolean: a Post entity instance or false
     */
    public function getSingleById($postId)
    {
        $query = $this->dbConnector->prepare('SELECT *
	    									  FROM posts
	    									  WHERE post_id = :postId');
        $query->bindParam(':postId', $postId, \PDO::PARAM_INT);
        $query->execute();
        $datas = $query->fetch(\PDO::FETCH_ASSOC);
        // Is there a result?
        if ($datas != false) {
            $post = new Post($datas);
            // Get paging number only for published posts
            if ($post->isPublished == 1) {
                $postIsOnPage = $this->getPagingForSingle($postId);
                if ($postIsOnPage != false) {
                    // Temporary param "pagingNumber" is created here:
                    $post->pagingNumber = $postIsOnPage;
                }
            }
            return $post;
        }
        return false;
    }

    /**
     * Get a single post with its slug
     *
     * @param string $postSlug
     *
     * @return object\boolean: a Post entity instance or false
     */
    public function getSingleBySlug($postSlug)
    {
        // Do not trust $postSlug based on $_POST['title'] when a post is created or updated!
        $query = $this->dbConnector->prepare('SELECT *
	    									  FROM posts
	    									  WHERE post_slug = :postSlug');
        $query->bindParam(':postSlug', $postSlug, \PDO::PARAM_STR);
        $query->execute();
        $datas = $query->fetch(\PDO::FETCH_ASSOC);
        if ($datas != false) {
            return new Post($datas);
        } else {
            return false;
        }
    }

    /**
     * Get a single post with its author datas
     *
     * @param string $postId
     * @param string|null $postSlug
     *
     * @return array|boolean: an array which contains a Post entity instance, or false
     */
    public function getSingleWithAuthor($postId, $postSlug = null)
    {
        // Check if $postSlug exists in database
        if (!is_null($postSlug)) {
            $isSlug = $this->getSingleBySlug($postSlug);
            if (!$isSlug) {
                return false;
            }
        }
        // Will associate author datas for each post
        $postWithAuthor = [];
        $post = $this->getSingleById($postId);
        if ($post != false) {
            // Get and store user who is also an author for current post
            $author = $this->getAuthorByPostUserId($post->userId);
            if ($author != false) {
                // Temporary parameter is created here:
                $post->author = $author;
                array_push($postWithAuthor, $post);
                return $postWithAuthor;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Get all Post entities
     *
     * @param boolean $published: true (only published posts) or false (all status)
     *
     * @return array: an array which contains all Post entities instances
     */
    public function getList($published = true)
    {
        $posts = [];
        $published = $published ? 'WHERE post_isPublished = 1' : '';
        $query = $this->dbConnector->query("SELECT *
    										FROM posts
                                            $published
                                            ORDER BY post_creationDate DESC");
        while ($datas = $query->fetch(\PDO::FETCH_ASSOC)) {
            $posts[] = new Post($datas);
        }
        return $posts;
    }

    /**
     * Get all Image entities ordered by creation date
     *
     * @return array: an array which contains all Image entities instances
     */
    public function getImageList()
    {
        $images = [];
        $query = $this->dbConnector->query("SELECT *
                                            FROM images
                                            ORDER BY image_creationDate DESC");
        while ($datas = $query->fetch(\PDO::FETCH_ASSOC)) {
            $images[] = new Image($datas);
        }
        return $images;
    }

    /**
     * Get published posts for a particular paging number: result depends on post per page quantity.
     *
     * @param int $pageId
     * @param int $postPerPage
     *
     * @return array: an array of needed parameters with list of posts on a particular page
     */
    public function getListByPaging($pageId, $postPerPage)
    {
        // Get first post row to show for group of posts which appears on selected page : must begin to 0 on first page ($pageId = 1)
        $start = ($pageId - 1) * $postPerPage;
        $postsOnpage = [];
        // SQL_CALC_FOUND_ROWS (MySQL 4+) also stores total number of rows (ignores LIMIT) with one query (to avoid secondary query with COUNT()!). OFFSET is more readable.
        $query = $this->dbConnector->prepare('SELECT SQL_CALC_FOUND_ROWS *
											  FROM posts
                                              WHERE post_isPublished = 1
											  ORDER BY post_creationDate DESC
											  LIMIT /*:start, :postPerPage*/:postPerPage OFFSET :start');
        $query->bindParam(':start', $start, \PDO::PARAM_INT);
        $query->bindParam(':postPerPage', $postPerPage, \PDO::PARAM_INT);
        $query->execute();
        // Compare post_id from retrieved posts to add rank property to corresponding Post instance
        while ($datas = $query->fetch(\PDO::FETCH_ASSOC)) {
            $postsOnpage[] = new Post($datas);
        }
        // Get the same result like COUNT() function:
        $query2 = $this->dbConnector->query('SELECT FOUND_ROWS()');
        // Get total number of posts in database
        $countedPosts = $query2->fetchColumn();
        // Get total number of pages for paging
        $pagesQuantity = ceil($countedPosts / $postPerPage);
        return ['currentPage' => $pageId, 'pageQuantity' => $pagesQuantity, 'postsOnPage' => $postsOnpage];
    }

    /**
     * Get paging number for a particular post with its id
     *
     * @param string $postId
     *
     * @return int: paging number to retrieve
     */
    public function getPagingForSingle($postId)
    {
        $result = $this->getRankForSingle($postId);
        $postRank = intval($result[0]);
        $postQuantity = intval($result[1]);
        $postPerPage = $this->config::getParam('posts.postPerPage');
        $paging = ceil($postQuantity / $postPerPage);
        $start = 0;
        for ($i = 1; $i <= $paging; $i++) {
            if ($start <= $postRank && $postRank < $start + $postPerPage) {
                $singleIsOnPage = $i;
                break;
            }
            $start = $start + $postPerPage;
        }
        return $singleIsOnPage;
    }

    /**
     * Get rank for a particular post
     *
     * @param string $postId
     *
     * @return array: an array which contains retrieved post rank, and total quantity of posts in database
     */
    public function getRankForSingle($postId)
    {
        $postsRank = $this->getRankForAll(); // no arguments means true: get rank only for all published posts
        for ($i = 0; $i < count($postsRank) - 1; $i++) {
            if ($postsRank[$i]['post_id'] == $postId) {
                $singleRank = $postsRank[$i]['rank'];
                break;
            }
        }
        return [$singleRank, $postsRank['total']];
    }

    /**
     * Get rank for each post
     *
     * @param boolean $published: true (only published posts) or false (all status)
     *
     * @return array: an array which contains post id and rank for each post and total quantity of posts in database
     */
    public function getRankForAll($published = true)
    {
        $postsRank = [];
        $i = 0;
        $published = $published ? 'WHERE post_isPublished = 1' : '';
        $query = $this->dbConnector->query("SELECT SQL_CALC_FOUND_ROWS p.post_id, (@curRank := @curRank + 1) AS rank
    										FROM posts p, (SELECT @curRank := -1) r
                                            $published
    										ORDER BY p.post_creationDate DESC");
        while ($datas = $query->fetch(\PDO::FETCH_ASSOC)) {
            $postsRank[$i]['post_id'] = $datas['post_id'];
            $postsRank[$i]['rank'] = $datas['rank'];
            $i++;
        }
        // Get the same result like COUNT() function:
        $query2 = $this->dbConnector->query('SELECT FOUND_ROWS()');
        // Get total number of posts in database
        $countedPosts = $query2->fetchColumn();
        $postsRank['total'] = $countedPosts;
        return $postsRank;
    }

    /**
     * Get an author with its user id
     *
     * @param string $userId
     *
     * @return object|boolean: a User entity instance or false
     */
    public function getAuthorById($userId)
    {
        $query = $this->dbConnector->prepare('SELECT *
                                              FROM users
                                              WHERE user_id = :userId');
        $query->bindParam(':userId', $userId, \PDO::PARAM_INT);
        $query->execute();
        $datas = $query->fetch(\PDO::FETCH_ASSOC);
        // Is there a result?
        if ($datas != false) {
            $user = new User($datas);
            return $user;
        } else {
            return false;
        }
    }

    /**
     * Get author infos with a post user id
     *
     * @param string $postUserId
     *
     * @return object|boolean: a User entity instance, or false
     */
    public function getAuthorByPostUserId($postUserId)
    {
        $query = $this->dbConnector->prepare('SELECT u.user_id, u.user_firstName, u.user_familyName,
                                              u.user_nickName, u.user_email,
	    									  u.user_password, u.user_isActivated, u.user_userTypeId
	    									  FROM posts p
	    									  INNER JOIN users u ON (p.post_userId = u.user_id)
	    									  WHERE p.post_userId = :postUserId');
        $query->bindParam(':postUserId', $postUserId, \PDO::PARAM_INT);
        $query->execute();
        $datas = $query->fetch(\PDO::FETCH_ASSOC);
        if ($datas != false) {
            return new User($datas);
        } else {
            return false;
        }
    }

    /**
     * Get all post with their author infos
     *
     * @param boolean $published: true (only published posts) or false (all status)
     *
     * @return array: an array which contains all Post entities with their author infos
     */
    public function getListWithAuthor($published = true)
    {
        // Will associate author datas for each post
        $postsWithAuthor = [];
        $posts = $this->getList($published);
        foreach ($posts as $post) {
            $author = $this->getAuthorByPostUserId($post->userId);
            // Temporary parameter: get and store user who is also an author for each post
            $post->author = $author;
            array_push($postsWithAuthor, $post);
        }
        return $postsWithAuthor;
    }

    /**
     * Insert a Comment entity in database
     *
     * @param array $commentDatas: an array of post comment datas
     *
     * @return void
     */
    public function insertComment($commentDatas)
    {
        // Secure query
        $query = $this->dbConnector->prepare('INSERT INTO comments
                                              (comment_creationDate, comment_nickName, comment_email, comment_title, comment_content, comment_postId)
                                              VALUES (NOW(), :nickName, :email, :title, :content, :postId)');
        $query->bindParam(':nickName', $nickName, \PDO::PARAM_STR);
        $query->bindParam(':email', $email, \PDO::PARAM_STR);
        $query->bindParam(':title', $title, \PDO::PARAM_STR);
        $query->bindParam(':content', $content, \PDO::PARAM_STR);
        $query->bindParam(':postId', $postId, \PDO::PARAM_INT);
        // Insertion
        $nickName = $commentDatas['pcf_nickName'];
        $email = $commentDatas['pcf_email'];
        $title = $commentDatas['pcf_title'];
        $content = $commentDatas['pcf_content'];
        // Post id was checked before by controller!
        $postId = $commentDatas['pcf_postId'];
        $query->execute();
    }

    /**
     * Get a single comment with its id
     *
     * @param string $commentId
     *
     * @return object|boolean: a Comment entity instance or false
     */
    public function getCommentById($commentId)
    {
        $query = $this->dbConnector->prepare('SELECT *
                                              FROM comments
                                              WHERE comment_id = :commentId');
        $query->bindParam(':commentId', $commentId, \PDO::PARAM_INT);
        $query->execute();
        $datas = $query->fetch(\PDO::FETCH_ASSOC);
        // Is there a result?
        if ($datas != false) {
            $comment = new Comment($datas);
            return $comment;
        } else {
            return false;
        }
    }

    /**
     * Get all Image entities for a particular post
     *
     * @param string $postId
     *
     * @return array|boolean: an array which contains all Image entities instances or false
     */
    public function getImageListForSingle($postId)
    {
        $images = [];
        $query = $this->dbConnector->prepare('SELECT *
                                              FROM images
                                              WHERE image_postId = ?
                                              ORDER BY image_creationDate DESC');
        $query->bindParam(1, $postId, \PDO::PARAM_INT);
        $query->execute();
        while ($datas = $query->fetch(\PDO::FETCH_ASSOC)) {
            $images[] = new Image($datas);
        }
        if (isset($images)) {
            return $images;
        } else {
            return false;
        }
    }

    /**
     * Get all Comment entities for a particular post
     *
     * @param string $postId
     *
     * @return array|boolean: an array which contains all Comment entities instances or false
     */
    public function getCommentListForSingle($postId)
    {
        $comments = [];
        $query = $this->dbConnector->prepare('SELECT *
                                              FROM comments
                                              WHERE comment_postId = ? AND comment_isPublished = 1
                                              ORDER BY comment_creationDate DESC');
        $query->bindParam(1, $postId, \PDO::PARAM_INT);
        $query->execute();
        while ($datas = $query->fetch(\PDO::FETCH_ASSOC)) {
            $comments[] = new Comment($datas);
        }
        if (isset($comments)) {
            return $comments;
        } else {
            return false;
        }
    }
}
