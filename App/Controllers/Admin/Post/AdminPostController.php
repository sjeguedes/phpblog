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
        // Get all posts with external model (PostModel)
        $postList = $this->currentModel->getPostList();
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
            ],
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
        if(isset($_SESSION['paf_success'])) {
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