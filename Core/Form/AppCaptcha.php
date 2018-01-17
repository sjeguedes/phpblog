<?php
namespace Core\Form;
use Core\Config\AppConfig;

// Composer autoloader
if (!class_exists('Composer\\Autoload\\ClassLoader'))
{
	require_once __DIR__ . '/../../Libs/vendor/autoload.php';
}

// Import Google recaptcha component
use ReCaptcha\ReCaptcha;

/**
 * Class to use captcha in form
 */
class AppCaptcha
{
	/**
	 * @var object: captcha to use
	 */
	private $captcha;
	/**
	 * @var object: config to use
	 */
	private $config;
	/**
	 * Constructor
	 * @param object $captcha: an instance of one type of captcha object 
	 * @return void
	 */
	
	public function __construct($captcha)
    {
        $this->captcha = $captcha;
        $this->config = AppConfig::getInstance();
    }
    
    /**
     * Call the right method and depends on called object
     * @param array $arguments: an array of $arguments to feed a method
     * @return mixed: return depends on called method
     */
    public function call($arguments)
    {
    	switch((new \ReflectionClass($this->captcha))->getShortName()) {
    		case 'ReCaptcha':
    			return call_user_func_array([$this, 'validateReCaptcha'], $arguments);
    		break;
    		// Other types: do stuff here
    	}
    }

    /**
	 * Check Google recaptcha response with ReCaptcha instance
	 * @param string $grcResponse value of 'g-recaptcha-response' in submitted form
	 * @return mixed boolean or an array which contains boolean and string
	 */
	public function checkReCaptchaResponse($grcResponse) 
	{
		if(isset($grcResponse)) {
			$return = $this->captcha->verify($grcResponse, $_SERVER['REMOTE_ADDR']);

			// Verify response
			if ($return->isSuccess()) {
				return true;
			}
			else {
				return [false, $return->getErrorCodes()];
			}
		}
		else {
			return false;
		}
	}

	/**
	 * Validate Google recaptcha (is it correctly checked?)
	 * @param string $grcResponse: value of 'g-recaptcha-response' in submitted form
	 * @param array $result: an array which contains form inputs values, errors with messages
	 * @param string $errorIndex: name of errors index related to submitted form 
	 * @return void
	 */
	public function validateReCaptcha($grcResponse, $result, $errorIndex)
	{
		if(isset($grcResponse)) {
			$response = $this->checkReCaptchaResponse($grcResponse);

			if(is_bool($response) && $response && empty($result[$errorIndex])) {
				$result['g-recaptcha-response'] = true;
			}
			elseif((is_array($response) && !$response[0]) || (is_bool($response) && $response && !empty($result[$errorIndex]))) {
				$result[$errorIndex]['g-recaptcha-response'] = 'Please confirm you are a human.';
				$result['g-recaptcha-response'] = false;
			}
			else {
				$result[$errorIndex]['g-recaptcha-response'] = 'Sorry, a technical error happened.<br>We were not able to check if you are a human.<br>Please try again later.';
				$result['g-recaptcha-response'] = false;
			}
		}
		return $result;
	}
}