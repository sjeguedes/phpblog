<?php
namespace App\Controllers\Blog\Post;
use App\Controllers\BaseController;
use Core\Routing\AppRouter;
use Core\Service\AppContainer;

/**
 * Manage Posts appearence and actions on front-end
 */
class PostController extends BaseController
{
	/**
     * @var object: an instance of validator object
     */
    private $commentFormValidator;
    /**
     * @var string: dynamic index name for comment form token
     */
    private $pcfTokenIndex;
    /**
     * @var string: dynamic value for comment form token
     */
    private $pcfTokenValue;
    /**
     * @var object: an instance of captcha object
     */
    private $commentFormCaptcha;
    /**
     * @var array: an array of parameters to generate captcha user interface
     */
    private $captchaUIParams;

    /**
     * Constructor
     * @param AppRouter $router
     * @return void
     */
    public function __construct(AppRouter $router)
	{
		parent::__construct($router);
		$this->currentModel = $this->getCurrentModel(__CLASS__);
        // Initialize comment form validator
        $this->commentFormValidator = $this->container::getFormValidator()[1];
        // Define used parameters to avoid CSRF
        $this->pcfTokenIndex = $this->commentFormValidator->generateTokenIndex('pcf_check');
        $this->pcfTokenValue = $this->commentFormValidator->generateTokenValue('pcf_token');
        // Initialize comment form captcha
        $this->commentFormCaptcha = $this->container::getCaptcha()[1];
	}

	/**
     * Show all posts without paging
     * @return void
     */
    public function showList()
	{
		// Get posts datas
		$postList = $this->currentModel->getListWithAuthor();
        // Loop to find any existing comments attached to each post
        for ($i = 0; $i < count($postList); $i ++) {
            // Retrieve (or not) single post comments
            $postComments = $this->currentModel->getCommentListForSingle($postList[$i]->id);
            // Comments are found for a post.
            if ($postComments != false) {
                // Add temporary param "postComments" to object
                $postList[$i]->postComments = $postComments;
            }
            // Retrieve single post images
            $postImages = $this->currentModel->getImageListForSingle($postList[$i]->id);
            // Images are found for a post.
            if ($postImages != false) {
                // Add temporary param "postImages" to object
                $postList[$i]->postImages = $postImages;
            }
        }
		$varsArray = [
			'metaTitle' => 'Posts list',
			'metaDescription' => 'Here, you can follow our news and technical topics.',
			'imgBannerCSSClass' => 'post-list',
			'postList' => $postList
		];
		echo $this->page->renderTemplate('Blog/Post/post-list.tpl', $varsArray);
	}

	/**
     * Show all posts for a particular paging number
     * @param array $matches: an array which contains paging number to show
     * @return void
     */
    public function showListWithPaging($matches)
	{
		// Get page number to show with paging
		$currentPageId = $matches[0];
		// $currentPageId doesn't exist (string or int <= 0)
		if ((int) $currentPageId <= 0) {
			$this->httpResponse->set404ErrorResponse('Sorry this page doesn\'t exist! [Debug trace: reason is paging starts at number "1".]', $this->router);
            exit();
		} else {
            // Get published posts
            $publishedPostsList = $this->currentModel->getList();
            // Get total number of pages for paging
            $pagesQuantity = ceil(count($publishedPostsList) / $this->config::getParam('posts.postPerPage'));
            // $currentPageId value is too high!
            if ((int) $currentPageId > $pagesQuantity) {
                $this->httpResponse->set404ErrorResponse('Sorry this page doesn\'t exist! [Debug trace: reason is current page id > page quantity.]', $this->router);
                exit();
            }
			// Get posts datas for current page and get post Quantity to show per page
			$postListOnPage = $this->currentModel->getListByPaging($currentPageId, $this->config::getParam('posts.postPerPage'));
            // Loop to find any existing comments attached to each post
            for ($i = 0; $i < count($postListOnPage['postsOnPage']); $i ++) {
                // Retrieve (or not) single post comments
                $postComments = $this->currentModel->getCommentListForSingle($postListOnPage['postsOnPage'][$i]->id);
                // Comments are found for a post.
                if ($postComments != false) {
                    // Add temporary param "postComments" to object
                    $postListOnPage['postsOnPage'][$i]->postComments = $postComments;
                }
                // Retrieve single post images
                $postImages = $this->currentModel->getImageListForSingle($postListOnPage['postsOnPage'][$i]->id);
                // Images are found for a post.
                if ($postImages != false) {
                    // Add temporary param "postImages" to object
                    $postListOnPage['postsOnPage'][$i]->postImages = $postImages;
                }
            }
			// $currentPageId value is correct: render page with included posts!
			$varsArray = [
				'metaTitle' => 'Posts list',
				'metaDescription' => 'Here, you can follow our news and technical topics.',
				'imgBannerCSSClass' => 'post-list',
				'postListOnPage' => $postListOnPage
			];
			echo $this->page->renderTemplate('Blog/Post/post-list.tpl', $varsArray);
		}
	}

