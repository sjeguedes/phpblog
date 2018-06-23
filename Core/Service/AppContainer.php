<?php
namespace Core\Service;

use Core\Form\AppFormValidator;
use Core\Form\AppCaptcha;
use Core\Form\AppMailer;
// Import PHPMailer component
use PHPMailer\PHPMailer\PHPMailer;
// Import Google recaptcha component
use ReCaptcha\ReCaptcha;
// Custom antispam tools
use Core\Form\Element\AppNoSpamTools;

/**
 * Create a DIC (Dependency Injection Container)
 */
class AppContainer
{
    use \Core\Helper\Shared\UseRouterTrait;
    use \Core\Helper\Shared\UseConfigTrait;
    use \Core\Helper\Shared\UseHTTPResponseTrait;

    /**
     * @var object: unique instance of AppContainer
     */
    private static $_instance;
    /**
     * @var AppRouter instance
     */
    private static $_router;
    /**
     * @var AppConfig instance
     */
    private static $_config;
    /**
     * @var AppHTTPResponse instance
     */
    private static $_httpResponse;
    /**
     * @var array: an array of arguments for called services
     */
    private static $_params;

    /**
     * Instanciate a unique AppContainer object (Singleton)
     *
     * @return AppContainer: a unique instance of AppContainer
     */
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new AppContainer();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     *
     * @return void
     */
    private function __construct()
    {
        // WARNING: don't initialize properties from helpers "Traits" here!
    }

    /**
    * Magic method __clone
    *
    * @return void
    */
    public function __clone()
    {
        self::$_httpResponse->set404ErrorResponse(self::$_config::isDebug('Technical error [Debug trace: Don\'t try to clone singleton ' . __CLASS__ . '!]'), self::$_router);
        exit();
    }

    /**
     * Get DIC parameters
     *
     * @return array: an array of service parameters
     */
    private static function getParams()
    {
        // Get parameters from yaml file
        $yaml = self::$_config::parseYAMLFile(__DIR__ . '/service.yml');
        return $yaml;
    }

    /**
     * Set DIC parameters
     *
     * @param array $params: an array of service parameters
     *
     * @return void
     */
    private static function setParams($params)
    {
        // Get parameters from yaml file
        self::$_params = self::getParams();
    }

    /**
     * Init DIC
     *
     * @return void
     */
    public static function init()
    {
        $params = self::getParams();
        self::setParams($params);
    }

    /**
     * Create form validator instances
     *
     * @return array; an array of AppFormValidator instances
     */
    public static function getFormValidator()
    {
        for ($i = 0; $i < count(self::$_params['service']['formValidator']); $i++) {
            switch (self::$_params['service']['formValidator'][$i]['formDatasRequest']) {
                case 'POST':
                    $validators[$i] = new AppFormValidator(self::$_router, $_POST, self::$_params['service']['formValidator'][$i]['formIdentifier']);
                    break;
                case 'GET':
                    $validators[$i] = new AppFormValidator(self::$_router, $_GET, self::$_params['service']['formValidator'][$i]['formIdentifier']);
                    break;
                // Other types: do stuff here!
            }
        }
        return $validators;
    }

    /**
     * Create captcha instances
     *
     * @see https://www.google.com/recaptcha/admin key generator
     *
     * @return array; an array of AppCaptcha with ReCaptcha|AppNoSpamTools|... instances
     */
    public static function getCaptcha()
    {
        for ($i = 0; $i < count(self::$_params['service']['captcha']); $i++) {
            switch (self::$_params['service']['captcha'][$i]['type']) {
                case 'ReCaptcha':
                    $captchas[$i] = new AppCaptcha(new ReCaptcha(self::$_config::getParam('googleRecaptcha.secretKey')), self::$_router);
                    break;
                case 'AppNoSpamTools':
                    $captchas[$i] = new AppCaptcha(new AppNoSpamTools(self::$_router, self::$_params['service']['captcha'][$i]), self::$_router);
                    break;
                // Other types: do stuff here!
            }
        }
        return $captchas;
    }

    /**
     * Create mailer instances
     *
     * @see https://github.com/PHPMailer/PHPMailer send a mail with its proper configuration
     *
     * @return array; an array of AppMailer with PHPMailer|... instances
     */
    public static function getMailer()
    {
        for ($i = 0; $i < count(self::$_params['service']['mailer']); $i++) {
            switch (self::$_params['service']['mailer'][$i]['type']) {
                case 'PHPMailer':
                    $mailers[$i] = new AppMailer(new PHPMailer(self::$_config::getParam('contactPHPMailer.enableExceptions')), self::$_router, self::$_params['service']['mailer'][$i]['sendingMethod'], self::$_params['service']['mailer'][$i]['use']);
                    break;
                // Other types: do stuff here! Example: swiftMailer
            }
        }
        return $mailers;
    }
}
