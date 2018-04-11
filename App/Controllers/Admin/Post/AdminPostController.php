<?php
namespace App\Controllers\Admin\Post;
use App\Controllers\Admin\AdminController;
use Core\Routing\AppRouter;
use Core\Service\AppContainer;

/**
 * Manage all actions as concerns Post entity in back-end
 */
class AdminPostController extends AdminController
{
	/**
     * @var object: an instance of validator object
     */
    private $adminPostValidator;
    /**
     * @var string: dynamic index name for deleting post form token
     */
    private $ppdTokenIndex;
    /**
     * @var string: dynamic value for deleting post form token
     */
    private $ppdTokenValue;
    /**
     * @var string: dynamic index name for validation post form token
     */
    private $ppvTokenIndex;
    /**
     * @var string: dynamic value for validation post form token
     */
    private $ppvTokenValue;
    /**
     * @var string: dynamic index name for publication post form token
     */
    private $pppTokenIndex;
    /**
     * @var string: dynamic value for publication post form token
     */
    private $pppTokenValue;
    /**
     * @var string: dynamic index name for publication cancelation post form token
     */
    private $ppuTokenIndex;
    /**
     * @var string: dynamic value for publication cancelation post form token
     */
    private $ppuTokenValue;
    /**
     * @var string: dynamic index name for deleting comment form token
     */
    private $pcdTokenIndex;
    /**
     * @var string: dynamic value for deleting comment form token
     */
    private $pcdTokenValue;
    /**
     * @var string: dynamic index name for validation comment form token
     */
    private $pcvTokenIndex;
    /**
     * @var string: dynamic value for validation comment form token
     */
    private $pcvTokenValue;
    /**
     * @var string: dynamic index name for publication comment form token
     */
    private $pcpTokenIndex;
    /**
     * @var string: dynamic value for publication comment form token
     */
    private $pcpTokenValue;
    /**
     * @var string: dynamic index name for publication cancelation comment form token
     */
    private $pcuTokenIndex;
    /**
     * @var string: dynamic value for publication cancelation comment form token
     */
    private $pcuTokenValue;

    /**
     * Constructor
     * @param AppRouter $router
     * @return void
     */
    public function __construct(AppRouter $router)
    {
        parent::__construct($router);
		$this->currentModel = $this->getCurrentModel(__CLASS__);
        // Initialize home admin forms validator
        $this->adminPostValidator = $this->container::getFormValidator()[3];
        // Define used parameters to avoid CSRF:
        // Post deleting token
        $this->ppdTokenIndex = $this->adminPostValidator->generateTokenIndex('ppd_check');
        $this->ppdTokenValue = $this->adminPostValidator->generateTokenValue('ppd_token');
        // Post validation token
        $this->ppvTokenIndex = $this->adminPostValidator->generateTokenIndex('ppv_check');
        $this->ppvTokenValue = $this->adminPostValidator->generateTokenValue('ppv_token');
        // Post publication token
        $this->pppTokenIndex = $this->adminPostValidator->generateTokenIndex('ppp_check');
        $this->pppTokenValue = $this->adminPostValidator->generateTokenValue('ppp_token');
        // Post publication cancelation token
        $this->ppuTokenIndex = $this->adminPostValidator->generateTokenIndex('ppu_check');
        $this->ppuTokenValue = $this->adminPostValidator->generateTokenValue('ppu_token');
        // Comment deleting token
        $this->pcdTokenIndex = $this->adminPostValidator->generateTokenIndex('pcd_check');
        $this->pcdTokenValue = $this->adminPostValidator->generateTokenValue('pcd_token');
        // Comment validation token
        $this->pcvTokenIndex = $this->adminPostValidator->generateTokenIndex('pcv_check');
        $this->pcvTokenValue = $this->adminPostValidator->generateTokenValue('pcv_token');
        // Comment publication token
        $this->pcpTokenIndex = $this->adminPostValidator->generateTokenIndex('pcp_check');
        $this->pcpTokenValue = $this->adminPostValidator->generateTokenValue('pcp_token');
        // Comment publication cancelation token
        $this->pcuTokenIndex = $this->adminPostValidator->generateTokenIndex('pcu_check');
        $this->pcuTokenValue = $this->adminPostValidator->generateTokenValue('pcu_token');
	}

