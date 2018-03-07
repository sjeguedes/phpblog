<?php
namespace App\Controllers\Home;
use App\Controllers\BaseController;
use Core\AppPage;
use Core\AppHTTPResponse;
use Core\Routing\AppRouter;
use Core\Config\AppConfig;
use Core\Service\AppContainer;

/**
 * Manage Homepage appearence and its actions on front-end
 */
class HomeController extends BaseController
{
    /**
     * @var object: an instance of validator object
     */
    private $contactFormValidator;
    /**
     * @var string: dynamic index name for contact form token
     */
    private $cfTokenIndex;
    /**
     * @var string: dynamic value for contact form token
     */
    private $cfTokenValue;
    /**
     * @var object: an instance of captcha object
     */
    private $contactFormCaptcha;
    /**
     * @var object: an instance of mailer object
     */
    private $contactFormMailer;
    /**
     * @var string: used to inform if contact entity is saved or not in database
     */
    private $insertionInfos;
    /**
     * @var string: used to inform if form contact message is sent
     */
    private $sendingInfos;

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
        // Get homepage model
        $this->currentModel = $this->getCurrentModel(__CLASS__);
        // Initialize contact form validator
        $this->contactFormValidator = AppContainer::getFormValidator()[0];
        // Define used parameters to avoid CSRF
        $this->cfTokenIndex = $this->contactFormValidator->generateTokenIndex('cf_check');
        $this->cfTokenValue = $this->contactFormValidator->generateTokenValue('cf_token', $this->config::getParam('contactForm.ajaxMode'));
        // Initialize contact form captcha
        $this->contactFormCaptcha = AppContainer::getCaptcha()[0];
        // Initialize contact form mailer
        $this->contactFormMailer = AppContainer::getMailer()[0];
    }

    /**
     * Action called on Homepage with routing: check AJAX request, $_POST, $_GET parameters
     * to call the right action
     * @return void
     */
    public function isCalled()
    {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            // Detect AJAX request to return token as JSON string for JS validation
            if (isset($_GET['cf_call']) && $_GET['cf_call'] == 'check-ajax') {
                $this->getCurrentToken();
            // Detect AJAX contact form submission
            } elseif (isset($_POST['cf_call']) && $_POST['cf_call'] == 'contact-ajax') {
                $this->showContact();
            }
        } else {
            // Detect only server side contact form submission
            if (isset($_POST['cf_call']) && $_POST['cf_call'] == 'contact') {
                $this->showContact();
            // Execute showHome entirely
            } elseif (isset($_GET['url']) && count($_GET) == 1) {
                $this->showHome();
            }
        }
    }

    /**
     * Check if there is already a success state for contact form
     * @return boolean
     */
    private function isContactSuccess() {
        if (isset($_SESSION['cf_success'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Show homepage with normal routing, call twig template and initialize contact form parameters
     * @return void
     */
    private function showHome()
    {
        // Show normal home view
        $jsArray = [
            0 => [
                'placement' => 'bottom',
                'attributes' => 'async defer',
                'src' => 'https://www.google.com/recaptcha/api.js?hl=en&onload=onloadCallback&render=explicit'
            ],
            1 => [
                'placement' => 'bottom',
                'src' => '/assets/js/sendContactMessage.js'
            ],
        ];
        $varsArray = [
            'ajaxModeForContactForm' => $this->config::getParam('contactForm.ajaxMode') ? 1 : 0,
            'JS' => $jsArray,
            'metaTitle' => 'Blog made with OOP in PHP code',
            'metaDescription' => 'This blog aims at showing and manage articles.',
            'imgBannerCSSClass' => 'home',
            'siteKey' => $this->config::getParam('googleRecaptcha.siteKey'),
            'cfTokenIndex' => $this->cfTokenIndex,
            'cfTokenValue' => $this->cfTokenValue,
            'submit' => 0,
            'success' => isset($_SESSION['cf_success']) && $_SESSION['cf_success'] ? true : false,
            'sending' => 0
        ];
        echo $this->page->renderTemplate('Home/home-index.tpl', $varsArray);
        // Is it already a succcess state?
        // this happens after no AJAX mode success redirection
        if ($this->isContactSuccess()) {
            unset($_SESSION['cf_success']);
        }
    }

    /**
     * Manage two modes to update contact form: AJAX and no AJAX twig templates
     * and call form validation
     * @return void
     */
    private function showContact()
    {
        // Store result from form validation
        $checkedForm = $this->validateContactForm();
        // Ajax mode is not used!
        if (!$this->config::getParam('contactForm.ajaxMode')) {
            // Success state is not returned.
            if (!$this->isContactSuccess()) {
                $jsArray = [
                    0 => [
                        'placement' => 'bottom',
                        'attributes' => 'async defer',
                        'src' => 'https://www.google.com/recaptcha/api.js?hl=en&onload=onloadCallback&render=explicit'
                    ],
                    1 => [
                        'placement' => 'bottom',
                        'src' => '/assets/js/sendContactMessage.js'
                    ],
                ];
                $varsArray = [
                    'ajaxModeForContactForm' => $this->config::getParam('contactForm.ajaxMode') ? 1 : 0,
                    'JS' => $jsArray,
                    'metaTitle' => 'Blog made with OOP in PHP code',
                    'metaDescription' => 'This blog aims at showing and manage articles.',
                    'imgBannerCSSClass' => 'home',
                    'familyName' => isset($checkedForm['cf_familyName']) ? $checkedForm['cf_familyName'] : '',
                    'firstName' => isset($checkedForm['cf_firstName']) ? $checkedForm['cf_firstName'] : '',
                    'email' => isset($checkedForm['cf_email']) ? $checkedForm['cf_email'] : '',
                    'message' => isset($checkedForm['cf_message']) ? $checkedForm['cf_message'] : '',
                    'siteKey' => $this->config::getParam('googleRecaptcha.siteKey'),
                    'cfTokenIndex' => $this->cfTokenIndex,
                    'cfTokenValue' => $this->cfTokenValue,
                    'submit' => isset($_SESSION['cf_success']) && $_SESSION['cf_success'] ? 1 : 0,
                    'errors' => isset($checkedForm['cf_errors']) ? $checkedForm['cf_errors'] : false,
                    'success' => isset($_SESSION['cf_success']) && $_SESSION['cf_success'] ? true : false,
                    'sending' => isset($checkedForm['cf_notSent']) && $checkedForm['cf_notSent'] ? 1 : 0
                ];
                // Render the entire page
                echo $this->page->renderTemplate('Home/home-index.tpl', $varsArray);
            // Success state is returned: avoid previous $_POST with a redirection.
            } else {
                $this->httpResponse->addHeader('Location: /');
            }
        }
        // Ajax mode
        else {
            $varsArray = [
                'ajaxModeForContactForm' => $this->config::getParam('contactForm.ajaxMode') ? 1 : 0,
                'familyName' => isset($checkedForm['cf_familyName']) ? $checkedForm['cf_familyName'] : '',
                'firstName' => isset($checkedForm['cf_firstName']) ? $checkedForm['cf_firstName'] : '',
                'email' => isset($checkedForm['cf_email']) ? $checkedForm['cf_email'] : '',
                'message' => isset($checkedForm['cf_message']) ? $checkedForm['cf_message'] : '',
                'siteKey' => $this->config::getParam('googleRecaptcha.siteKey'),
                'cfTokenIndex' => $this->cfTokenIndex,
                'cfTokenValue' => $this->cfTokenValue,
                'submit' => isset($_SESSION['cf_success']) && $_SESSION['cf_success'] ? 1 : 0,
                'errors' => isset($checkedForm['cf_errors']) ? $checkedForm['cf_errors'] : false,
                'success' => isset($_SESSION['cf_success']) && $_SESSION['cf_success'] ? true : false,
                'sending' => isset($checkedForm['cf_notSent']) && $checkedForm['cf_notSent'] ? 1 : 0
            ];
            // Render AJAX response with contact form only
            $this->httpResponse->addHeader('Cache-Control: no-cache, must-revalidate');
            echo $this->page->renderBlock('Home/home-contact-form.tpl', 'contactForm',  $varsArray);
            // Is it already a succcess state?
            if ($this->isContactSuccess()) {
                unset($_SESSION['cf_success']);
            }
        }
    }

    /**
     * echo a current token JSON string for contact form
     * @return void
     */
    private function getCurrentToken() {
        $this->httpResponse->addHeader('Content-Type:application/json; charset=utf-8');
        echo json_encode(['key' => $this->cfTokenIndex,'value' => $this->cfTokenValue]);
    }

    /**
     * Validate contact form and send message with services
     * @return array: an array of datas (error notice, fields error messages, input values)
     */
    private function validateContactForm()
    {
        // Prepare filters for form datas
        $datas = [
            0 => ['name' => 'familyName', 'filter' => 'alphanum', 'modifiers' => ['trimStr', 'strtoupperStr']],
            1 => ['name' => 'firstName',  'filter' => 'alphanum', 'modifiers' => ['trimStr', 'ucfirstStr']],
            2 => ['name' => 'email', 'filter' => 'email', 'modifiers' => ['trimStr']],
            3 => ['name' => 'message', 'filter' => 'alphanum', 'modifiers' => ['trimStr', 'ucfirstStr']]
        ];
        // Filter user inputs in $_POST datas
        $this->contactFormValidator->filterDatas($datas);
        // Family name
        $this->contactFormValidator->validateRequired('familyName', 'family name');
        // First name
        $this->contactFormValidator->validateRequired('firstName', 'first name');
        // Email
        $this->contactFormValidator->validateEmail('email', 'email', $_POST['cf_email']);
        // Message
        $this->contactFormValidator->validateRequired('message', 'message');
        // Check token to avoid CSRF: here it is more another antispam method for contact form!
        $this->contactFormValidator->validateToken(isset($_POST[$this->cfTokenIndex]) ? $_POST[$this->cfTokenIndex] : false);
        // Get validation result without captcha
        $result = $this->contactFormValidator->getResult();
        // Update validation result with Google recaptcha antispam validation
        $result = $this->contactFormCaptcha->call([$_POST['g-recaptcha-response'], $result, 'cf_errors']);
        // Submit: contact form is correctly filled.
        if (isset($result) && empty($result['cf_errors']) && isset($result['g-recaptcha-response']) && $result['g-recaptcha-response'] && isset($result['cf_check']) && $result['cf_check']) {
            // Insert Contact entity in database to keep it
            try {
                $this->currentModel->insertContact($result);
                $this->insertionInfos = '<span style="color:#ffffff;background-color:#18ce0f;padding:5px">Success notice - Contact entity was successfully saved in database.</span>';
            } catch (\PDOException $e) {
                $this->insertionInfos = '<span style="color:#ffffff;background-color:#ff3636;padding:5px">Error warning - Contact entity was unsaved in database:</span><br><br><span style="color:#ffffff;background-color:#ff3636;padding:5px">' . $e->getMessage() . '</span>';
            }
            // Is message sent?
            $datas = [
                'firstName' => $result['cf_firstName'],
                'familyName' => $result['cf_familyName'],
                'email' => $result['cf_email'],
                'message' => $result['cf_message'],
            ];
            $isMailSent = $this->contactFormMailer->call([$datas, $this->insertionInfos, $this->sendingInfos]);
            // Email was sent.
            if ($isMailSent) {
                // Reset the form
                $result = [];
                // Delete current token
                unset($_SESSION['cf_check']);
                unset($_SESSION['cf_token']);
                // Regenerate token to be updated in form
                $this->cfTokenIndex = $this->contactFormValidator->generateTokenIndex('cf_check');
                $this->cfTokenValue = $this->contactFormValidator->generateTokenValue('cf_token');
                // Show success message
                $_SESSION['cf_success'] = true;
            // Email was not sent!
            } else {
                // Feed "data-not-sent" attribute on contact form.
                $result['cf_notSent'] = true;
                // Warn user if sending failed.
                $result['cf_errors']['cf_sending'] = 'Sorry, a technical error happened.<br>Your message was not sent.';
                if ($this->config::getParam('appDebug') && !empty($this->sendingInfos['error'])) {
                    // Show more details in case of debug mode
                    $result['cf_errors']['cf_sending'] .= '<br>' . $this->sendingInfos['error'];
                }
            }
        }
        // Update datas in form, error messages near fields, and notice error/success message
        return $result;
    }
}