    /**
     * Render single post template with comment form
     * @param array $post: an array which contains a single post
     * @param array $checkedForm: an array which contains filtered values or an empty array
     * @return void
     */
    private function renderSingle($post, $checkedForm = []) {
        // Post is not published.
        if ((bool) $post[0]->isPublished === false) {
            $this->httpResponse->set404ErrorResponse($this->config::isDebug('The content you try to access doesn\'t exist! [Debug trace: reason is $post is not published]'), $this->router);
            exit();
        // Post is ready to be displayed in front-end.
        } else {
            // Retrieve single post comments
            $postComments = $this->currentModel->getCommentListForSingle($post[0]->id);
            // Retrieve single post images
            $postImages = $this->currentModel->getImageListForSingle($post[0]->id);
            // Prepare template vars
            $cssArray = [
                0 => [
                    'pluginName' => 'Slick Slider 1.8.1',
                    'src' => 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css'
                ],
                1 => [
                    'pluginName' => 'Slick Slider 1.8.1',
                    'src' => 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css'
                ]
            ];
            $jsArray = [
                0 => [
                    'pluginName' => 'Slick Slider 1.8.1',
                    'placement' => 'bottom',
                    'src' => 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js'
                ],
                1 => [
                    'placement' => 'bottom',
                    'src' => '/assets/js/phpblog.js'
                ],
                2 => [
                    'placement' => 'bottom',
                    'src' => '/assets/js/commentPost.js'
                ],
                3 => [
                    'placement' => 'bottom',
                    'src' => '/assets/js/postSingle.js'
                ]
            ];
            $varsArray = [
                'CSS' => $cssArray,
                'JS' => $jsArray,
                'metaTitle' => 'Post - ' . $post[0]->title,
                'metaDescription' => $post[0]->intro,
                'imgBannerCSSClass' => 'post-single',
                'post' => $post,
                'postComments' => $postComments,
                'postImages' => $postImages,
                // Get number of Comment entities to show per slide for post comments slider (paging slider)
                'commentPerSlide' => $this->config::getParam('singlePost.commentPerSlide'),
                'nickName' => isset($checkedForm['pcf_nickName']) ? $checkedForm['pcf_nickName'] : '',
                'title' => isset($checkedForm['pcf_title']) ? $checkedForm['pcf_title'] : '',
                'email' => isset($checkedForm['pcf_email']) ? $checkedForm['pcf_email'] : '',
                'content' => isset($checkedForm['pcf_content']) ? $checkedForm['pcf_content'] : '',
                'pcfTokenIndex' => $this->pcfTokenIndex,
                'pcfTokenValue' => $this->pcfTokenValue,
                'pcfNoSpam' => $this->captchaUIParams,
                'submit' => isset($_SESSION['pcf_success']) && $_SESSION['pcf_success'] ? 1 : 0,
                'tryValidation' => isset($_POST['pcf_submit']) ? 1 : 0,
                'errors' => isset($checkedForm['pcf_errors']) ? $checkedForm['pcf_errors'] : false,
                'success' => isset($_SESSION['pcf_success']) && $_SESSION['pcf_success'] ? true : false,
            ];
            // Render template
            echo $this->page->renderTemplate('Blog/Post/post-single.tpl', $varsArray);
        }
    }

