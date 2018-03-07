<?php
namespace App\Models\Blog\Post;
use App\Models\BaseModel;
use Core\Database\AppDatabase;
use Core\Config\AppConfig;
use App\Models\Blog\Entity\Post;
use App\Models\Admin\Entity\User;
use App\Models\Admin\Entity\Comment;

/**
 * Create a class for front-end posts queries
 */
class PostModel extends BaseModel
{
	/**
     * Constructor
     * @param AppConfig $config: an instance of AppConfig
     * @return void
     */
    public function __construct(AppConfig $config)
    {
        parent::__construct(AppDatabase::getInstance(), $config);
    }

    /**
     * Get a post slug with its id
     * @param string $postId
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
     * @param string $postId
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
            $postIsOnPage = $this->getPagingForSingle($postId);
            // Temporary parameter is created here:
            $post->pagingNumber = $postIsOnPage;
	       return $post;
        } else {
            return false;
        }
	}

    /**
     * Get a single post with its slug
     * @param string $postSlug
     * @return object: a Post entity instance
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
	    if(!$datas) {
	    	return false;
	    }
	    return new Post($datas);
	}

    /**
     * Get a single post with its author datas
     * @param string $postId
     * @param string|null $postSlug
     * @return array: an array which contains a Post entity instance
     */
	public function getSingleWithAuthor($postId, $postSlug = null)
	{
		// Check if $postSlug exists in database
		if(!is_null($postSlug)) {
			$isSlug = $this->getSingleBySlug($postSlug);
			if(!$isSlug) {
				return false;
			}
		}
		// Will associate author datas for each post
		$postWithAuthor = [];
		$post = $this->getSingleById($postId);
		// Get and store user who is also an author for current post
		$author = $this->getAuthorByPostUserId($post->userId);
        // Temporary parameter is created here:
		$post->author = $author;
		array_push($postWithAuthor, $post);
		return $postWithAuthor;
	}

    /**
     * Get all Post entities
     * @return array: an array which contains all Post entities instances
     */
	public function getList()
	{
		$posts = [];
    	$query = $this->dbConnector->query('SELECT *
    										FROM posts
    										ORDER BY post_creationDate DESC');
	    while($datas = $query->fetch(\PDO::FETCH_ASSOC)) {
	      	$posts[] = new Post($datas);
	    }
    	return $posts;
	}

    /**
     * Get posts for a particular paging number: result depends on post per page quantity.
     * @param int $pageId
     * @param int $postPerPage
     * @return array: an array of needed parameters with list of posts on a particular page
     */
	public function getListByPaging($pageId, $postPerPage)
	{
		// Get first post row to show for group of posts which appears on selected page : must begin to 0 on first page ($pageId = 1)
		$start = ($pageId - 1) * $postPerPage;
		$i = 0;
		$postsOnpage = [];
		// SQL_CALC_FOUND_ROWS (MySQL 4+) also stores total number of rows (ignores LIMIT) with one query (to avoid secondary query with COUNT()!). OFFSET is more readable.
		$query = $this->dbConnector->prepare('SELECT SQL_CALC_FOUND_ROWS *
											  FROM posts
											  ORDER BY post_creationDate DESC
											  LIMIT /*:start, :postPerPage*/:postPerPage OFFSET :start');
		$query->bindParam(':start', $start, \PDO::PARAM_INT);
		$query->bindParam(':postPerPage', $postPerPage, \PDO::PARAM_INT);
		$query->execute();
		// Compare post_id from retrieved posts to add rank property to corresponding Post instance
		while($datas = $query->fetch(\PDO::FETCH_ASSOC)) {
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
     * @param string $postId
     * @return int: paging number to retrieve
     */
	public function getPagingForSingle($postId)
	{
		$result = $this->getRankForSingle($postId);
		$postRank = intval($result[0]);
		$postQuantity = intval($result[1]);
		$postPerPage = $this->config::getParam('posts.postPerPage');
		$paging = ceil($postQuantity / $postPerPage);
		$interval = [];
		$start = 0;
		for($i = 1; $i <= $paging; $i++) {
			if($start <= $postRank && $postRank < $start + $postPerPage) {
				$singleIsOnPage = $i;
				break;
			}
			$start = $start + $postPerPage;
		}
		return $singleIsOnPage;
	}

    /**
     * Get rank for a particular post
     * @param string $postId
     * @return array: an array which contains retrieved post rank, and total quantity of posts in database
     */
	public function getRankForSingle($postId)
	{
		$postsRank = $this->getRankForAll();
		for($i = 0; $i < count($postsRank) - 1; $i++) {
			if($postsRank[$i]['post_id'] == $postId) {
				$singleRank = $postsRank[$i]['rank'];
				break;
			}
		}
		return [$singleRank, $postsRank['total']];
	}

    /**
     * Get rank for each post
     * @return array: an array which contains post id and rank for each post and total quantity of posts in database
     */
	public function getRankForAll()
	{
		$postsRank = [];
		$i = 0;
    	$query = $this->dbConnector->query('SELECT SQL_CALC_FOUND_ROWS p.post_id, (@curRank := @curRank + 1) AS rank
    										FROM posts p, (SELECT @curRank := -1) r
    										ORDER BY p.post_creationDate DESC');
	    while($datas = $query->fetch(\PDO::FETCH_ASSOC)) {
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
     * Get author infos with its post user id
     * @param string $postUserId
     * @return object: a User entity instance
     */
	public function getAuthorByPostUserId($postUserId)
	{
	    $query = $this->dbConnector->prepare('SELECT u.user_id, u.user_firstName, u.user_familyName, u.user_pseudo, u.user_email,
	    									  u.user_password, u.user_isActivated, u.user_userTypeId
	    									  FROM posts p
	    									  INNER JOIN users u ON (p.post_userId = u.user_id)
	    									  WHERE p.post_userId = :postUserId');
	    $query->bindParam(':postUserId', $postUserId, \PDO::PARAM_INT);
	    $query->execute();
	    $datas = $query->fetch(\PDO::FETCH_ASSOC);
	    return new User($datas);
	}

    /**
     * Get all post with their author infos
     * @return array: an array which contains all Post entities with their author infos
     */
	public function getListWithAuthor()
	{
		// Will associate author datas for each post
		$postsWithAuthor = [];
		$posts = $this->getList();
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
     * @param array $commentDatas: an array of post comment datas
     * @return void
     */
    public function insertComment($commentDatas)
    {
        // Secure query
        $query = $this->dbConnector->prepare('INSERT INTO comments
                                              (comment_creationDate, comment_nickName, comment_email, comment_title, comment_content, comment_postId)
                                              VALUES (NOW(), ?, ?, ?, ?, ?)');
        $query->bindParam(1, $nickName);
        $query->bindParam(2, $email);
        $query->bindParam(3, $title);
        $query->bindParam(4, $content);
        $query->bindParam(5, $postId);

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
     * @param string $commentId
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
     * Get all Comment entities for a particular post
     * @param string $postId
     * @return array|boolean: an array which contains all Comment entities instances or false
     */
    public function getCommentListForSingle($postId)
    {
        $comments = [];
        $query = $this->dbConnector->prepare('SELECT *
                                              FROM comments
                                              WHERE comment_postId = ?
                                              ORDER BY comment_creationDate DESC');
        $query->bindParam(1, $postId, \PDO::PARAM_INT);
        $query->execute();
        while($datas = $query->fetch(\PDO::FETCH_ASSOC)) {
            $comments[] = new Comment($datas);
        }
        if(!isset($comments)) {
            return false;
        }
        return $comments;
    }
}