<?php
namespace Core\Service;
use Core\Config\AppConfig;
use Core\Form\AppFormValidator;
use Core\Form\AppCaptcha;
use Core\Form\AppMailer;
// Import PHPMailer component
use PHPMailer\PHPMailer\PHPMailer;
// Import Google recaptcha component
use ReCaptcha\ReCaptcha;

/**
 * Class to create a DIC (Dependency Injection Container)
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
    	self::$_params = $this->addParameters();
    }

    /**
     * Feed DIC parameters
     * @return array an array of service parameters
     */
    private function addParameters() 
    {
    	// Get parameters from yaml file
		$yaml = self::$_config::parseYAMLFile(__DIR__ . '/service.yml');
		return $yaml;
    }

	/**
	 * Create form validator instances
	 * @return AppFormValidator
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
	 * @return ReCaptcha|...
	 */
	public static function getCaptcha()
	{
		for ($i = 0; $i < count(self::$_params['service']['captcha']); $i++) {
			switch (self::$_params['service']['captcha'][$i]['type']) {
				case 'ReCaptcha':
					$captchas[$i] = new AppCaptcha(new ReCaptcha(self::$_config::$_params['googleRecaptcha']['secretKey']));
				break;
				// Other types: do stuff here!
			}

		}
		return $captchas;
	}

	/**
	 * Create mailer instances
	 * @see https://github.com/PHPMailer/PHPMailer send a mail with its proper configuration
	 * @return AppMailer with PHPMailer|...
	 */
	public static function getMailer()
	{
		for ($i = 0; $i < count(self::$_params['service']['mailer']); $i++) {
			switch (self::$_params['service']['mailer'][$i]['type']) {
				case 'PHPMailer':
					$mailers[$i] = new AppMailer(new PHPMailer(self::$_config::$_params['contactPHPMailer']['EnableExceptions']), self::$_params['service']['mailer'][$i]['sendingMethod'], self::$_params['service']['mailer'][$i]['use']);
				break;
				// Other types: do stuff here!
			}

		}
		return $mailers;
	}
}