    /**
     * Check if a single post exists
     * @param array $matches: an array of parameters which contains post id (and optionnaly post slug)
     * @return boolean|array: false or an array which contains a Post entity
     */
    private function checkSingle($matches)
    {
        switch (count($matches)) {
            case 1:
                 // $matches contains only id parameter
                $postSlug = null;
                $postId = (int) $matches[0];
                // Post id is valid!
                if ($postId > 0) {
                    // Post id exists in database
                    if ($this->currentModel->checkRowId('posts', 'post', $postId)) {
                        // Retrieve post slug
                        $postSlug = $this->currentModel->getSlug($postId);
                        // GET REQUEST METHOD redirections for post
                        if (isset($_GET['url']) && preg_match('#^/?post/\d+$#', $_GET['url'])) {
                            // Create a redirection from route which contains "/post/:id" to "/post/:slug-:id"
                            $this->httpResponse->addHeader('Status: 301 Moved Permanently');
                            $this->httpResponse->addHeader('Location: /post/' . $postSlug . '-' . $postId, true, 301);
                            exit();
                        } elseif (!isset($_POST['pcf_submit']) && isset($_GET['url']) && preg_match('#^/?comment-post/\d+$#', $_GET['url'])) {
                            // Create a redirection from route which contains "/comment-post/:id" to "/post/:slug-:id"
                            $this->httpResponse->addHeader('Status: 301 Moved Permanently');
                            $this->httpResponse->addHeader('Location: /post/' . $postSlug . '-' . $postId, true, 301);
                            exit();
                        }
                    }
                }
                break;
            case 2:
               // $matches contains slug and id parameters
                $postSlug = (string) $matches[0];
                $postId = (int) $matches[1];
                break;
        }
        // Wrong number of parameters or postId is not a valid id
        if (count($matches) > 2 || $postId <= 0) {
            $this->httpResponse->set404ErrorResponse($this->config::isDebug('The content you try to access doesn\'t exist! [Debug trace: reason is $count($matches) > 2 || $postId <= 0]'), $this->router);
            return false;
        } else {
            $post = $this->currentModel->getSingleWithAuthor($postId, $postSlug);
            if (!$post) {
                // No post was found because visitor tries to use wrong parameters!
                $this->httpResponse->set404ErrorResponse($this->config::isDebug('The content you try to access doesn\'t exist! [Debug trace: reason is !$post]'), $this->router);
                return false;
            } else {
                // A post exists.
                return $post;
            }
        }
    }

    /**
     * Check if there is already a success state for comment form
     * @return boolean
     */
    private function isCommentSuccess() {
        if(isset($_SESSION['pcf_success'])) {
            return true;
        }
        else {
            return false;
        }
    }

	/**
     * Show single post page template
     * @param array $matches: an array of parameters catched by router
     * @return void
     */
    public function showSingle($matches)
	{
        // Check if single post exists
        $post = $this->checkSingle($matches);
        if ($post !== false && is_array($post)) {
            // Set captcha values with initial values
            $this->commentFormCaptcha->call(['customized' => [0 => 'setNoSpamFormValues']]);
            // Set captcha user interface
            $this->captchaUIParams = $this->commentFormCaptcha->call(['customized' => [0 => 'setNoSpamFormElements']]);
            // Show template only: call template with a method for more flexibility
            $this->renderSingle($post);
        }
        // Is it already a succcess state for comment form?
        if ($this->isCommentSuccess()) {
            unset($_SESSION['pcf_success']);
        }
	}