    /**
     * Initialize default template parameters
     * @return array: an array of template parameters
     */
    private function initAdminPosts()
    {
        // Get all comment entities
        $commentList = $this->currentModel->getCommentList();
        // Get all posts (author data included) with external model (PostModel)
        $postList = $this->currentModel->getPostListWithAuthor();
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
                'src' => '/assets/js/adminPosts.js'
            ]
        ];
        return [
            'CSS' => $cssArray,
            'JS' => $jsArray,
            'metaTitle' => 'Admin posts',
            'metaDescription' => 'Here, you can have a look at all the essential administration information and functionalities for posts.',
            'metaRobots' => 'noindex, nofollow',
            'imgBannerCSSClass' => 'admin-post',
            // Get complete list of each entity type
            'commentList' => $commentList,
            'postList' => $postList,
            // Get number of entities to show per slide for each slider (paging sliders)
            'commentPerSlide' => $this->config::getParam('admin.posts.commentPerSlide'),
            'postPerSlide' => $this->config::getParam('admin.posts.postPerSlide'),
            // Deleting token for Post entity
            'ppdTokenIndex' => $this->ppdTokenIndex,
            'ppdTokenValue' => $this->ppdTokenValue,
            // Validation token for Post entity
            'ppvTokenIndex' => $this->ppvTokenIndex,
            'ppvTokenValue' => $this->ppvTokenValue,
            // Publication token for Post entity
            'pppTokenIndex' => $this->pppTokenIndex,
            'pppTokenValue' => $this->pppTokenValue,
            // Publication cancelation token for Post entity
            'ppuTokenIndex' => $this->ppuTokenIndex,
            'ppuTokenValue' => $this->ppuTokenValue,
            // Deleting token for Comment entity
            'pcdTokenIndex' => $this->pcdTokenIndex,
            'pcdTokenValue' => $this->pcdTokenValue,
            // Validation token for Comment entity
            'pcvTokenIndex' => $this->pcvTokenIndex,
            'pcvTokenValue' => $this->pcvTokenValue,
            // Publication token for Comment entity
            'pcpTokenIndex' => $this->pcpTokenIndex,
            'pcpTokenValue' => $this->pcpTokenValue,
            // Publication cancelation token for Comment entity
            'pcuTokenIndex' => $this->pcuTokenIndex,
            'pcuTokenValue' => $this->pcuTokenValue,
            // Error messages notice (only updated in actions)
            'errors' => false,
            // Update success state for each type of form after success redirection
            'success' => isset($_SESSION['paf_success']) ? $_SESSION['paf_success'] : false
        ];
    }

    /**
     * Render admin posts template (template based on Twig template engine)
     * @param array $vars: an array of template engine parameters
     * @return void
     */
    private function renderAdminPosts($vars)
    {
        echo $this->page->renderTemplate('Admin/admin-posts.tpl', $vars);
    }

    /**
     * Check if there is already a success state for one of admin posts forms
     * @return boolean
     */
    private function isActionSuccess() {
        if (isset($_SESSION['paf_success'])) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Show default admin posts template
     * @return void
     */
    public function showAdminPosts()
    {
        $varsArray = $this->initAdminPosts();
        $this->renderAdminPosts($varsArray);
        // Is it already a succcess state for one of admin posts forms?
        if ($this->isActionSuccess()) {
            // Do not store a success state anymore!
            unset($_SESSION['paf_success']);
        }
    }

    /**
     * Delete a Post entity in database
     * @param array $matches: an array of parameters matched in route
     * @return void
     */
    public function deletePost($matches)
    {
        $varsArray = $this->initAdminPosts();
        $paramsArray = [
            'tokenIdentifier' => 'ppd',
            'tokenIndex' => $this->ppdTokenIndex,
            'action' => 'delete',
            'errorMessage' => 'Deleting action was not performed correctly<br>as concerns post #',
            'successMessage' => 'Deleting action was performed successfully<br>as concerns post #',
            'datas' => ['entity' => 'post'],
        ];
        // Validate or not form datas
        $checkedForm = $this->validateEntityForms($paramsArray, $this->adminPostValidator, '/admin/posts');
        // Reset form token immediately after success state
        // This can not be made directly in "validateEntityForms()" because of private properties
        if ($this->isActionSuccess()) {
            // Delete current token
            unset($_SESSION['ppd_check']);
            unset($_SESSION['ppd_token']);
            // Regenerate token to be updated in forms
            $this->ppdTokenIndex = $this->adminPostValidator->generateTokenIndex('ppd_check');
            $this->ppdTokenValue = $this->adminPostValidator->generateTokenValue('ppd_token');
        }
        // Remind current paging slide item
        $varsArray['slideRankAfterSubmit'] = isset($_POST['ppd_slide_rank']) && (int) $_POST['ppd_slide_rank'] !== 0 ? $_POST['ppd_slide_rank'] : 1;
        // Need to update errors template var, while there is no redirection to admin posts (success state)
        $varsArray['errors'] = isset($checkedForm['paf_errors']) ? $checkedForm['paf_errors'] : false;
        // Render template with updated vars
        $this->renderAdminPosts($varsArray);
    }

    /**
     * Validate (moderate) a Post entity changing its state in database
     * @param array $matches: an array of parameters matched in route
     * @return void
     */
    public function validatePost($matches)
    {
        // Initialize necessary vars for admin posts
        $varsArray = $this->initAdminPosts();
        // Prepare params for form validation
        $paramsArray = [
            'tokenIdentifier' => 'ppv',
            'tokenIndex' => $this->ppvTokenIndex,
            'action' => 'update',
            'errorMessage' => 'Validation action was not performed correctly<br>as concerns post #',
            'successMessage' => 'Validation action was performed successfully<br>as concerns post #',
            'datas' => [
                'entity' => 'post',
                'values' => [
                    0 => [
                        'type' => 1, // int
                        'column' => 'isValidated',
                        'value' => 1 // true
                    ]
                ]
            ]
        ];
        // Validate or not form datas
        $checkedForm = $this->validateEntityForms($paramsArray, $this->adminPostValidator, '/admin/posts');
        // Reset form token immediately after success state
        // This can not be made directly in "validateEntityForms()" because of private properties
        if ($this->isActionSuccess()) {
            // Delete current token
            unset($_SESSION['ppv_check']);
            unset($_SESSION['ppv_token']);
            // Regenerate token to be updated in forms
            $this->ppvTokenIndex = $this->adminPostValidator->generateTokenIndex('ppv_check');
            $this->ppvTokenValue = $this->adminPostValidator->generateTokenValue('ppv_token');
        }
        // Remind current paging slide item
        $varsArray['slideRankAfterSubmit'] = isset($_POST['ppv_slide_rank']) && (int) $_POST['ppv_slide_rank'] !== 0 ? $_POST['ppv_slide_rank'] : 1;
        // Need to update errors template var, while there is no redirection to admin posts (success state)
        $varsArray['errors'] = isset($checkedForm['paf_errors']) ? $checkedForm['paf_errors'] : false;
        // Render template with updated vars
        $this->renderAdminPosts($varsArray);
    }

    /**
     * Publish a Post entity changing its state in database
     * @param array $matches: an array of parameters matched in route
     * @return void
     */
    public function publishPost($matches)
    {
        // Initialize necessary vars for admin posts
        $varsArray = $this->initAdminPosts();
        // Prepare params for form validation
        $paramsArray = [
            'tokenIdentifier' => 'ppp',
            'tokenIndex' => $this->pppTokenIndex,
            'action' => 'update',
            'errorMessage' => 'Publication action was not performed correctly<br>as concerns post #',
            'successMessage' => 'Publication action was performed successfully<br>as concerns post #',
            'datas' => [
                'entity' => 'post',
                'values' => [
                    0 => [
                        'type' => 1, // int
                        'column' => 'isPublished',
                        'value' => 1 // true
                    ]
                ]
            ]
        ];
        // Validate or not form datas
        $checkedForm = $this->validateEntityForms($paramsArray, $this->adminPostValidator, '/admin/posts');
        // Reset form token immediately after success state
        // This can not be made directly in "validateEntityForms()" because of private properties
        if ($this->isActionSuccess()) {
            // Delete current token
            unset($_SESSION['ppp_check']);
            unset($_SESSION['ppp_token']);
            // Regenerate token to be updated in forms
            $this->pppTokenIndex = $this->adminPostValidator->generateTokenIndex('ppp_check');
            $this->pppTokenValue = $this->adminPostValidator->generateTokenValue('ppp_token');
        }
        // Remind current paging slide item
        $varsArray['slideRankAfterSubmit'] = isset($_POST['ppp_slide_rank']) && (int) $_POST['ppp_slide_rank'] !== 0 ? $_POST['ppp_slide_rank'] : 1;
        // Need to update errors template var, while there is no redirection to admin posts (success state)
        $varsArray['errors'] = isset($checkedForm['paf_errors']) ? $checkedForm['paf_errors'] : false;
        // Render template with updated vars
        $this->renderAdminPosts($varsArray);
    }

    /**
     * Cancel publication for Post entity changing its state in database
     * @param array $matches: an array of parameters matched in route
     * @return void
     */
    public function unpublishPost($matches)
    {
        // Initialize necessary vars for admin posts
        $varsArray = $this->initAdminPosts();
        // Prepare params for form validation
        $paramsArray = [
            'tokenIdentifier' => 'ppu',
            'tokenIndex' => $this->ppuTokenIndex,
            'action' => 'update',
            'errorMessage' => 'Publication cancelation was not performed correctly<br>as concerns post #',
            'successMessage' => 'Publication cancelation was performed successfully<br>as concerns post #',
            'datas' => [
                'entity' => 'post',
                'values' => [
                    0 => [
                        'type' => 1, // int
                        'column' => 'isPublished',
                        'value' => 0 // false
                    ]
                ]
            ]
        ];
        // Validate or not form datas
        $checkedForm = $this->validateEntityForms($paramsArray, $this->adminPostValidator, '/admin/posts');
        // Reset form token immediately after success state
        // This can not be made directly in "validateEntityForms()" because of private properties
        if ($this->isActionSuccess()) {
            // Delete current token
            unset($_SESSION['ppu_check']);
            unset($_SESSION['ppu_token']);
            // Regenerate token to be updated in forms
            $this->ppuTokenIndex = $this->adminPostValidator->generateTokenIndex('ppu_check');
            $this->ppuTokenValue = $this->adminPostValidator->generateTokenValue('ppu_token');
        }
        // Remind current paging slide item
        $varsArray['slideRankAfterSubmit'] = isset($_POST['ppu_slide_rank']) && (int) $_POST['ppu_slide_rank'] !== 0 ? $_POST['ppu_slide_rank'] : 1;
        // Need to update errors template var, while there is no redirection to admin posts (success state)
        $varsArray['errors'] = isset($checkedForm['paf_errors']) ? $checkedForm['paf_errors'] : false;
        // Render template with updated vars
        $this->renderAdminPosts($varsArray);
    }

    /**
     * Delete a Comment entity in database
     * @param array $matches: an array of parameters matched in route
     * @return void
     */
    public function deleteComment($matches)
    {
        $varsArray = $this->initAdminPosts();
        $paramsArray = [
            'tokenIdentifier' => 'pcd',
            'tokenIndex' => $this->pcdTokenIndex,
            'action' => 'delete',
            'errorMessage' => 'Deleting action was not performed correctly<br>as concerns comment #',
            'successMessage' => 'Deleting action was performed successfully<br>as concerns comment #',
            'datas' => ['entity' => 'comment'],
        ];
        // Validate or not form datas
        $checkedForm = $this->validateEntityForms($paramsArray, $this->adminPostValidator, '/admin/posts');
        // Reset form token immediately after success state
        // This can not be made directly in "validateEntityForms()" because of private properties
        if ($this->isActionSuccess()) {
            // Delete current token
            unset($_SESSION['pcd_check']);
            unset($_SESSION['pcd_token']);
            // Regenerate token to be updated in forms
            $this->pcdTokenIndex = $this->adminPostValidator->generateTokenIndex('pcd_check');
            $this->pcdTokenValue = $this->adminPostValidator->generateTokenValue('pcd_token');
        }
        // Remind current paging slide item
        $varsArray['slideRankAfterSubmit'] = isset($_POST['pcd_slide_rank']) && (int) $_POST['pcd_slide_rank'] !== 0 ? $_POST['pcd_slide_rank'] : 1;
        // Need to update errors template var, while there is no redirection to admin posts (success state)
        $varsArray['errors'] = isset($checkedForm['paf_errors']) ? $checkedForm['paf_errors'] : false;
        // Render template with updated vars
        $this->renderAdminPosts($varsArray);
    }

    /**
     * Validate (moderate) a Comment entity changing its state in database
     * @param array $matches: an array of parameters matched in route
     * @return void
     */
    public function validateComment($matches)
    {
        // Initialize necessary vars for admin posts
        $varsArray = $this->initAdminPosts();
        // Prepare params for form validation
        $paramsArray = [
            'tokenIdentifier' => 'pcv',
            'tokenIndex' => $this->pcvTokenIndex,
            'action' => 'update',
            'errorMessage' => 'Validation action was not performed correctly<br>as concerns comment #',
            'successMessage' => 'Validation action was performed successfully<br>as concerns comment #',
            'datas' => [
                'entity' => 'comment',
                'values' => [
                    0 => [
                        'type' => 1, // int
                        'column' => 'isValidated',
                        'value' => 1 // true
                    ]
                ]
            ]
        ];
        // Validate or not form datas
        $checkedForm = $this->validateEntityForms($paramsArray, $this->adminPostValidator, '/admin/posts');
        // Reset form token immediately after success state
        // This can not be made directly in "validateEntityForms()" because of private properties
        if ($this->isActionSuccess()) {
            // Delete current token
            unset($_SESSION['pcv_check']);
            unset($_SESSION['pcv_token']);
            // Regenerate token to be updated in forms
            $this->pcvTokenIndex = $this->adminPostValidator->generateTokenIndex('pcv_check');
            $this->pcvTokenValue = $this->adminPostValidator->generateTokenValue('pcv_token');
        }
        // Remind current paging slide item
        $varsArray['slideRankAfterSubmit'] = isset($_POST['pcv_slide_rank']) && (int) $_POST['pcv_slide_rank'] !== 0 ? $_POST['pcv_slide_rank'] : 1;
        // Need to update errors template var, while there is no redirection to admin posts (success state)
        $varsArray['errors'] = isset($checkedForm['paf_errors']) ? $checkedForm['paf_errors'] : false;
        // Render template with updated vars
        $this->renderAdminPosts($varsArray);
    }

    /**
     * Publish a Comment entity changing its state in database
     * @param array $matches: an array of parameters matched in route
     * @return void
     */
    public function publishComment($matches)
    {
        // Initialize necessary vars for admin posts
        $varsArray = $this->initAdminPosts();
        // Prepare params for form validation
        $paramsArray = [
            'tokenIdentifier' => 'pcp',
            'tokenIndex' => $this->pcpTokenIndex,
            'action' => 'update',
            'errorMessage' => 'Publication action was not performed correctly<br>as concerns comment #',
            'successMessage' => 'Publication action was performed successfully<br>as concerns comment #',
            'datas' => [
                'entity' => 'comment',
                'values' => [
                    0 => [
                        'type' => 1, // int
                        'column' => 'isPublished',
                        'value' => 1 // true
                    ]
                ]
            ]
        ];
        // Validate or not form datas
        $checkedForm = $this->validateEntityForms($paramsArray, $this->adminPostValidator, '/admin/posts');
        // Reset form token immediately after success state
        // This can not be made directly in "validateEntityForms()" because of private properties
        if ($this->isActionSuccess()) {
            // Delete current token
            unset($_SESSION['pcp_check']);
            unset($_SESSION['pcp_token']);
            // Regenerate token to be updated in forms
            $this->pcpTokenIndex = $this->adminPostValidator->generateTokenIndex('pcp_check');
            $this->pcpTokenValue = $this->adminPostValidator->generateTokenValue('pcp_token');
        }
        // Remind current paging slide item
        $varsArray['slideRankAfterSubmit'] = isset($_POST['pcp_slide_rank']) && (int) $_POST['pcp_slide_rank'] !== 0 ? $_POST['pcp_slide_rank'] : 1;
        // Need to update errors template var, while there is no redirection to admin posts (success state)
        $varsArray['errors'] = isset($checkedForm['paf_errors']) ? $checkedForm['paf_errors'] : false;
        // Render template with updated vars
        $this->renderAdminPosts($varsArray);
    }

    /**
     * Cancel publication for Comment entity changing its state in database
     * @param array $matches: an array of parameters matched in route
     * @return void
     */
    public function unpublishComment($matches)
    {
        // Initialize necessary vars for admin posts
        $varsArray = $this->initAdminPosts();
        // Prepare params for form validation
        $paramsArray = [
            'tokenIdentifier' => 'pcu',
            'tokenIndex' => $this->pcuTokenIndex,
            'action' => 'update',
            'errorMessage' => 'Publication cancelation was not performed correctly<br>as concerns comment #',
            'successMessage' => 'Publication cancelation was performed successfully<br>as concerns comment #',
            'datas' => [
                'entity' => 'comment',
                'values' => [
                    0 => [
                        'type' => 1, // int
                        'column' => 'isPublished',
                        'value' => 0 // false
                    ]
                ]
            ]
        ];
        // Validate or not form datas
        $checkedForm = $this->validateEntityForms($paramsArray, $this->adminPostValidator, '/admin/posts');
        // Reset form token immediately after success state
        // This can not be made directly in "validateEntityForms()" because of private properties
        if ($this->isActionSuccess()) {
            // Delete current token
            unset($_SESSION['pcu_check']);
            unset($_SESSION['pcu_token']);
            // Regenerate token to be updated in forms
            $this->pcuTokenIndex = $this->adminPostValidator->generateTokenIndex('pcu_check');
            $this->pcuTokenValue = $this->adminPostValidator->generateTokenValue('pcu_token');
        }
        // Remind current paging slide item
        $varsArray['slideRankAfterSubmit'] = isset($_POST['pcu_slide_rank']) && (int) $_POST['pcu_slide_rank'] !== 0 ? $_POST['pcu_slide_rank'] : 1;
        // Need to update errors template var, while there is no redirection to admin posts (success state)
        $varsArray['errors'] = isset($checkedForm['paf_errors']) ? $checkedForm['paf_errors'] : false;
        // Render template with updated vars
        $this->renderAdminPosts($varsArray);
    }
}