<?php
namespace App\Controllers\Admin\User;
use App\Controllers\Admin\AdminController;
use Core\AppPage;
use Core\AppHTTPResponse;
use Core\Routing\AppRouter;
use Core\Config\AppConfig;
use Core\Service\AppContainer;
use App\Models\Admin\Entity\User;

/**
 * Manage admin user actions
 */
class AdminUserController extends AdminController
{
    /**
     * @var object: an instance of validator object
     */
    private $loginFormValidator;
    /**
     * @var string: dynamic index name for login form token
     */
    private $lifTokenIndex;
    /**
     * @var string: dynamic value for login form token
     */
    private $lifTokenValue;
    /**
     * @var object: an instance of captcha object
     */
    private $loginFormCaptcha;

    /**
     * Constructor
     * @param AppPage $page
     * @param AppHTTPResponse $httpResponse
     * @param AppRouter $router
     * @param AppConfig $config
     * @return void
     */
    public function __construct(AppPage $page, AppHTTPResponse $httpResponse, AppRouter $router, AppConfig $config)
    {
        parent::__construct($page, $httpResponse, $router, $config);
        $this->currentModel = $this->getCurrentModel(__CLASS__);
        // Initialize login form validator
        $this->loginFormValidator = AppContainer::getFormValidator()[3];
        // Define used parameters to avoid CSRF on login form
        $this->lifTokenIndex = $this->loginFormValidator->generateTokenIndex('lif_check');
        $this->lifTokenValue = $this->loginFormValidator->generateTokenValue('lif_token');
        // Initialize login form captcha
        $this->loginFormCaptcha = AppContainer::getCaptcha()[2];
    }

    /**
     * Initialize admin login template parameters
     * @param array: an array which contains result of form validation (error on fields, filtered form values, ...)
     * @return array: an array of template parameters
     */
    private function initAdminAccess($checkedForm = null)
    {
        $jsArray = [
            0 => [
                'placement' => 'bottom',
                'attributes' => 'async defer',
                'src' => 'https://www.google.com/recaptcha/api.js?hl=en&onload=onloadCallback&render=explicit'
            ],
            1 => [
                'placement' => 'bottom',
                'src' => '/assets/js/phpblog.js'
            ],
            2 => [
                'placement' => 'bottom',
                'src' => '/assets/js/loginUser.js'
            ]
        ];
        return [
            'JS' => $jsArray,
            'metaTitle' => 'Admin access',
            'metaDescription' => 'Please connect to access application back office.',
            'metaRobots' => 'noindex, nofollow',
            'imgBannerCSSClass' => 'admin-login',
            'email' => isset($checkedForm['lif_email']) ? $checkedForm['lif_email'] : '',
            'password' => isset($checkedForm['lif_password']) ? $checkedForm['lif_password'] : '',
            'siteKey' => $this->config::getParam('googleRecaptcha.siteKey'),
            // Token for login form
            'lifTokenIndex' => $this->lifTokenIndex,
            'lifTokenValue' => $this->lifTokenValue,
            // Does validation submit already exist with error?
            'tryValidation' => isset($_POST['lif_submit']) ? 1 : 0,
            'submit' => isset($_SESSION['lif_success']) && $_SESSION['lif_success'] ? 1 : 0,
            // Error messages
            'errors' => isset($checkedForm['lif_errors']) ? $checkedForm['lif_errors'] : false,
            // Update success state if the same template is loaded (no redirection to admin homepage)
            'success' => isset($_SESSION['lif_success']) ? $_SESSION['lif_success'] : false
        ];
    }

    /**
     * Render admin login template (template based on Twig template engine)
     * @param array $vars: an array of template engine parameters
     * @return void
     */
    private function renderAdminAccess($vars)
    {
        echo $this->page->renderTemplate('Admin/admin-login-form.tpl', $vars);
    }