    /**
     * Show comment form validation try (on submission) template
     * on single post page
     * @param array $matches: an array of parameters catched by router
     * @return void
     */
    public function commentPost($matches)
    {
        // Check if single post exists
        $post = $this->checkSingle($matches);
        if ($post !== false && is_array($post)) {
            // Set captcha values with submitted values
            $this->commentFormCaptcha->call(['customized' => [0 => 'setNoSpamFormValues', 1 => [$_POST]]]);
            // Set captcha user interface
            $this->captchaUIParams = $this->commentFormCaptcha->call(['customized' => [0 => 'setNoSpamFormElements']]);
            // Store result from comment form validation
            $checkedForm = $this->validateCommentForm();
            // Is it already a succcess state?
            if ($this->isCommentSuccess()) {
                // Success state is returned: avoid previous $_POST with a redirection.
                $this->httpResponse->addHeader('Location: /post/' . $post[0]->getSlug(). '-' . $post[0]->getId());
                exit();
            } else {
                // Call template with a method for more flexibility
                $this->renderSingle($post, $checkedForm);
            }
        }
    }

    /**
     * Validate (or not) comment form on single post page
     * @return array: an array which contains result of validation (error on fields, filtered form values, ...)
     */
    private function validateCommentForm()
    {
        // Prepare filters for form datas
        $datas = [
            0 => ['name' => 'nickName', 'filter' => 'alphanum', 'modifiers' => ['trimStr', 'ucfirstStr']],
            1 => ['name' => 'email', 'filter' => 'email', 'modifiers' => ['trimStr']],
            2 => ['name' => 'title', 'filter' => 'alphanum', 'modifiers' => ['trimStr', 'ucfirstStr']],
            3 => ['name' => 'content', 'filter' => 'alphanum', 'modifiers' => ['trimStr', 'ucfirstStr']]
        ];
        // Filter user inputs in $_POST datas
        $this->commentFormValidator->filterDatas($datas);
        // Nickname
        $this->commentFormValidator->validateRequired('nickName', 'nickname');
        // Email
        $this->commentFormValidator->validateEmail('email', 'email', $_POST['pcf_email']);
        // Title
        $this->commentFormValidator->validateRequired('title', 'title');
        // Content
        $this->commentFormValidator->validateRequired('content', 'comment');
        // Check token to avoid CSRF
        $this->commentFormValidator->validateToken(isset($_POST[$this->pcfTokenIndex]) ? $_POST[$this->pcfTokenIndex] : false);
        // Get validation result without captcha
        $result = $this->commentFormValidator->getResult();
        // Update validation result with "no spam tools" captcha antispam validation
        $result = $this->commentFormCaptcha->call([$result, 'pcf_errors']);
        // Submit: comment form is correctly filled.
        if (isset($result) && empty($result['pcf_errors']) && isset($result['pcf_noSpam']) && $result['pcf_noSpam'] && isset($result['pcf_check']) && $result['pcf_check']) {
            // Insert Comment entity in database
            try {
                // Check post id used in form
                // Is there an existing post with this id? User can change hidden input value!
                if ($this->currentModel->getSingleById($_POST['pcf_postId']) != false) {
                    $result['pcf_postId'] =  $_POST['pcf_postId'];
                    $this->currentModel->insertComment($result);
                    $insertion = true;
                } else {
                    $result['pcf_errors']['pcf_unsaved'] = $this->config::isDebug('<span class="form-check-notice">Sorry a technical error happened! Your comment was not saved: please try again later.<br>[Debug trace: id "<strong>' . $_POST['pcf_postId'] . '</strong>" doesn\'t exist in database!]</span>');
                    $insertion = false;
                }
            } catch (\PDOException $e) {
                $result['pcf_errors']['pcf_unsaved'] = $this->config::isDebug('<span class="form-check-notice">Sorry a technical error happened! Your comment was not saved: please try again later.<br>[Debug trace: <strong>' . $e->getMessage() . '</strong>]</span>');
                $insertion = false;
            }
            // Comment entity was saved successfuly!
            if ($insertion) {
                // Reset the form
                $result = [];
                // Delete current token
                unset($_SESSION['pcf_check']);
                unset($_SESSION['pcf_token']);
                // Regenerate token to be updated in form
                $this->pcfTokenIndex = $this->commentFormValidator->generateTokenIndex('pcf_check');
                $this->pcfTokenValue = $this->commentFormValidator->generateTokenValue('pcf_token');
                // Show success message
                $_SESSION['pcf_success'] = true;
            }
        }
        // Update datas in form, error messages near fields, and notice error/success message
        return $result;
    }
}