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
     * @var string: dynamic index name for deleting contact form token
     */
    private $cdTokenIndex;
    /**
     * @var string: dynamic value for deleting contact form token
     */
    private $cdTokenValue;
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
        // Contact deleting token
        $this->cdTokenIndex = $this->adminHomeValidator->generateTokenIndex('cd_check');
        $this->cdTokenValue = $this->adminHomeValidator->generateTokenValue('cd_token');
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
                'src' => '/assets/js/adminHomepage.js'
            ],
        ];
        return [
            'CSS' => $cssArray,
            'JS' => $jsArray,
            'metaTitle' => 'Admin homepage',
            'metaDescription' => 'Here, you can have a look at all the essential administration information and functionalities.',
            'metaRobots' => 'noindex, nofollow',
            'imgBannerCSSClass' => 'admin-home',
            // Get complete list of each entity type
            'contactList' => $contactList,
            'commentList' => $commentList,
            'postList' => $postList,
            // Get number of entities to show per slide for each slider (paging sliders)
            'contactPerSlide' => $this->config::getParam('admin.home.contactPerSlide'),
            'commentPerSlide' => $this->config::getParam('admin.home.commentPerSlide'),
            'postPerSlide' => $this->config::getParam('admin.home.postPerSlide'),
            // Deleting token for Contact entity
            'cdTokenIndex' => $this->cdTokenIndex,
            'cdTokenValue' => $this->cdTokenValue,
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
            'success' => isset($_SESSION['haf_success']) ? $_SESSION['haf_success'] : false,
            'loginSuccess' => isset($_SESSION['lif_success']) ? $_SESSION['lif_success'] : false
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
     * Check if there is already a success state for one of home admin forms
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
        // Is it already a succcess state for admin login form?
        // Enable authentication success message box, once a time, when user is logged in.
        if (isset($_SESSION['lif_success'])) {
            // Do not store a success state anymore!
            unset($_SESSION['lif_success']);
        }
	}

    /**
     * Delete a Contact entity in database
     * @param array $matches: an array of parameters matched in route
     * @return void
     */
    public function deleteContact($matches)
    {
        $varsArray = $this->initAdminHome();
        $paramsArray = [
            'tokenIdentifier' => 'cd',
            'action' => 'delete',
            'errorMessage' => 'Deleting action was not performed correctly<br>as concerns contact #',
            'successMessage' => 'Deleting action was performed successfully<br>as concerns contact #',
            'datas' => ['entity' => 'contact'],
        ];
        // Validate or not form datas
        $checkedForm = $this->validateEntityForms($paramsArray);
        // Remind current paging slide item
        $varsArray['slideRankAfterSubmit'] = isset($_POST['cd_slide_rank']) && (int) $_POST['cd_slide_rank'] !== 0 ? $_POST['cd_slide_rank'] : 1;
        // Need to update errors template var, while there is no redirection to admin home (success state)
        $varsArray['errors'] = isset($checkedForm['haf_errors']) ? $checkedForm['haf_errors'] : false;
        // Render template with updated vars
        $this->renderAdminHome($varsArray);
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
        $checkedForm = $this->validateEntityForms($paramsArray);
        // Remind current paging slide item
        $varsArray['slideRankAfterSubmit'] = isset($_POST['pcd_slide_rank']) && (int) $_POST['pcd_slide_rank'] !== 0 ? $_POST['pcd_slide_rank'] : 1;
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
        $checkedForm = $this->validateEntityForms($paramsArray);
        // Remind current paging slide item
        $varsArray['slideRankAfterSubmit'] = isset($_POST['pcv_slide_rank']) && (int) $_POST['pcv_slide_rank'] !== 0 ? $_POST['pcv_slide_rank'] : 1;
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
        $checkedForm = $this->validateEntityForms($paramsArray);
        // Remind current paging slide item
        $varsArray['slideRankAfterSubmit'] = isset($_POST['pcp_slide_rank']) && (int) $_POST['pcp_slide_rank'] !== 0 ? $_POST['pcp_slide_rank'] : 1;
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
        $checkedForm = $this->validateEntityForms($paramsArray);
        // Remind current paging slide item
        $varsArray['slideRankAfterSubmit'] = isset($_POST['pcu_slide_rank']) && (int) $_POST['pcu_slide_rank'] !== 0 ? $_POST['pcu_slide_rank'] : 1;
        // Need to update errors template var, while there is no redirection to admin home (success state)
        $varsArray['errors'] = isset($checkedForm['haf_errors']) ? $checkedForm['haf_errors'] : false;
        // Render template with updated vars
        $this->renderAdminHome($varsArray);
    }

    /**
     * Validate (or not) home admin actions forms on entity (deleting, validation, publication, publication cancelation)
     * @param array $params: an array of parameter to validate a simple form
     * @return array: an array which contains result of validation (errors, form values, ...)
     */
    private function validateEntityForms($params)
    {
        // Use value as var name
        $tokenIndex = $params['tokenIdentifier'] . 'TokenIndex';
        $entityIdIndex = $params['tokenIdentifier'] . '_id';
        $entity = $params['datas']['entity'];
        $entityName = ucfirst($params['datas']['entity']);
        $getEntityByid = "get${entityName}ById";
        $action = $params['action'] . 'Entity';
        $arguments = [$_POST[$entityIdIndex], $params['datas']];
        // Check token to avoid CSRF
        $tokenValue = isset($_POST[$this->$tokenIndex]) ? $_POST[$this->$tokenIndex] : false;
        $tokenPrefix = $params['tokenIdentifier'] . '_';
        $this->adminHomeValidator->validateToken($tokenValue, $tokenPrefix);
        // Get validation result
        $result = $this->adminHomeValidator->getResult();
        // Additional error message in case of form errors
        if (!empty($result['haf_errors'])) {
            // Check wrong entity id used in form
            if ($this->currentModel->$getEntityByid($_POST[$entityIdIndex]) == false) {
                $result['haf_errors']['haf_failed'][$entity]['message'] = $params['errorMessage'] . htmlentities($_POST[$entityIdIndex]) . '.';
            }
        }
        // Submit: entity form is correctly filled.
        if (isset($result) && empty($result['haf_errors']) && isset($result['haf_check']) && $result['haf_check']) {
            // Perform desired action in database
            try {
                // Check entity id used in form
                // Is there an existing entity with this id?
                if ($this->currentModel->$getEntityByid($_POST[$entityIdIndex]) != false) {
                    // Delete or validate or publish or unpublish entity
                    call_user_func_array([$this->currentModel, $action], $arguments);
                    $performed = true;
                } else {
                    $result['haf_errors']['haf_failed'][$entity]['message2'] = $this->config::isDebug('<span class="form-check-notice">Sorry an error happened! <strong>Wrong ' . $entity . ' id</strong> is used.<br>Action [Debug trace: <strong>' . $params["action"] . '</strong>] on ' . $entity . ' can not be performed correctly.<br>[Debug trace: ' . $entity . ' id "<strong>' . htmlentities($_POST[$entityIdIndex]) . '</strong>" doesn\'t exist in database!]</span>');
                    $performed = false;
                }
            } catch (\PDOException $e) {
                $result['haf_errors']['haf_failed'][$entity]['message2'] = $this->config::isDebug('<span class="form-check-notice">Sorry a technical error happened! Please try again later.<br>Action [Debug trace: <strong>' . $params["action"] . '</strong>] on ' . $entity . ' [Debug trace: <strong> ' . $entity . ' id ' . htmlentities($_POST[$entityIdIndex]) . '</strong>] was not performed correctly.<br>[Debug trace: <strong>' . $e->getMessage() . '</strong>]</span>');
                $performed = false;
            }
            // Action was performed successfully on entity!
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
                $_SESSION['haf_success'][$entity] = [
                    'state' => true, // show success message (not really useful)
                    'id' => htmlentities($_POST[$entityIdIndex]), // retrieve entity id
                    'message' => $params['successMessage'] . htmlentities($_POST[$entityIdIndex]), // customize success message as regards action
                    'slideRank' => htmlentities($_POST[$tokenPrefix . 'slide_rank']) // last slide item reminder to position slide after redirection
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