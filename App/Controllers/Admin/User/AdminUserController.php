<?php
namespace App\Controllers\Admin\User;
use App\Controllers\Admin\AdminController;
use Core\Routing\AppRouter;
use App\Models\Admin\Entity\User;

/**
 * Manage admin user actions
 */
class AdminUserController extends AdminController
{
    /**
     * @var object: an instance of validator object
     */
    private $registerFormValidator;
    /**
     * @var object: an instance of validator object
     */
    private $loginFormValidator;
    /**
     * @var object: an instance of validator object
     */
    private $forgetPasswordFormValidator;
     /**
     * @var string: dynamic index name for register form token
     */
    private $refTokenIndex;
    /**
     * @var string: dynamic value for register form token
     */
    private $refTokenValue;
    /**
     * @var string: dynamic index name for login form token
     */
    private $lifTokenIndex;
    /**
     * @var string: dynamic value for login form token
     */
    private $lifTokenValue;
    /**
     * @var string: dynamic index name for request new password (forgotten) form token
     */
    private $fpfTokenIndex;
    /**
     * @var string: dynamic value for request new password (forgotten) form token
     */
    private $fpfTokenValue;
    /**
     * @var string: dynamic index name for renew password form token
     */
    private $rpfTokenIndex;
    /**
     * @var string: dynamic value for renew password form token
     */
    private $rpfTokenValue;
    /**
     * @var object: an instance of captcha object
     */
    private $registerFormCaptcha;
    /**
     * @var object: an instance of captcha object
     */
    private $loginFormCaptcha;
    /**
     * @var object: an instance of captcha object
     */
    private $forgetPasswordFormCaptcha;
    /**
     * @var array: an array of parameters to generate captcha user interface
     */
    private $captchaUIParams;
    /**
     * @var object: an instance of captcha object
     */
    private $renewPasswordFormCaptcha;

    /**
     * Constructor
     * @param AppRouter $router
     * @return void
     */
    public function __construct(AppRouter $router)
    {
        parent::__construct($router);
        $this->currentModel = $this->getCurrentModel(__CLASS__);
        // Initialize register form validator
        $this->registerFormValidator = $this->container::getFormValidator()[3];
        // Initialize login form validator
        $this->loginFormValidator = $this->container::getFormValidator()[4];
        // Initialize request new password (forgotten) validator
        $this->forgetPasswordFormValidator = $this->container::getFormValidator()[5];
        // Initialize renew password validator
        $this->renewPasswordFormValidator = $this->container::getFormValidator()[6];
        // Define used parameters to avoid CSRF on register form
        $this->refTokenIndex = $this->registerFormValidator->generateTokenIndex('ref_check');
        $this->refTokenValue = $this->registerFormValidator->generateTokenValue('ref_token');
        // Define used parameters to avoid CSRF on login form
        $this->lifTokenIndex = $this->loginFormValidator->generateTokenIndex('lif_check');
        $this->lifTokenValue = $this->loginFormValidator->generateTokenValue('lif_token');
        // Define used parameters to avoid CSRF on request new password (forgotten) form
        $this->fpfTokenIndex = $this->forgetPasswordFormValidator->generateTokenIndex('fpf_check');
        $this->fpfTokenValue = $this->forgetPasswordFormValidator->generateTokenValue('fpf_token');
        // Define used parameters to avoid CSRF on renew password form
        $this->rpfTokenIndex = $this->renewPasswordFormValidator->generateTokenIndex('rpf_check');
        $this->rpfTokenValue = $this->renewPasswordFormValidator->generateTokenValue('rpf_token');
        // Initialize register form captcha
        $this->registerFormCaptcha = $this->container::getCaptcha()[2];
        // Initialize login form captcha
        $this->loginFormCaptcha = $this->container::getCaptcha()[3];
        // Initialize request new password (forgotten) form captcha
        $this->forgetPasswordFormCaptcha = $this->container::getCaptcha()[4];
        // Initialize renew password form captcha
        $this->renewPasswordFormCaptcha = $this->container::getCaptcha()[5];
    }

