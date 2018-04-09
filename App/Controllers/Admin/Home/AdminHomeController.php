<?php
namespace App\Controllers\Admin\Home;
use App\Controllers\Admin\AdminController;
use Core\Routing\AppRouter;
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
	 * Constructor
	 * @param AppRouter $router
	 * @return void
	 */
	public function __construct(AppRouter $router)
	{
		parent::__construct($router);
		$this->currentModel = $this->getCurrentModel(__CLASS__);
        // Initialize home admin forms validator
        $this->adminHomeValidator = $this->container::getFormValidator()[2];
        // Define used parameters to avoid CSRF:
        // Contact deleting token
        $this->cdTokenIndex = $this->adminHomeValidator->generateTokenIndex('cd_check');
        $this->cdTokenValue = $this->adminHomeValidator->generateTokenValue('cd_token');
	}

    /**
     * Initialize default template parameters
     * @return array: an array of template parameters
     */
    private function initAdminHome()
    {
        // Get all contact entities
        $contactList = $this->currentModel->getContactList();
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
                'src' => '/assets/js/adminHome.js'
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
            // Get number of entities to show per slide for each slider (paging sliders)
            'contactPerSlide' => $this->config::getParam('admin.home.contactPerSlide'),
            // Deleting token for Contact entity
            'cdTokenIndex' => $this->cdTokenIndex,
            'cdTokenValue' => $this->cdTokenValue,
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
            'tokenIndex' => $this->cdTokenIndex,
            'action' => 'delete',
            'errorMessage' => 'Deleting action was not performed correctly<br>as concerns contact #',
            'successMessage' => 'Deleting action was performed successfully<br>as concerns contact #',
            'datas' => ['entity' => 'contact'],
        ];
        // Validate or not form datas
        $checkedForm = $this->validateEntityForms($paramsArray, $this->adminHomeValidator, '/admin');

        // Reset form token immediately after success state
        // This can not be made directly in "validateEntityForms()" because of private properties
        if ($this->isActionSuccess()) {
            // Delete current token
            unset($_SESSION['cd_check']);
            unset($_SESSION['cd_token']);
            // Regenerate token to be updated in forms
            $this->cdTokenIndex = $this->adminHomeValidator->generateTokenIndex('cd_check');
            $this->cdTokenValue = $this->adminHomeValidator->generateTokenValue('cd_token');
        }

        // Remind current paging slide item
        $varsArray['slideRankAfterSubmit'] = isset($_POST['cd_slide_rank']) && (int) $_POST['cd_slide_rank'] !== 0 ? $_POST['cd_slide_rank'] : 1;
        // Need to update errors template var, while there is no redirection to admin home (success state)
        $varsArray['errors'] = isset($checkedForm['haf_errors']) ? $checkedForm['haf_errors'] : false;
        // Render template with updated vars
        $this->renderAdminHome($varsArray);
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