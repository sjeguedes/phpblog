<?php
namespace Core\Service;
use Core\Config\AppConfig;
use Core\AppHTTPResponse;
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
	/**
	 * @var object: unique instance of AppContainer
	 */
	private static $_instance;
	/**
	 * @var object: AppConfig
	 */
	private static $_config;
    /**
     * @var object: an instance of AppHTTPResponse
     */
    private static $_httpResponse;
	/**
	 * @var array: an array of arguments for called services
	 */
	private static $_params;

	/**
	 * Singleton
	 * @return object AppContainer
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
     * @return void
     */
    private function __construct()
    {
    	self::$_config = AppConfig::getInstance();
        self::$_httpResponse = new AppHTTPResponse();
    	self::$_params = self::addParameters();
    }

    /**
    * Magic method __clone
    * @return void
    */
    public function __clone()
    {
        self::$_httpResponse->set404ErrorResponse(self::isDebug('Technical error [Debug trace: Don\'t try to clone singleton ' . __CLASS__ . '!]'));
    }

    /**
     * Feed DIC parameters
     * @return array an array of service parameters
     */
    private static function addParameters()
    {
    	// Get parameters from yaml file
		$yaml = self::$_config::parseYAMLFile(__DIR__ . '/service.yml');
		return $yaml;
    }

	/**
	 * Create form validator instances
	 * @return array; an array of AppFormValidator instances
	 */
	public static function getFormValidator()
	{
		for ($i = 0; $i < count(self::$_params['service']['formValidator']); $i++) {
			switch (self::$_params['service']['formValidator'][$i]['formDatasRequest']) {
				case 'POST':
					$validators[$i] = new AppFormValidator($_POST, self::$_params['service']['formValidator'][$i]['formIdentifier']);
				break;
				case 'GET':
					$validators[$i] = new AppFormValidator($_GET, self::$_params['service']['formValidator'][$i]['formIdentifier']);
				break;
				// Other types: do stuff here!
			}
		}
		return $validators;
	}

	/**
	 * Create captcha instances
	 * @see https://www.google.com/recaptcha/admin key generator
	 * @return array; an array of AppCaptcha with ReCaptcha|AppNoSpamTools|... instances
	 */
	public static function getCaptcha()
	{
		for ($i = 0; $i < count(self::$_params['service']['captcha']); $i++) {
			switch (self::$_params['service']['captcha'][$i]['type']) {
				case 'ReCaptcha':
					$captchas[$i] = new AppCaptcha(new ReCaptcha(self::$_config::getParam('googleRecaptcha.secretKey')));
				break;
                case 'AppNoSpamTools':
                    $captchas[$i] = new AppCaptcha(new AppNoSpamTools(self::$_params['service']['captcha'][$i]));
                break;
				// Other types: do stuff here!
			}
		}
		return $captchas;
	}

	/**
	 * Create mailer instances
	 * @see https://github.com/PHPMailer/PHPMailer send a mail with its proper configuration
	 * @return array; an array of AppMailer with PHPMailer|... instances
	 */
	public static function getMailer()
	{
		for ($i = 0; $i < count(self::$_params['service']['mailer']); $i++) {
			switch (self::$_params['service']['mailer'][$i]['type']) {
				case 'PHPMailer':
					$mailers[$i] = new AppMailer(new PHPMailer(self::$_config::getParam('contactPHPMailer.enableExceptions')), self::$_params['service']['mailer'][$i]['sendingMethod'], self::$_params['service']['mailer'][$i]['use']);
				break;
				// Other types: do stuff here!
			}
		}
		return $mailers;
	}
}