    /**
     * Initialize admin register template parameters
     * @param array|null $checkedForm: an array which contains result of form validation (error on fields, filtered form values, ...), or null
     * @return array: an array of template parameters
     */
    private function initAdminRegister($checkedForm = null)
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
                'src' => '/assets/js/registerUser.js'
            ]
        ];
        return [
            'JS' => $jsArray,
            'metaTitle' => 'Admin registration',
            'metaDescription' => 'Please register as a member to use application back office.',
            'metaRobots' => 'noindex, nofollow',
            'imgBannerCSSClass' => 'admin-register',
            'familyName' => isset($checkedForm['ref_familyName']) ? $checkedForm['ref_familyName'] : '',
            'firstName' => isset($checkedForm['ref_firstName']) ? $checkedForm['ref_firstName'] : '',
            'nickName' => isset($checkedForm['ref_nickName']) ? $checkedForm['ref_nickName'] : '',
            'email' => isset($checkedForm['ref_email']) ? $checkedForm['ref_email'] : '',
            'password' => isset($checkedForm['ref_password']) ? $checkedForm['ref_password'] : '',
            'passwordConfirmation' => isset($checkedForm['ref_passwordConfirmation']) ? $checkedForm['ref_passwordConfirmation'] : '',
            'siteKey' => $this->config::getParam('googleRecaptcha.siteKey'),
            // Token for registration form
            'refTokenIndex' => $this->refTokenIndex,
            'refTokenValue' => $this->refTokenValue,
            // Does validation submit already exist with error?
            'tryValidation' => isset($_POST['ref_submit']) ? 1 : 0,
            'submit' => isset($_SESSION['ref_success']) && $_SESSION['ref_success'] ? 1 : 0,
            // Error messages
            'errors' => isset($checkedForm['ref_errors']) ? $checkedForm['ref_errors'] : false,
            // Update success state if the same template is loaded (no redirection to admin homepage)
            'success' => isset($_SESSION['ref_success']) ? $_SESSION['ref_success'] : false
        ];
    }

    /**
     * Render admin register template (template based on Twig template engine)
     * @param array $vars: an array of template engine parameters
     * @return void
     */
    private function renderAdminRegister($vars)
    {
        echo $this->page->renderTemplate('Admin/admin-register-form.tpl', $vars);
    }

    /**
     * Check if there is already a success state for admin register form
     * @return boolean
     */
    private function isRegisterSuccess() {
        if (isset($_SESSION['ref_success'])) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Check if there is already a success state for user registration activation
     * @return boolean
     */
    private function isActivationSuccess() {
        if (isset($_SESSION['ref_act_success'])) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Show default register template
     * @return void
     */
    public function showAdminRegister()
    {
        // (No need here to update user form inputs! "$checkedForm" argument is null)
        $varsArray = $this->initAdminRegister();
        // Account activation case
        if (isset($_GET['userAccount']) && isset($_GET['activationKey'])) {
            $activationResult = $this->activateUserAccount($_GET);
            if ($this->isActivationSuccess()) {
                $varsArray['activationSuccess'] = $_SESSION['ref_act_success'];
            } else {
                $varsArray['activationErrors'] = $activationResult['ref_act_errors'];
            }
        }
        // Call template with methods for more flexibility
        $this->renderAdminRegister($varsArray);
        // Is it already a succcess state for admin register form?
        // Enable registration success message box once a time
        if ($this->isRegisterSuccess()) {
            // Do not store a success state anymore!
            unset($_SESSION['ref_success']);
        }
        // Is it already a succcess state for user registration activation?
        // Enable activation success message box once a time
        if ($this->isActivationSuccess()) {
            // Do not store a success state anymore!
            unset($_SESSION['ref_act_success']);
        }
    }

    /**
     * Register a user with form validation try (on submission) template
     * @return void
     */
    public function registerUser() {
        // Store result from register form validation
        $checkedForm = $this->validateRegisterForm();
        // Is it already a succcess state?
        if ($this->isRegisterSuccess()) {
            // Success state is returned: avoid previous $_POST with a redirection to the same page
            $this->httpResponse->addHeader('Location: /admin/register');
            exit();
        } else {
            // Call template with methods for more flexibility
            $varsArray = $this->initAdminRegister($checkedForm);
            $this->renderAdminRegister($varsArray);
        }
    }

    /**
     * Validate (or not) register form
     * @return array: an array which contains result of validation (error on fields, filtered form values, ...)
     */
    private function validateRegisterForm()
    {
        // Prepare filters for form datas
        $datas = [
            0 => ['name' => 'familyName', 'filter' => 'alphanum', 'modifiers' => ['trimStr', 'strtoupperStr']],
            1 => ['name' => 'firstName',  'filter' => 'alphanum', 'modifiers' => ['trimStr', 'ucfirstStr']],
            2 => ['name' => 'nickName', 'filter' => 'alphanum', 'modifiers' => ['trimStr', 'ucfirstStr']],
            3 => ['name' => 'email', 'filter' => 'email', 'modifiers' => ['trimStr']]
            // Password must not be filtered (no sanitization), it will only be hashed to check matching in database.
        ];
        // Filter user inputs in $_POST datas
        $this->registerFormValidator->filterDatas($datas);
        // Family name
        $this->registerFormValidator->validateRequired('familyName', 'family name');
        // First name
        $this->registerFormValidator->validateRequired('firstName', 'first name');
        // Nickname
        $this->registerFormValidator->validateRequired('nickName', 'nickname');
        // Email
        $this->registerFormValidator->validateEmail('email', 'email', $_POST['ref_email']);
        // Password
        $this->registerFormValidator->validatePassword('password', 'password', $_POST['ref_password']);
        // Password confirmation
        $this->registerFormValidator->validatePassword('passwordConfirmation', 'password', isset($_POST['ref_passwordConfirmation']) ? $_POST['ref_passwordConfirmation'] : '');
        // Check if password confirmation and password are identical
        $this->registerFormValidator->validatePasswordConfirmation('passwordConfirmation', $_POST['ref_password'], isset($_POST['ref_passwordConfirmation']) ? $_POST['ref_passwordConfirmation'] : '');
        // Check token to avoid CSRF
        $this->registerFormValidator->validateToken(isset($_POST[$this->refTokenIndex]) ? $_POST[$this->refTokenIndex] : false);
        // Get validation result without captcha
        $result = $this->registerFormValidator->getResult();
        // Update validation result with Google Recaptcha antispam validation
        $result = $this->registerFormCaptcha->call([isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : false, $result, 'ref_errors']);
        // Submit: register form is correctly filled.
        if (isset($result) && empty($result['ref_errors']) && isset($result['g-recaptcha-response']) && $result['g-recaptcha-response'] && isset($result['ref_check']) && $result['ref_check']) {
             try {
                // Check not existing email account or nickname in database
                $existingEmail = false;
                $existingNickName = false;
                $users = $this->currentModel->getUserList();
                foreach ($users as $user) {
                    if ($result['ref_email'] === $user->email) {
                        $existingEmail = true;
                        break;
                    } elseif (strtolower($result['ref_nickName']) === strtolower($user->nickName)) {
                        $existingNickName = true;
                        break;
                    }
                }
                // Show any existing errors
                if ($existingEmail) {
                    // Email account already exists in database!
                    $result['ref_errors']['ref_register'] = $this->config::isDebug('<span class="form-check-notice">Sorry account registration was refused!<br>A user already exists with this email address: <strong>' . htmlentities($result['ref_email']) . '</strong>!<br>Please declare another one.</span>');
                    $insertion = false;
                } elseif ($existingNickName) {
                    // Nickname already exists in database!
                    $result['ref_errors']['ref_register'] = $this->config::isDebug('<span class="form-check-notice">Sorry account registration was refused!<br>A user already exists with this nickname: <strong>' . htmlentities($result['ref_nickName']) . '</strong>!<br><strong><em class="text-muted">Advice: Change at least one character.<br>You can try to combine numbers and letters.<br>Caution: Play with case make no result!</em></strong><br>Please declare another one.</span>');
                    $insertion = false;
                } else {
                    // User entity insertion:
                    // Hash user password before insertion
                    $result['ref_password'] = $this->generateUserPasswordEncryption($result['ref_password']);
                    // Create account activation code to insert and send to user (thanks to his email address!)
                    $result['ref_activationCode'] = $this->generateUserActivationCode($result['ref_email']);
                    // Create (Insert) User entity account in database
                    $this->currentModel->insertUser($result);
                    $insertion = true;
                }
            } catch (\PDOException $e) {
                $result['ref_errors']['ref_register'] = $this->config::isDebug('<span class="form-check-notice">Sorry a technical error happened! You are not able to create an account at this time: please try again later.<br>[Debug trace: <strong>' . $e->getMessage() . '</strong>]</span>');
                $insertion = false;
            }
            // User entity was saved successfuly!
            if ($insertion) {
                // Send account activation email
                $this->sendUserActivationEmail($result);
                // Reset the form
                $result = [];
                // Delete current token
                unset($_SESSION['ref_check']);
                unset($_SESSION['ref_token']);
                // Regenerate token to be updated in form
                $this->refTokenIndex = $this->registerFormValidator->generateTokenIndex('ref_check');
                $this->refTokenValue = $this->registerFormValidator->generateTokenValue('ref_token');
                // Show success message
                $_SESSION['ref_success'] = true;
            }
        }
        // Update datas in form, error messages near fields, and notice error/success message
        return $result;
    }

    /**
     * Send a user registration activation email
     * @param array $result: register form datas
     * @return void
     */
    private function sendUserActivationEmail($result)
    {
        // Time limit to activate account (+ 2 days)
        $date = new \DateTime(date('d-m-Y H:i:s'));
        $date->add(new \DateInterval('P2D'));
        // Prepare email
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
        $headers .= 'From: "' . $this->config->getParam('websiteName') . '" <' . $this->config->getParam('contactForm.contactEmail') . '>'. "\r\n";
        $emailMessage = '<html><head></head><body>' . PHP_EOL .
        '<p style="width: 600px; margin: 0 auto; text-align:center;"><img src="' . $this->config::getParam('mailing.hostedImagesAbsoluteURL') . 'phpblog-logo.jpg" alt="phpBlog - Registration activation" with="150" height="150"></p>' . PHP_EOL .
        '<p style="width: 600px; margin: 0 auto; text-align:center;"><strong>ACCOUNT REGISTRATION ACTIVATION</strong><br><br></p>' . PHP_EOL .
        '<p style="width: 600px; margin: 0 auto; text-align:center; border-top: 2px solid #ffb236; border-bottom: 2px solid #2ca8ff;"><br>Dear ' . htmlentities($result['ref_firstName']) . ' ' . htmlentities($result['ref_familyName']) . ',<br>Thank you to be registered on <a href="' . $this->config->getParam('domain'). '" title="phpBlog"><font color="#888"><u><strong>' . $this->config->getParam('domain') . '</u></strong></font></a>.<br>Now, you have to activate your account to be able to use it on our website.<br>Please click on <a href="' . $this->config->getParam('domain') .'/admin/register/?userAccount=' . $result['ref_email'] . '&amp;activationKey=' . $result['ref_activationCode'] . '" title="Activate your user account"><font color="#f96332"><u>your personal link</u></font></a> to perform this action.<br>Important: please consider your account will be deleted automatically in 48 hours<br>if no activation happens before time limit: <strong>' . $date->format('d-m-Y H:i:s') . '</strong>.<br>Best regards.<br><br>&copy; ' . date('Y') . ' phpBlog<br><br></p>' . PHP_EOL .
        '</body></html>';
        // Send email
        mail( $result['ref_email'], 'Registration activation on ' . $this->config->getParam('websiteName'), $emailMessage, $headers);
    }

    /**
     * Activate user account if it's possible
     * @return array: error to show in message box, or empty array if activation is a success
     */
    private function activateUserAccount()
    {
        try {
            // Check email format
            $checkedEmail = isset($_GET['userAccount']) ? filter_var(trim($_GET['userAccount']), FILTER_VALIDATE_EMAIL) : false;
            if ($checkedEmail) {
                // Check email account in database
                $user = $this->currentModel->getUserByEmail($checkedEmail);
                // Existing user email account
                if ($user != false) {
                    // Account is already activated!
                    if ($user->isActivated) {
                        $info = isset($_GET['userAccount']) ? 'This account is already activated: <strong>' . htmlentities($_GET['userAccount']) . '</strong>!<br>' : '';
                        $result['ref_act_errors']['ref_act_register'] = $this->config::isDebug('<span class="form-check-notice">Sorry account activation failed!<br>' . $info . 'Please <a href="/#contact-us" class="text-muted text-lower" title="Contact us"><strong>contact us</strong></a> if it\'s necessary.<br></span>');
                        $activation = false;
                    } else {
                        // Activation code matches activation key parameter
                        if (isset($_GET['activationKey']) && $_GET['activationKey'] === $user->activationCode) {
                            $datas = [
                                'entity' => 'user',
                                'values' => [
                                    0 => [
                                        'type' => 4, // null
                                        'column' => 'activationCode',
                                        'value' => 'NULL' // set to NULL
                                    ],
                                    1 => [
                                        'type' => 2, // string
                                        'column' => 'activationDate',
                                        'value' => date('Y-m-d H:i:s') // set to current date time (SQL format)
                                    ],
                                    2 => [
                                        'type' => 1, // int
                                        'column' => 'isActivated',
                                        'value' => 1 // set to true
                                    ]
                                ]
                            ];
                            // Update user activation datas
                            $this->currentModel->updateEntity($user->id, $datas);
                            $activation = true;
                        } else {
                            // Wrong activation code
                            $info = isset($_GET['activationKey']) ? 'Your activation key is not valid: <strong>' . htmlentities($_GET['activationKey']) . '</strong>!<br>' : '';
                            $result['ref_act_errors']['ref_act_register'] = $this->config::isDebug('<span class="form-check-notice">Sorry account activation was refused!<br>' . $info . 'Please <a href="/#contact-us" class="text-muted text-lower" title="Contact us"><strong>contact us</strong></a> if it\'s necessary.<br></span>');
                            $activation = false;
                        }
                    }
                } else {
                    // Unknown user email account
                    $info = isset($_GET['userAccount']) ? 'This email address is unknown: <strong>' . htmlentities($_GET['userAccount']) . '</strong>!<br>' : '';
                    $result['ref_act_errors']['ref_act_register'] = $this->config::isDebug('<span class="form-check-notice">Sorry account activation was refused!<br>' . $info . 'Please <a href="/#contact-us" class="text-muted text-lower" title="Contact us"><strong>contact us</strong></a> if it\'s necessary.<br></span>');
                    $activation = false;
                }
            } else {
                // Invalid email
                $info = isset($_GET['userAccount']) ? 'This email address is not valid: <strong>' . htmlentities($_GET['userAccount']) . '</strong>!<br>' : '';
                $result['ref_act_errors']['ref_act_register'] = $this->config::isDebug('<span class="form-check-notice">Sorry account activation was refused!<br>' . $info . 'Please <a href="/#contact-us" class="text-muted text-lower" title="Contact us"><strong>contact us</strong></a> if it\'s necessary.<br></span>');
                $activation = false;
            }
        } catch (\PDOException $e) {
            $result['ref_act_errors']['ref_act_register'] = $this->config::isDebug('<span class="form-check-notice">Sorry a technical error happened! You are not able to activate your account at this time: please try again later<br>or <a href="/#contact-us" class="text-muted text-lower" title="Contact us"><strong>contact us</strong></a> if it\'s necessary.<br>[Debug trace: <strong>' . $e->getMessage() . '</strong>]</span>');
            $activation = false;
        }
        // User entity was activated successfully!
        if ($activation) {
            // Reset activation result
            $result = [];
            // Show success message
            $_SESSION['ref_act_success'] = true;
        }
        // Update notice error/success message
        return $result;
    }

    /**
     * Initialize admin login template parameters
     * @param array|null $checkedForm: an array which contains result of form validation (error on fields, filtered form values, ...), or null
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
            'success' => isset($_SESSION['lif_success']) ? $_SESSION['lif_success'] : false,
            // Update success state for password renewal success message box
            'passwordRenewalSuccess' => isset($_SESSION['rpf_success']) ? $_SESSION['rpf_success'] : false,
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
        // Is it already a succcess state for user when renewing password?
        // Enable password renewal success message box once a time
        if ($this->isRenewPasswordSuccess()) {
            // Do not store a success state anymore!
            unset($_SESSION['rpf_success']);
        }
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
    private function validateLoginForm()
    {
        // User is already authenticated.
        $user = $this->session::isUserAuthenticated();
        if ($user != false) {
            $result['lif_errors']['lif_login'] = '<span class="form-check-notice">Sorry, You are already authenticated!<br>So you don\'t need to login again...</span>';
            return $result;
        }
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
        $result = $this->loginFormCaptcha->call([isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : false, $result, 'lif_errors']);
        // Submit: login form is correctly filled.
        if (isset($result) && empty($result['lif_errors']) && isset($result['g-recaptcha-response']) && $result['g-recaptcha-response'] && isset($result['lif_check']) && $result['lif_check']) {
            try {
                // Check email account in database
                $user = $this->currentModel->getUserByEmail($result['lif_email']);
                // Email account exists in database
                if ($user != false) {
                    if ($user->isActivated == false) {
                        // User hasn't activated his account yet (or not at all)!
                        $result['lif_errors']['lif_login'] = $this->config::isDebug('<span class="form-check-notice">Sorry, authentication failed! Please activate your account first!<br>Please <a href="/#contact-us" class="text-muted text-lower" title="Contact us"><strong>contact us</strong></a> if it\'s necessary.<br>[Debug trace: user with email account "<strong>' . htmlentities($result['lif_email']) . '</strong>" must be activated in database!]</span>');
                    } else {
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
                                'userKey' => substr($this->generateUserSessionTokenValue('user' . $user->id . '_key'), 0, 30),
                                'userName' => [$user->firstName, $user->familyName],
                                'sessionId' => session_id(),
                                'sessionToken' => $this->generateUserSessionTokenValue('user' . $user->id . '_token')
                            ];
                        } else {
                            // No existing password (no existing password or no match with email account)
                            $result['lif_errors']['lif_login'] = $this->config::isDebug('<span class="form-check-notice">Sorry, authentication failed! Please check your email and password!<br>[Debug trace: hashed password string "<strong>' . htmlentities($result['lif_password']) . '</strong>" doesn\'t exist in database<br>or doesn\'t match email account "<strong>' . htmlentities($result['lif_email']) . '</strong>"!]</span>');
                        }
                    }
                } else {
                    // No existing email account
                    $result['lif_errors']['lif_login'] = $this->config::isDebug('<span class="form-check-notice">Sorry, authentication failed! Please check your email and password!<br>[Debug trace: email account "<strong>' . htmlentities($result['lif_email']) . '</strong>" doesn\'t exist in database!]</span>');
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
     * $_GET['userKey'] is a personal key to identify a particular user
     * This key is regenerated each time a new session is created.
     * @return void
     */
    public function logoutUser()
    {
        // Add control to check if there is an active user session
        $user = $this->session::isUserAuthenticated();
        // Destroy current user session
        if ($user != false && isset($_GET['userKey']) && $_GET['userKey'] == $_SESSION['user']['userKey']) {
            $this->session::destroy();
            $this->httpResponse->addHeader('Location: /admin/login');
            exit();
        } else {
            // Wrong user key is applied to logout!
            $this->httpResponse->set401ErrorResponse($this->config::isDebug('Logout refused: user was not recognized! [Debug trace: wrong user key is applied!]'), $this->router);
            exit();
        }
    }

    /**
     * Initialize admin request new password (forgotten) template parameters
     * @param array|null $checkedForm: an array which contains result of form validation (error on fields, filtered form values, ...), or null
     * @return array: an array of template parameters
     */
    public function initAdminRequestNewPassword($checkedForm = null)
    {
        $jsArray = [
            0 => [
                'placement' => 'bottom',
                'src' => '/assets/js/phpblog.js'
            ],
            1 => [
                'placement' => 'bottom',
                'src' => '/assets/js/forgetPassword.js'
            ]
        ];
        return [
            'JS' => $jsArray,
            'metaTitle' => 'Admin access - Forgotten password',
            'metaDescription' => 'Please use your email account to receive a password renewal authentication code.',
            'metaRobots' => 'noindex, nofollow',
            'imgBannerCSSClass' => 'admin-login',
            'email' => isset($checkedForm['fpf_email']) ? $checkedForm['fpf_email'] : '',
            // Token for request new password (forgotten) form
            'fpfTokenIndex' => $this->fpfTokenIndex,
            'fpfTokenValue' => $this->fpfTokenValue,
            // Does validation submit already exist with error?
            'tryValidation' => isset($_POST['fpf_submit']) ? 1 : 0,
            'submit' => isset($_SESSION['fpf_success']) && $_SESSION['fpf_success'] ? 1 : 0,
            'fpfNoSpam' => $this->captchaUIParams,
            // Error messages
            'errors' => isset($checkedForm['fpf_errors']) ? $checkedForm['fpf_errors'] : false,
            // Update success state on the same template
            'success' => isset($_SESSION['fpf_success']) ? $_SESSION['fpf_success'] : false
        ];
    }

    /**
     * Render admin request new password (forgotten) template (template based on Twig template engine)
     * @param array $vars: an array of template engine parameters
     * @return void
     */
    public function renderAdminRequestNewPassword($vars)
    {
        // Render minimalist template
        echo $this->page->renderTemplate('Admin/admin-forget-password-form.tpl', $vars);
    }

    /**
     * Check if there is already a success state for user new password (forgotten) request
     * @return boolean
     */
    private function isRequestNewPasswordSuccess() {
        if (isset($_SESSION['fpf_success'])) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Show default admin request new password (forgotten) template
     * @return void
     */
    public function showAdminRequestNewPassword()
    {
        // Set captcha values with initial values
        $this->forgetPasswordFormCaptcha->call(['customized' => [0 => 'setNoSpamFormValues']]);
        // Set captcha user interface
        $this->captchaUIParams = $this->forgetPasswordFormCaptcha->call(['customized' => [0 => 'setNoSpamFormElements']]);
        // Render default template
        $vars = $this->initAdminRequestNewPassword();
        $this->renderAdminRequestNewPassword($vars);
         // Is it already a succcess state for user when requesting new password?
        // Enable requesting new password success message box once a time
        if ($this->isRequestNewPasswordSuccess()) {
            // Do not store a success state anymore!
            unset($_SESSION['fpf_success']);
        }
    }

    /**
     * Send user an authentication code by email to renew his password (on submission) template
     * @return void
     */
    public function requestNewPassword()
    {
        // Set captcha values with submitted values
        $this->forgetPasswordFormCaptcha->call(['customized' => [0 => 'setNoSpamFormValues', 1 => [$_POST]]]);
        // Set captcha user interface
        $this->captchaUIParams = $this->forgetPasswordFormCaptcha->call(['customized' => [0 => 'setNoSpamFormElements']]);
        // Store result from request new password (forgotten) form validation
        $checkedForm = $this->validateRequestNewPasswordForm();
        // Is it already a succcess state?
        if ($this->isRequestNewPasswordSuccess()) {
            // Success state is returned: avoid previous $_POST with a redirection to the same page
            $this->httpResponse->addHeader('Location: /admin/request-new-password');
            exit();
        } else {
            // Render template when submitting form
            $vars = $this->initAdminRequestNewPassword($checkedForm);
            $this->renderAdminRequestNewPassword($vars);
        }
    }

    /**
     * Validate (or not) request new password (forgotten) form
     * @return array: an array which contains result of validation (error on fields, filtered form values, ...)
     */
    public function validateRequestNewPasswordForm()
    {
        // Prepare filters for form email data
        $datas = [
            0 => ['name' => 'email', 'filter' => 'email', 'modifiers' => ['trimStr']]
        ];
        // Filter user email input in $_POST datas
        $this->forgetPasswordFormValidator->filterDatas($datas);
        // Email
        $this->forgetPasswordFormValidator->validateEmail('email', 'email', $_POST['fpf_email']);
        // Check token to avoid CSRF
        $this->forgetPasswordFormValidator->validateToken(isset($_POST[$this->fpfTokenIndex]) ? $_POST[$this->fpfTokenIndex] : false);
        // Get validation result without captcha
        $result = $this->forgetPasswordFormValidator->getResult();
        // Update validation result with "no spam tools" captcha antispam validation
        $result = $this->forgetPasswordFormCaptcha->call([$result, 'fpf_errors']);
         // Submit: login form is correctly filled.
        if (isset($result) && empty($result['fpf_errors']) && isset($result['fpf_noSpam']) && $result['fpf_noSpam'] && isset($result['fpf_check']) && $result['fpf_check']) {
            try {
                // Check email account in database
                $user = $this->currentModel->getUserByEmail($result['fpf_email']);
                // Email account exists in database
                if ($user != false) {
                    // Prepare profile name to show in email
                    $result['fpf_firstName'] = $user->firstName;
                    $result['fpf_familyName'] = $user->familyName;
                    // Create password renewal authentication token to send to user (thanks to his email address!): he will use it in password renewal form
                    $result['fpf_passwordUpdateToken'] = $this->generateUserPasswordUpdateToken($result['fpf_email']);
                    $datas = [
                        'entity' => 'user',
                        'values' => [
                            0 => [
                                'type' => 2, // string
                                'column' => 'passwordUpdateToken',
                                'value' =>  $result['fpf_passwordUpdateToken'] // update token value
                            ]
                        ]
                    ];
                    // Update user password renewal authentication token and code datas
                    $this->currentModel->updateEntity($user->id, $datas);
                    $update = true;
                } else {
                    // No existing email account
                    $result['fpf_errors']['fpf_renewalCode'] = $this->config::isDebug('<span class="form-check-notice">Sorry, authentication failed! Please check your email!<br>[Debug trace: email account "<strong>' . htmlentities($result['fpf_email']) . '</strong>" doesn\'t exist in database!]</span>');
                    $update = false;
                }
            } catch (\PDOException $e) {
                $result['fpf_errors']['fpf_renewalCode'] = $this->config::isDebug('<span class="form-check-notice">Sorry a technical error happened! You are not able to get your authentication code at this time: please try again later.<br>[Debug trace: <strong>' . $e->getMessage() . '</strong>]</span>');
                $update = false;
            }
            // Authentication code was generated successfully!
            if ($update) {
                // Send user authentication code to renew his password
                $this->sendUserAuthenticationCodeEmail($result);
                // Reset request new password (forgotten) form
                $result = [];
                // Delete request new password (forgotten) form token
                unset($_SESSION['fpf_check']);
                unset($_SESSION['fpf_token']);
                // Regenerate token to be updated in request new password (forgotten) form
                $this->fpfTokenIndex = $this->forgetPasswordFormValidator->generateTokenIndex('fpf_check');
                $this->fpfTokenValue = $this->forgetPasswordFormValidator->generateTokenValue('fpf_token');
                // Show success message
                $_SESSION['fpf_success'] = true;
            }
        }
        // Update datas in form, error messages near fields, and notice error/success message
        return $result;
    }

    /**
     * Send a user authentication code email to renew his password
     * @param array $result: request new password (forgotten) form datas
     * @return void
     */
    public function sendUserAuthenticationCodeEmail($result) {
        // Time limit to use update token (+ 2 days)
        $date = new \DateTime(date('d-m-Y H:i:s'));
        $date->add(new \DateInterval('P2D'));
        // Prepare email
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
        $headers .= 'From: "' . $this->config->getParam('websiteName') . '" <' . $this->config->getParam('contactForm.contactEmail') . '>'. "\r\n";
        $emailMessage = '<html><head></head><body>' . PHP_EOL .
        '<p style="width: 600px; margin: 0 auto; text-align:center;"><img src="' . $this->config::getParam('mailing.hostedImagesAbsoluteURL') . 'phpblog-logo.jpg" alt="phpBlog - Registration activation" with="150" height="150"></p>' . PHP_EOL .
        '<p style="width: 600px; margin: 0 auto; text-align:center;"><strong>PASSWORD RENEWAL AUTHENTICATION CODE</strong><br><br></p>' . PHP_EOL .
        '<p style="width: 600px; margin: 0 auto; text-align:center; border-top: 2px solid #ffb236; border-bottom: 2px solid #2ca8ff;"><br>Dear ' . htmlentities($result['fpf_firstName']) . ' ' . htmlentities($result['fpf_familyName']) . ',<br>Here is your authentication code <strong>' . $result['fpf_passwordUpdateToken'] . '</strong> which is only valid on <a href="' . $this->config->getParam('domain'). '" title="phpBlog"><font color="#888"><u><strong>' . $this->config->getParam('domain') . '</u></strong></font></a>.<br>Now, you have to use it on our website, to renew your password.<br>Please click on <a href="' . $this->config->getParam('domain') .'/admin/renew-password" title="Renew your password"><font color="#f96332"><u>this link</u></font></a> to access a dedicated form.<br>Important: please consider your authentication code (token) will be deleted automatically in 48 hours<br>if no update happens before time limit: <strong>' . $date->format('d-m-Y H:i:s') . '</strong>.<br>Best regards.<br><br>&copy; ' . date('Y') . ' phpBlog<br><br></p>' . PHP_EOL .
        '</body></html>';
        // Send email
        mail( $result['fpf_email'], 'Password renewal authentication code to use on ' . $this->config->getParam('websiteName'), $emailMessage, $headers);
    }

    /**
     * Initialize admin renew password template parameters
     * @param array|null $checkedForm: an array which contains result of form validation (error on fields, filtered form values, ...), or null
     * @return array: an array of template parameters
     */
    public function initAdminRenewPassword($checkedForm = null)
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
                'src' => '/assets/js/renewPassword.js'
            ]
        ];
        return [
            'JS' => $jsArray,
            'metaTitle' => 'Admin access - Password renewal',
            'metaDescription' => 'Please use your email account and authentication token (code) to renew your password.',
            'metaRobots' => 'noindex, nofollow',
            'imgBannerCSSClass' => 'admin-login',
            'email' => isset($checkedForm['rpf_email']) ? $checkedForm['rpf_email'] : '',
            'passwordUpdateToken' => isset($checkedForm['rpf_passwordUpdateToken']) ? $checkedForm['rpf_passwordUpdateToken'] : '',
            'password' => isset($checkedForm['rpf_password']) ? $checkedForm['rpf_password'] : '',
            'passwordConfirmation' => isset($checkedForm['rpf_passwordConfirmation']) ? $checkedForm['rpf_passwordConfirmation'] : '',
            'siteKey' => $this->config::getParam('googleRecaptcha.siteKey'),
            // Token for renew new password form
            'rpfTokenIndex' => $this->rpfTokenIndex,
            'rpfTokenValue' => $this->rpfTokenValue,
            // Does validation submit already exist with error?
            'tryValidation' => isset($_POST['rpf_submit']) ? 1 : 0,
            'submit' => isset($_SESSION['rpf_success']) && $_SESSION['rpf_success'] ? 1 : 0,
            // Error messages
            'errors' => isset($checkedForm['rpf_errors']) ? $checkedForm['rpf_errors'] : false,
            // Update success state on the same template
            'success' => isset($_SESSION['rpf_success']) ? $_SESSION['rpf_success'] : false
        ];
    }

    /**
     * Render admin renew password template (template based on Twig template engine)
     * @param array $vars: an array of template engine parameters
     * @return void
     */
    public function renderAdminRenewPassword($vars)
    {
        // Render minimalist template
        echo $this->page->renderTemplate('Admin/admin-renew-password-form.tpl', $vars);
    }

    /**
     * Check if there is already a success state for user password renewal
     * @return boolean
     */
    private function isRenewPasswordSuccess() {
        if (isset($_SESSION['rpf_success'])) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Show default admin renew password template
     * @return void
     */
    public function showAdminRenewPassword()
    {
        // Render default template
        $vars = $this->initAdminRenewPassword();
        $this->renderAdminRenewPassword($vars);
    }

    /**
     * Renew user password (on submission) template
     * @return void
     */
    public function renewPassword()
    {
        // Store result from renew password form validation
        $checkedForm = $this->validateRenewPasswordForm();
        // Is it already a succcess state?
        if ($this->isRenewPasswordSuccess()) {
            // Success state is returned: avoid previous $_POST with a redirection to login page
            $this->httpResponse->addHeader('Location: /admin/login');
            exit();
        } else {
            // Render template when submitting form
            $vars = $this->initAdminRenewPassword($checkedForm);
            $this->renderAdminRenewPassword($vars);
        }
    }

    /**
     * Validate user password renewal form
     * @return array: an array which contains result of validation (error on fields, filtered form values, ...)
     */
    public function validateRenewPasswordForm()
    {
        // Prepare filters for form datas
        $datas = [
            0 => ['name' => 'email', 'filter' => 'email', 'modifiers' => ['trimStr']],
            1 => ['name' => 'passwordUpdateToken', 'filter' => 'alphanum', 'modifiers' => ['trimStr']]
            // Password must not be filtered (no sanitization), it will only be hashed to check matching in database.
        ];
        // Filter user inputs in $_POST datas
        $this->renewPasswordFormValidator->filterDatas($datas);
        // Authentication token
        $this->renewPasswordFormValidator->validateRequired('passwordUpdateToken', 'token');
        // Email
        $this->renewPasswordFormValidator->validateEmail('email', 'email', $_POST['rpf_email']);
        // Password update token (check length)
        $this->renewPasswordFormValidator->validatePasswordUpdateTokenLength('passwordUpdateToken', $_POST['rpf_passwordUpdateToken']);
        // Password
        $this->renewPasswordFormValidator->validatePassword('password', 'password', $_POST['rpf_password']);
        // Password confirmation
        $this->renewPasswordFormValidator->validatePassword('passwordConfirmation', 'password', isset($_POST['rpf_passwordConfirmation']) ? $_POST['rpf_passwordConfirmation'] : '');
        // Check if password confirmation and password are identical
        $this->renewPasswordFormValidator->validatePasswordConfirmation('passwordConfirmation', $_POST['rpf_password'], isset($_POST['rpf_passwordConfirmation']) ? $_POST['rpf_passwordConfirmation'] : '');
        // Check token to avoid CSRF
        $this->renewPasswordFormValidator->validateToken(isset($_POST[$this->rpfTokenIndex]) ? $_POST[$this->rpfTokenIndex] : false);
        // Get validation result without captcha
        $result = $this->renewPasswordFormValidator->getResult();
        // Update validation result with Google Recaptcha antispam validation
        $result = $this->renewPasswordFormCaptcha->call([isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : false, $result, 'rpf_errors']);
        // Submit: renew password form is correctly filled.
        if (isset($result) && empty($result['rpf_errors']) && isset($result['g-recaptcha-response']) && $result['g-recaptcha-response'] && isset($result['rpf_check']) && $result['rpf_check']) {
            try {
                // Check email account in database
                $user = $this->currentModel->getUserByEmail($result['rpf_email']);
                // Existing user email account
                if ($user != false) {
                    // Check user input authentication token value which must match password token value in database
                    $validPasswordUpdateToken = $this->checkPasswordUpdateTokenValue($result['rpf_passwordUpdateToken'], $user->passwordUpdateToken);
                    if ($validPasswordUpdateToken) {
                        // Hash user password before update
                        $result['rpf_password'] = $this->generateUserPasswordEncryption($result['rpf_password']);
                        $datas = [
                            'entity' => 'user',
                            'values' => [
                                0 => [
                                    'type' => 2, // string
                                    'column' => 'password',
                                    'value' =>  $result['rpf_password'] // set password value
                                ],
                                1 => [
                                    'type' => 2, // string
                                    'column' => 'passwordUpdateToken',
                                    'value' =>  'NULL' // set update token value
                                ],
                                2 => [
                                    'type' => 2, // string
                                    'column' => 'passwordUpdateDate',
                                    'value' =>  date('Y-m-d H:i:s') // set update date value
                                ]
                            ]
                        ];
                        // Update user password renewal authentication token and code datas
                        $this->currentModel->updateEntity($user->id, $datas);
                        $update = true;
                    } else {
                        // Show detailed error message in error box
                        $result['rpf_errors']['rpf_renewal'] = $this->config::isDebug('<span class="form-check-notice">Sorry, your token is not valid.<br>Please check your token value or corresponding email account.<br>[Debug trace: <strong>' . htmlentities($result['rpf_passwordUpdateToken']) . '</strong> does not match email account, or does not exist in database.]</span>');
                        // Show captcha error already checked before.
                        $result['rpf_errors']['g-recaptcha-response'] = 'Please confirm you are a human.';
                        $update = false;
                    }
                } else {
                    $result['rpf_errors']['rpf_renewal'] = $this->config::isDebug('<span class="form-check-notice">Sorry, your email account is not valid.<br>[Debug trace: <strong>' . htmlentities($result['rpf_email']) . '</strong> does not exist in database.]</span>');
                    $update = false;
                }
            } catch (\PDOException $e) {
                $result['rpf_errors']['rpf_renewal'] = $this->config::isDebug('<span class="form-check-notice">Sorry a technical error happened! You are not able to renew your password at this time: please try again later.<br>[Debug trace: <strong>' . $e->getMessage() . '</strong>]</span>');
                $update = false;
            }
            // User password was updated successfully!
            if ($update) {
                // Reset renew password form
                $result = [];
                // Delete renew password form token
                unset($_SESSION['rpf_check']);
                unset($_SESSION['rpf_token']);
                // Regenerate token to be updated in renew password form
                $this->rpfTokenIndex = $this->renewPasswordFormValidator->generateTokenIndex('rpf_check');
                $this->rpfTokenValue = $this->renewPasswordFormValidator->generateTokenValue('rpf_token');
                // Show success message
                $_SESSION['rpf_success'] = true;
            }
        }
        // Update datas in form, error messages near fields, and notice error/success message
        return $result;
    }
}