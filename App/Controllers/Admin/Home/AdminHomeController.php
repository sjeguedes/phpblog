<?php
namespace App\Controllers\Admin\Home;
use App\Controllers\Admin\AdminController;
use Core\AppPage;
use Core\AppHTTPResponse;
use Core\Routing\AppRouter;
use Core\Config\AppConfig;
use Core\Service\AppContainer;

/**
 * Manage admin homepage actions
 */
class AdminHomeController extends AdminController
{
	/**
     * @var object: an instance of validator object
     */
    private $adminHomeValidator;
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
	 * @param AppPage $page
	 * @param AppHTTPResponse $httpResponse
	 * @param AppRouter $router
	 * @param AppConfig $config
	 * @return void
	 */
	public function __construct(AppPage $page, AppHTTPResponse $httpResponse, AppRouter $router,  AppConfig $config)
	{
		parent::__construct($page, $httpResponse, $router, $config);
		$this->currentModel = $this->getCurrentModel(__CLASS__);
        // Initialize home admin forms validator
        $this->adminHomeValidator = AppContainer::getFormValidator()[2];
        // Define used parameters to avoid CSRF:
        // Comment deleting token
        $this->pcdTokenIndex = $this->adminHomeValidator->generateTokenIndex('pcd_check');
        $this->pcdTokenValue = $this->adminHomeValidator->generateTokenValue('pcd_token');
        // Comment validation token
        $this->pcvTokenIndex = $this->adminHomeValidator->generateTokenIndex('pcv_check');
        $this->pcvTokenValue = $this->adminHomeValidator->generateTokenValue('pcv_token');
        // Comment publication token
        $this->pcpTokenIndex = $this->adminHomeValidator->generateTokenIndex('pcp_check');
        $this->pcpTokenValue = $this->adminHomeValidator->generateTokenValue('pcp_token');
        // Comment publication cancelation token
        $this->pcuTokenIndex = $this->adminHomeValidator->generateTokenIndex('pcu_check');
        $this->pcuTokenValue = $this->adminHomeValidator->generateTokenValue('pcu_token');
	}