    /**
     * Check if there is already a success state for admin login form
     * @return boolean
     */
    private function isLoginSuccess() {
        if (isset($_SESSION['lif_success'])) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Show default admin login template
     * @return void
     */
    public function showAdminAccess()
    {
        // Call template with methods for more flexibility
        // (No need here to update user form inputs! "$checkedForm" argument is null)
        $varsArray = $this->initAdminAccess();
        $this->renderAdminAccess($varsArray);
    }

    /**
     * Login a user with form validation try (on submission) template
     * @return void
     */
    public function loginUser()
    {
        // Store result from login form validation
        $checkedForm = $this->validateLoginForm();
        // Is it already a succcess state?
        if ($this->isLoginSuccess()) {
            // Success state is returned: avoid previous $_POST with a redirection to admin homepage
            $this->httpResponse->addHeader('Location: /admin');
            exit();
        } else {
            // Call template with methods for more flexibility
            $varsArray = $this->initAdminAccess($checkedForm);
            $this->renderAdminAccess($varsArray);
        }
    }

    /**
     * Validate (or not) login form
     * @return array: an array which contains result of validation (error on fields, filtered form values, ...)
     */
    public function validateLoginForm()
    {
        // Prepare filters for form email data
        $datas = [
            0 => ['name' => 'email', 'filter' => 'email', 'modifiers' => ['trimStr']]
            // Password must not be filtered (no sanitization), it will only be hashed to check matching in database.
        ];
        // Filter user email input in $_POST datas
        $this->loginFormValidator->filterDatas($datas);
        // Email
        $this->loginFormValidator->validateEmail('email', 'email', $_POST['lif_email']);
        // Password
        $this->loginFormValidator->validatePassword('password', 'password', $_POST['lif_password']);
        // Check token to avoid CSRF
        $this->loginFormValidator->validateToken(isset($_POST[$this->lifTokenIndex]) ? $_POST[$this->lifTokenIndex] : false);
        // Get validation result without captcha
        $result = $this->loginFormValidator->getResult();
        // Update validation result with Google Recaptcha antispam validation
        $result = $this->loginFormCaptcha->call([$_POST['g-recaptcha-response'], $result, 'lif_errors']);
        // Submit: login form is correctly filled.
        if (isset($result) && empty($result['lif_errors']) && isset($result['g-recaptcha-response']) && $result['g-recaptcha-response'] && isset($result['lif_check']) && $result['lif_check']) {
             try {
                // Check email account in database
                $user = $this->currentModel->getUserByEmail($result['lif_email']);
                // Email account exists in database
                if ($user != false) {
                    // Check existing password for email account
                    $password = $this->verifyUserPassword($result['lif_password'], $user->password);
                    // Password matches with password in database
                    if ($password) {
                        // Reset login form
                        $result = [];
                        // Delete login form token
                        unset($_SESSION['lif_check']);
                        unset($_SESSION['lif_token']);
                        // Regenerate all other existing form tokens on website
                        $this->regenerateAllFormTokens();
                        // Regenerate token to be updated in login form
                        $this->lifTokenIndex = $this->loginFormValidator->generateTokenIndex('lif_check');
                        $this->lifTokenValue = $this->loginFormValidator->generateTokenValue('lif_token');
                        // Show success message
                        $_SESSION['lif_success'] = true;
                        // ------------------------------------------------------------------------------
                        // couple email and password belong to User entity in database: login user with session values
                        // So, initialize user session values (store user id, session id and generate user session token) with user id as part of index
                        $_SESSION['user'] = [
                            'userId' => $user->id,
                            'userName' => [$user->firstName, $user->familyName],
                            'sessionId' => session_id(),
                            'sessionToken' => $this->generateUserSessionTokenValue('user' . $user->id . '_token')
                        ];
                    } else {
                        // No existing password (no existing password or no match with email account)
                        $result['lif_errors']['lif_login'] = $this->config::isDebug('<span class="form-check-notice">Sorry, authentication failed! Please check your email and password!<br>[Debug trace: password "<strong>' . $result['lif_password'] . '</strong>" doesn\'t exist in database<br>or doesn\'t match with email account "<strong>' . $result['lif_email'] . '</strong>"!]</span>');
                    }
                } else {
                    // No existing email account
                    $result['lif_errors']['lif_login'] = $this->config::isDebug('<span class="form-check-notice">Sorry, authentication failed! Please check your email and password!<br>[Debug trace: email account "<strong>' . $result['lif_email'] . '</strong>" doesn\'t exist in database!]</span>');
                }
            } catch (\PDOException $e) {
                $result['lif_errors']['lif_login'] = $this->config::isDebug('<span class="form-check-notice">Sorry a technical error happened! You are not able to login at this time: please try again later.<br>[Debug trace: <strong>' . $e->getMessage() . '</strong>]</span>');
            }

        }
        // Update datas in form, error messages near fields, and notice error/success message
        return $result;
    }

    /**
     * Logout a back office user
     * @return void
     */
    public function logoutUser()
    {
        // Add control to check if there is an active user session
        $user = $this->session::isUserAuthenticated();
        // Destroy current user session
        if ($user != false) {
            // Reset custom cookie which stores user token
            foreach ($_COOKIE as $key => $value) {
                if (preg_match('#^UPDATEDUST((?=.\w)(?=.\d).*)$#', $key)) {
                    //unset($_COOKIE[$key]);
                    $this->session::resetCookie($key);
                }
            }
            $this->session::destroy();
            $this->httpResponse->addHeader('Location: /admin/login');
            exit();
        }
    }
}