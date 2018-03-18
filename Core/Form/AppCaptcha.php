<?php
namespace Core\Form;
use Core\Config\AppConfig;

/**
 * Create a class to use a captcha in forms
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
     * @return mixed: type of return depends on called method
     */
    public function call($arguments)
    {
    	switch((new \ReflectionClass($this->captcha))->getShortName()) {
    		case 'ReCaptcha':
    			return call_user_func_array([$this, 'validateReCaptcha'], $arguments);
    		break;
            case 'AppNoSpamTools':
                if (isset($arguments['customized'])) {
                    return call_user_func_array([$this->captcha, $arguments['customized'][0]], isset($arguments['customized'][1]) ? $arguments['customized'][1] : []);
                } elseif ((is_array($arguments))) { // Only arguments
                    return call_user_func_array([$this, 'validateAppNoSpamTools'], $arguments);
                }
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
		if (isset($grcResponse)) {
			$return = $this->captcha->verify($grcResponse, $_SERVER['REMOTE_ADDR']);
			// Verify response
			if ($return->isSuccess()) {
				return true;
			} else {
				return [false, $return->getErrorCodes()];
			}
		} else {
			return false;
		}
	}

	/**
	 * Validate Google recaptcha (is it correctly checked?)
	 * @param string $grcResponse: value of 'g-recaptcha-response' in submitted form
	 * @param array $result: an array which contains form inputs values, errors with messages
	 * @param string $errorIndex: name of errors index related to submitted form
	 * @return array: $result is updated with captcha form validation
	 */
	public function validateReCaptcha($grcResponse, $result, $errorIndex)
	{
		if (isset($grcResponse)) {
			$response = $this->checkReCaptchaResponse($grcResponse);

			if (is_bool($response) && $response && empty($result[$errorIndex])) {
				$result['g-recaptcha-response'] = true;
			} elseif ((is_array($response) && !$response[0]) || (is_bool($response) && $response && !empty($result[$errorIndex]))) {
				$result[$errorIndex]['g-recaptcha-response'] = 'Please confirm you are a human.';
				$result['g-recaptcha-response'] = false;
			} else {
				$result[$errorIndex]['g-recaptcha-response'] = 'Sorry, a technical error happened.<br>We were not able to check if you are a human.<br>Please try again later.';
				$result['g-recaptcha-response'] = false;
			}
		}
		return $result;
	}

    /**
     * Validate customized no spam tools "captcha"
     * @param array $result: an array which contains form inputs values, errors with messages
     * @param string $errorIndex: name of errors index related to submitted form
     * @return array: $result is updated with captcha form validation
     */
    public function validateAppNoSpamTools($result, $errorIndex)
    {
        $formIdentifier = $this->captcha->getFormIdentifier();
        $usedTools = $this->captcha->getNoSpamToolsUsed();

        if ($usedTools[$formIdentifier . 'hpi']) { // Honeypot
            // Test if honeypot is an empty string
            if (isset($_REQUEST[$formIdentifier . 'hpi']) && $_REQUEST[$formIdentifier . 'hpi'] == '') {
                $result[$formIdentifier . 'noSpam'] = true;
            } else {
                $result[$formIdentifier . 'noSpam'] = false;
            }
        }
        if ($usedTools[$formIdentifier . 'tli']) { // Time limit (minimum amount of time to fill and submit a form)
             // Test if time limit is a valid timestamp
             // and if submitted time() - time limit > loaded form time()
            if (isset($_REQUEST[$formIdentifier . 'tli']) && $this->captcha->checkTimestamp($_REQUEST[$formIdentifier . 'tli'])
            && ((time() - $this->captcha->getTimeLimit()) > $_REQUEST[$formIdentifier . 'tli'])) {
                $result[$formIdentifier . 'noSpam'] = true;
            } else {
                $result[$formIdentifier . 'noSpam'] = false;
            }
        }
        if ($usedTools[$formIdentifier . 'hsi']) { // Human checkbox check
            // Test if human checkbox is checked
            if (isset($_REQUEST[$formIdentifier . 'hsi']) && $_REQUEST[$formIdentifier . 'hsi'] == 'on') {
                $result[$formIdentifier . 'noSpam'] = true;
            } else {
                $result[$formIdentifier . 'noSpam'] = false;
            }
        }
        // Other tests? Declare them and do stuff here!

        // Error message
        if (!$result[$formIdentifier . 'noSpam']) {
            $result[$errorIndex][$formIdentifier . 'noSpam'] = 'Spam bot behaviour seems to be detected!<br>Form can not be validated.<br>Please confirm you are a human.';
        }
        return $result;
    }
}