    /**
     * Initialize default template parameters
     * @return array: an array of template parameters
     */
    private function initAdminHome()
    {
        // Get all contact entities
        $contactList = $this->currentModel->getContactList();
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
                'src' => '/assets/js/admin-homepage.js'
            ],
        ];
        return [
            'CSS' => $cssArray,
            'JS' => $jsArray,
            'metaTitle' => 'Admin homepage',
            'metaDescription' => 'Here, you can have a look at all the essential administration information and functionalities.',
            'metaRobots' => 'noindex, nofollow',
            'imgBannerCSSClass' => 'admin-home',
            'contactList' => $contactList,
            'commentList' => $commentList,
            'postList' => $postList,
            // Deleting token
            'pcdTokenIndex' => $this->pcdTokenIndex,
            'pcdTokenValue' => $this->pcdTokenValue,
            // Validation token
            'pcvTokenIndex' => $this->pcvTokenIndex,
            'pcvTokenValue' => $this->pcvTokenValue,
            // Publication token
            'pcpTokenIndex' => $this->pcpTokenIndex,
            'pcpTokenValue' => $this->pcpTokenValue,
            // Publication cancelation token
            'pcuTokenIndex' => $this->pcuTokenIndex,
            'pcuTokenValue' => $this->pcuTokenValue,
            // Error messages notice (only updated in actions)
            'errors' => false,
            // Update success state for each type of form after success redirection
            'success' => isset($_SESSION['haf_success']) ? $_SESSION['haf_success'] : false
        ];
    }

    /**
     * Render admin home template (template based on Twig template engine)
     * @param array $vars: an array of template engine parameters
     * @return void
     */
    private function renderAdminHome($vars)
    {
        echo $this->page->renderTemplate('Admin/admin-home.tpl', $vars);
    }

    /**
     * Check if there is already a success state for one of admin forms
     * @return boolean
     */
    private function isActionSuccess() {
        if(isset($_SESSION['haf_success'])) {
            return true;
        }
        else {
            return false;
        }
    }

	/**
	 * Show default admin homepage template
	 * @return void
	 */
	public function showAdminHome()
	{
        $varsArray = $this->initAdminHome();
        $this->renderAdminHome($varsArray);
        // Is it already a succcess state for one of admin forms?
        if ($this->isActionSuccess()) {
            // Do not store a success state anymore!
            unset($_SESSION['haf_success']);
        }
	}

    /**
     * Delete a Comment entity in database
     * @param array $matches: an array of parameters matched in route
     * @return void
     */
    public function deleteComment($matches)
    {
        $varsArray = $this->initAdminHome();
        $paramsArray = [
            'tokenIdentifier' => 'pcd',
            'action' => 'delete',
            'errorMessage' => 'Deleting action was not performed correctly<br>as concerns comment #',
            'successMessage' => 'Deleting action was performed successfully<br>as concerns comment #',
            'datas' => ['entity' => 'comment'],
        ];
        // Validate or not form datas
        $checkedForm = $this->validateCommentForms($paramsArray);
        // Remind current paging slide item
        $varsArray['slideRankAfterSubmit'] = (int) $_POST['pcd_slide_rank'] !== 0 ? $_POST['pcd_slide_rank'] : 1;
        // Need to update errors template var, while there is no redirection to admin home (success state)
        $varsArray['errors'] = isset($checkedForm['haf_errors']) ? $checkedForm['haf_errors'] : false;
        // Render template with updated vars
        $this->renderAdminHome($varsArray);
    }

    /**
     * Validate (moderate) a Comment entity changing its state in database
     * @param array $matches: an array of parameters matched in route
     * @return void
     */
    public function validateComment($matches)
    {
        // Initialize necessary vars for admin home
        $varsArray = $this->initAdminHome();
        // Prepare params for form validation
        $paramsArray = [
            'tokenIdentifier' => 'pcv',
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
        $checkedForm = $this->validateCommentForms($paramsArray);
        // Remind current paging slide item
        $varsArray['slideRankAfterSubmit'] = (int) $_POST['pcv_slide_rank'] !== 0 ? $_POST['pcv_slide_rank'] : 1;
        // Need to update errors template var, while there is no redirection to admin home (success state)
        $varsArray['errors'] = isset($checkedForm['haf_errors']) ? $checkedForm['haf_errors'] : false;
        // Render template with updated vars
        $this->renderAdminHome($varsArray);
    }

    /**
     * Publish a Comment entity changing its state in database
     * @param array $matches: an array of parameters matched in route
     * @return void
     */
    public function publishComment($matches)
    {
        // Initialize necessary vars for admin home
        $varsArray = $this->initAdminHome();
        // Prepare params for form validation
        $paramsArray = [
            'tokenIdentifier' => 'pcp',
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
        $checkedForm = $this->validateCommentForms($paramsArray);
        // Remind current paging slide item
        $varsArray['slideRankAfterSubmit'] = (int) $_POST['pcp_slide_rank'] !== 0 ? $_POST['pcp_slide_rank'] : 1;
        // Need to update errors template var, while there is no redirection to admin home (success state)
        $varsArray['errors'] = isset($checkedForm['haf_errors']) ? $checkedForm['haf_errors'] : false;
        // Render template with updated vars
        $this->renderAdminHome($varsArray);
    }

    /**
     * Cancel publication for Comment entity changing its state in database
     * @param array $matches: an array of parameters matched in route
     * @return void
     */
    public function unpublishComment($matches)
    {
        // Initialize necessary vars for admin home
        $varsArray = $this->initAdminHome();
        // Prepare params for form validation
        $paramsArray = [
            'tokenIdentifier' => 'pcu',
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
        $checkedForm = $this->validateCommentForms($paramsArray);
        // Remind current paging slide item
        $varsArray['slideRankAfterSubmit'] = (int) $_POST['pcu_slide_rank'] !== 0 ? $_POST['pcu_slide_rank'] : 1;
        // Need to update errors template var, while there is no redirection to admin home (success state)
        $varsArray['errors'] = isset($checkedForm['haf_errors']) ? $checkedForm['haf_errors'] : false;
        // Render template with updated vars
        $this->renderAdminHome($varsArray);
    }

    /**
     * Validate (or not) home admin comment forms (deleting, validation, publication, publication cancelation)
     * @param array $params: an array of parameter to validate a simple form
     * @return array: an array which contains result of validation (errors, form values, ...)
     */
    private function validateCommentForms($params)
    {
        // Use value as var name
        $tokenIndex = $params['tokenIdentifier'] . 'TokenIndex';
        $commentIdIndex = $params['tokenIdentifier'] . '_id';
        $action = $params['action'] . 'Entity';
        $arguments = [$_POST[$commentIdIndex], $params['datas']];
        // Check token to avoid CSRF
        $tokenValue = isset($_POST[$this->$tokenIndex]) ? $_POST[$this->$tokenIndex] : false;
        $tokenPrefix = $params['tokenIdentifier'] . '_';
        $this->adminHomeValidator->validateToken($tokenValue, $tokenPrefix);
        // Get validation result
        $result = $this->adminHomeValidator->getResult();
        // Additional error message in case of form errors
        if (!empty($result['haf_errors'])) {
             // Check wrong comment id used in form
            if ($this->currentModel->getCommentById($_POST[$commentIdIndex]) == false) {
                $result['haf_errors']['haf_failed']['comment']['message'] = $params['errorMessage'] . htmlentities($_POST[$commentIdIndex]) . '.';
            }
        }
        // Submit: comment form is correctly filled.
        if (isset($result) && empty($result['haf_errors']) && isset($result['haf_check']) && $result['haf_check']) {
            // Perform desired action in database
            try {
                // Check comment id used in form
                // Is there an existing comment with this id?
                if ($this->currentModel->getCommentById($_POST[$commentIdIndex]) != false) {
                    // Delete or validate or publish comment or unpublish comment
                    call_user_func_array([$this->currentModel, $action], $arguments);
                    $performed = true;
                } else {
                    $result['haf_errors']['haf_failed']['comment']['message2'] = $this->config::isDebug('<span class="form-check-notice">Sorry an error happened! <strong>Wrong comment id</strong> is used.<br>Action [Debug trace: <strong>' . $params["action"] . '</strong>] on comment can not be performed correctly.<br>[Debug trace: comment id "<strong>' . htmlentities($_POST[$commentIdIndex]) . '</strong>" doesn\'t exist in database!]</span>');
                    $performed = false;
                }
            } catch (\PDOException $e) {
                $result['haf_errors']['haf_failed']['comment']['message2'] = $this->config::isDebug('<span class="form-check-notice">Sorry a technical error happened! Please try again later.<br>Action [Debug trace: <strong>' . $params["action"] . '</strong>] on comment [Debug trace: <strong>comment id ' . htmlentities($_POST[$commentIdIndex]) . '</strong>] was not performed correctly.<br>[Debug trace: <strong>' . $e->getMessage() . '</strong>]</span>');
                $performed = false;
            }
            // Action was performed successfuly on Comment entity!
            if ($performed) {
                // Reset form associated datas
                $result = [];
                // Delete current token
                unset($_SESSION[$tokenPrefix . 'check']);
                unset($_SESSION[$tokenPrefix . 'token']);
                // Regenerate token to be updated in forms
                session_regenerate_id(true);
                $this->$tokenIndex = $this->adminHomeValidator->generateTokenIndex($tokenPrefix . 'check');
                $this->$tokenValue = $this->adminHomeValidator->generateTokenValue($tokenPrefix . 'token');
                // Initialize success state
                $_SESSION['haf_success']['comment'] = [
                    'state' => true, // show success message (not really useful)
                    'id' => htmlentities($_POST[$commentIdIndex]), // retrieve comment id
                    'message' => $params['successMessage'] . htmlentities($_POST[$commentIdIndex]), // customize success message as regards action
                    'slide_rank' => htmlentities($_POST[$tokenPrefix . 'slide_rank']) // last slide item reminder to position slide after redirection
                ];
                // Redirect to admin home action (to reset submitted form)
                $this->httpResponse->addHeader('Location: /admin');
            }
        }
        // Update error notice messages and form values
        return $result;
    }

	/**
	 * Get all contact entities datas
	 * @return array: an array which contains all the datas
	 */
	public function getContacts()
	{
		return $this->currentModel->selectAll('contacts');
	}
}