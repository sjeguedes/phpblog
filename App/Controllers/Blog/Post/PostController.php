<?php
namespace App\Controllers\Blog\Post;
use App\Controllers\BaseController;
use Core\AppPage;
use Core\AppHTTPResponse;
use Core\Routing\AppRouter;
use Core\Config\AppConfig;
/**
 *
 */
class PostController extends BaseController
{
	public function __construct(AppHTTPResponse $httpResponse, AppRouter $router)
	{
		parent::__construct(new AppPage(), $httpResponse, $router);
		$this->currentModel = $this->getCurrentModel(__CLASS__);
	}

	public function showList()
	{
		// Get posts datas
		$postList = $this->currentModel->getListWithAuthor();

		$varsArray = [
			'metaTitle' => 'Posts list',
			'metaDescription' => 'Here, you can follow our news and technical topics.',
			'imgBannerCSSClass' => 'post-list',
			'postList' => $postList
		];
		echo $this->page->renderTemplate('blog/post/post-list.tpl', $varsArray);
	}

	public function showListWithPaging($matches)
	{
		// Get page number to show with paging
		$currentPageId = $matches[0];

		// $currentPageId doesn't exist (string or int < 0)
		if((int) $currentPageId <= 0) {
			echo $this->httpResponse->set404ErrorResponse('Sorry this page doesn\'t exist!', $this->router);
		}
		else {
			// Get posts datas for current page and get post Quantity to show per page
			$postListOnPage = $this->currentModel->getListByPaging($currentPageId, $this->config::POST_PER_PAGE);

			// $currentPageId value is correct!
			if($currentPageId <= $postListOnPage['pageQuantity']) {
				$varsArray = [
					'metaTitle' => 'Posts list',
					'metaDescription' => 'Here, you can follow our news and technical topics.',
					'imgBannerCSSClass' => 'post-list',
					'postListOnPage' => $postListOnPage
				];
				echo $this->page->renderTemplate('blog/post/post-list.tpl', $varsArray);
			}
			// $currentPageId value is too high!
			else {
				echo $this->httpResponse->set404ErrorResponse($this->config::isDebug('The content you try to access doesn\'t exist! [Debug trace: reason is $currentPageId > $postListOnPage["pageQuantity"] ]'), $this->router);
			}
		}
	}

	public function showSingle($matches)
	{
		switch(count($matches)) {
			case 1:
				// $matches contains slug and id parameters
				$postSlug = null;
				$postId = (int) $matches[0];
				break;
			case 2:
				// $matches contains only id parameter
				$postSlug = (string) $matches[0];
				$postId = (int) $matches[1];
				break;
		}

		if(count($matches) > 2 || $postId <= 0) {
			echo $this->httpResponse->set404ErrorResponse($this->config::isDebug('The content you try to access doesn\'t exist! [Debug trace: reason is $count($matches) > 2 || $postId <= 0 ]'), $this->router);
		}
		else {
			$post = $this->currentModel->getSingleWithAuthor($postId, $postSlug);
			// No post was found because visitor tries to use wrong parameters!
			if(!$post) {
				echo $this->httpResponse->set404ErrorResponse($this->config::isDebug('The content you try to access doesn\'t exist! [Debug trace: reason is !$post ]'), $this->router);
			}
			// A post exists.
			else {
				//$postListWithPagingURL = $this->router->useURL('Blog\Post\Post|showListWithPaging', ['pageId' => $post[0]->temporaryParams['pagingNumber']]);
				//$adminUpdatePostURL = $this->router->useURL('Admin\Post\AdminPost|updatePost', ['id' => $post[0]->id]);
				
				$varsArray = [
					'metaTitle' => 'Post - ' . $post[0]->title,
					'metaDescription' => $post[0]->intro,
					'imgBannerCSSClass' => 'post-single',
					'post' => $post,
					//'postListWithPagingURL' => $postListWithPagingURL,
					//'adminUpdatePostURL' => $adminUpdatePostURL
				];
				echo $this->page->renderTemplate('blog/post/post-single.tpl', $varsArray);
			}
		}
	}
}