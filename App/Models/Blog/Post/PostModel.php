<?php
namespace App\Models\Blog\Post;
use App\Models\BaseModel;
use Core\Database\AppDatabase;
use Core\AppHTTPResponse;
use Core\Routing\AppRouter;
use Core\Config\AppConfig;
use App\Models\Blog\Entity\Post;
use App\Models\Admin\Entity\User;


class PostModel extends BaseModel
{
	/**
	 * Constructor
	 * @param object: HTTPResponse instance
	 * @param object: AppRouter instance
	 */
	public function __construct(AppHTTPResponse $httpResponse, AppRouter $router)
	{
		parent::__construct(AppDatabase::getInstance(), $httpResponse, $router);
	}

	public function getSingleById($postId)
	{
	    $postIsOnPage = $this->getPagingForSingle($postId); 

	    $query = $this->dbConnector->prepare('SELECT *							 
	    									  FROM posts
	    									  WHERE post_id = :postId');
	    $query->bindParam(':postId', $postId, \PDO::PARAM_INT);
	    $query->execute();
	    $datas = $query->fetch(\PDO::FETCH_ASSOC);
	    $post = new Post($datas);
	    $post->pagingNumber = $postIsOnPage;
	    return $post;
	}

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
		$post->author = $author;
		array_push($postWithAuthor, $post);
		//var_dump('WITH AUTHOR', $postWithAuthor);
		return $postWithAuthor;
	}

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

		// Get rank for all the posts: return an array of arrays (with post_id and rank)
		//$postsRank = $this->getRankForAll();
		//var_dump($postsRank);

		// Compare post_id from retrieved posts to add rank property to corresponding Post instance
		while($datas = $query->fetch(\PDO::FETCH_ASSOC)) {
			// for($i = 0; $i < count($postsRank) - 1; $i++) {
			// 	if($postsRank[$i]['post_id'] == $datas['post_id']) {
			// 		$datas['rank'] = $postsRank[$i]['rank'];
			// 	}
			// }
	      	$postsOnpage[] = new Post($datas);
	    }

		// Get the same result like COUNT() function:
		$query2 = $this->dbConnector->query('SELECT FOUND_ROWS()');
		// Get total number of posts in database
		$countedPosts = $query2->fetchColumn(); 
		// Get total number of pages for paging
		$pagesQuantity = ceil($countedPosts / $postPerPage);

	    //var_dump('$pagesQuantity:', $pagesQuantity, '$postsOnpage', $postsOnpage);
    	return ['currentPage' => $pageId, 'pageQuantity' => $pagesQuantity, 'postsOnPage' => $postsOnpage];
	}

	public function getPagingForSingle($postId)
	{
		$result = $this->getRankForSingle($postId);
		$postRank = intval($result[0]);
		$postQuantity = intval($result[1]);
		$postPerPage = $this->config::POST_PER_PAGE;
		$paging = ceil($postQuantity / $postPerPage);

		$interval = [];
		$start = 0;
		//var_dump('$postRank', $postRank, '$postQuantity', $postQuantity);
		for($i = 1; $i <= $paging; $i++) {
			//var_dump('s', $start, 's+p', $start + $postPerPage);
			if($start <= $postRank && $postRank < $start + $postPerPage) {
				$singleIsOnPage = $i;
				break;
			}
			$start = $start + $postPerPage;
		}
		//var_dump('getPagingForSingle', $singleIsOnPage);
		return $singleIsOnPage;
	}

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

	public function getAuthorByPostUserId($postUserId)
	{
	    $query = $this->dbConnector->prepare('SELECT u.user_id, u.user_firstname, u.user_name, u.user_pseudo, u.user_email,
	    									  u.user_password, u.user_isActivated, u.user_userTypeId			 
	    									  FROM posts p
	    									  INNER JOIN users u ON (p.post_userId = u.user_id)
	    									  WHERE p.post_userId = :postUserId');
	    $query->bindParam(':postUserId', $postUserId, \PDO::PARAM_INT);
	    $query->execute();
	    $datas = $query->fetch(\PDO::FETCH_ASSOC);
	    return new User($datas);
	}

	public function getListWithAuthor()
	{
		// Will associate author datas for each post
		$postsWithAuthor = [];
		$posts = $this->getList();
		foreach ($posts as $post) {
			$author = $this->getAuthorByPostUserId($post->userId);
			// Get and store user who is also an author for each post
			$post->author = $author;
			array_push($postsWithAuthor, $post);
		}
		return $postsWithAuthor;
	}
}