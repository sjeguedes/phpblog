<?php
namespace App\Controllers\Home;
use App\Controllers\BaseController;
use Core\AppPage;
use Core\AppHTTPResponse;
use Core\Routing\AppRouter;
use Core\Config\AppConfig;
/**
 *
 */
class HomeController extends BaseController
{
	private $cfTokenIndex;
	private $cfTokenValue;

	public function __construct(AppPage $page, AppHTTPResponse $httpResponse, AppRouter $router, AppConfig $config)
	{
		parent::__construct($page, $httpResponse, $router, $config);
		$this->currentModel = $this->getCurrentModel(__CLASS__);

		// Used to avoid CSRF
		$this->cfTokenIndex = $this->generateTokenIndex('cf_check');
		$this->cfTokenValue = $this->generateTokenValue('cf_token');
	}

	public function isCall()
	{
		// Detect both AJAX and server side contact form submission
		if((isset($_POST['cf_call']) && is_string($_POST['cf_call']) && $_POST['cf_call'] == 'contact')) {
			$this->sendContactMessage();
			return true;
		}
		// Detect AJAX request to return token as JSON string for JS validation
		elseif(!isset($_POST['cf_call']) && isset($_GET['cf_call']) && is_string($_GET['cf_call']) && $_GET['cf_call'] == 'check') { 
			$this->getCurrentToken();
			return true;
		}
		return false;
	}

	public function isContactMessageSuccess()
	{
		// Hide success notice message if it exists in case of page reload and make a redirection
		if(isset($_SESSION['cf_success'])) {
			if(!$this->config::$_params['contactForm']['ajaxMode']) {
				$this->httpResponse->addHeader('Location: /');
			}
			unset($_SESSION['cf_success']);
		}
	}

	public function showHome($matches)
	{
		// Show or hide success notice box
		$this->isContactMessageSuccess();

		// Check if there is no particular cases and then execute normal action
		if(!$this->isCall()):
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
				'ajaxModeForContactForm' => $this->config::$_params['contactForm']['ajaxMode'],
				'JS' => $jsArray,
				'metaTitle' => 'Blog made with OOP in PHP code',
				'metaDescription' => 'This blog aims at showing and manage articles.',
				'imgBannerCSSClass' => 'home',
				'siteKey' => $this->config::$_params['googleRecaptcha']['siteKey'],
				'cfTokenIndex' => $this->cfTokenIndex,
				'cfTokenValue' => $this->cfTokenValue,
				'submit' => isset($_SESSION['cf_success']) && $_SESSION['cf_success'] ? 1 : 0,
				'success' => isset($_SESSION['cf_success']) && $_SESSION['cf_success'] ? $_SESSION['cf_success'] : false
			];
			echo $this->page->renderTemplate('Home/home-index.tpl', $varsArray);
		endif;
	}

	public function sendContactMessage()
	{
		// Stores result of form validation
		$checkedForm = $this->validateContactForm();

		// Ajax mode is not used!
		if(!$this->config::$_params['contactForm']['ajaxMode']) {
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
				'ajaxModeForContactForm' => $this->config::$_params['contactForm']['ajaxMode'],
				'JS' => $jsArray,
				'metaTitle' => 'Blog made with OOP in PHP code',
				'metaDescription' => 'This blog aims at showing and manage articles.',
				'metaRobots' => 'noindex, nofollow',
				'imgBannerCSSClass' => 'home',
				'familyName' => isset($checkedForm['familyName']) ? $checkedForm['familyName'] : '',
				'firstName' => isset($checkedForm['firstName']) ? $checkedForm['firstName'] : '',
				'email' => isset($checkedForm['email']) ? $checkedForm['email'] : '',
				'message' => isset($checkedForm['message']) ? $checkedForm['message'] : '',
				'siteKey' => $this->config::$_params['googleRecaptcha']['siteKey'],
				'cfTokenIndex' => $this->cfTokenIndex,
				'cfTokenValue' => $this->cfTokenValue,
				'submit' => isset($_SESSION['cf_success']) && $_SESSION['cf_success'] ? 1 : 0,			
				'errors' => isset($checkedForm['cf_errors']) ? $checkedForm['cf_errors'] : false,
				'success' => isset($_SESSION['cf_success']) && $_SESSION['cf_success'] ? $_SESSION['cf_success'] : false
			]; 

			// Render the entire page
			echo $this->page->renderTemplate('Home/home-index.tpl', $varsArray);
		}
		// Ajax mode
		else {
			$varsArray = [
				'ajaxModeForContactForm' => $this->config::$_params['contactForm']['ajaxMode'],
				'familyName' => isset($checkedForm['familyName']) ? $checkedForm['familyName'] : '',
				'firstName' => isset($checkedForm['firstName']) ? $checkedForm['firstName'] : '',
				'email' => isset($checkedForm['email']) ? $checkedForm['email'] : '',
				'message' => isset($checkedForm['message']) ? $checkedForm['message'] : '',
				'siteKey' => $this->config::$_params['googleRecaptcha']['siteKey'],
				'cfTokenIndex' => $this->cfTokenIndex,
				'cfTokenValue' => $this->cfTokenValue,
				'submit' => isset($_SESSION['cf_success']) && $_SESSION['cf_success'] ? 1 : 0,	
				'errors' => isset($checkedForm['cf_errors']) ? $checkedForm['cf_errors'] : false,
				'success' => isset($_SESSION['cf_success']) && $_SESSION['cf_success'] ? $_SESSION['cf_success'] : false
			]; 

			// Render AJAX response with contact form only
			$this->httpResponse->addHeader('Cache-Control: no-cache, must-revalidate');
			echo $this->page->renderBlock('Home/home-contact-form.tpl', 'contactForm',  $varsArray);
		}
	}

	private function validateContactForm()
	{
		// Will store main data, errors, success state...
		$result = [];
		// Prepare filters for each user input
		$args = [
			'cf_familyName' => [
				'filter' => FILTER_SANITIZE_STRING,
				'flags' => FILTER_CALLBACK,
				'options' => [function($var) { trim($var); }]
			],
			'cf_firstName' => [
				'filter' => FILTER_SANITIZE_STRING,
				'flags' => FILTER_CALLBACK,
				'options' => [function($var) { trim($var); }]
			],
			'cf_email' => [
				'filter' => FILTER_SANITIZE_EMAIL,
			],
			'cf_message' => [
				'filter' => FILTER_SANITIZE_STRING,
				'flags' => FILTER_CALLBACK,
				'options' => [function($var) { trim($var); }]
			]

		];
		$filteredValues = filter_input_array(INPUT_POST, $args);

		// Family name
		if(array_key_exists('cf_familyName', $_POST)) {
			if(trim($_POST['cf_familyName']) == '') {
				$result['cf_errors']['familyName'] = 'Please fill in your family name.';
			}
			elseif(!is_string($_POST['cf_familyName'])) {
				$result['cf_errors']['familyName'] = 'Please verify your family name format.';
			}
			else {
				$result['familyName'] = ucwords($filteredValues['cf_familyName']);
				unset($result['cf_errors']['familyName']);
			}
		}

		// First name
		if(array_key_exists('cf_firstName', $_POST)) {
			if(trim($_POST['cf_firstName']) == '') {
				$result['cf_errors']['firstName'] = 'Please fill in your first name.';
			}
			elseif(!is_string($_POST['cf_firstName'])) {
				$result['cf_errors']['firstName'] = 'Please verify your first name format.';
			}
			else {
				$result['firstName'] = ucfirst($filteredValues['cf_firstName']);
				unset($result['cf_errors']['firstName']);
			}
		}

		// Email
		if(array_key_exists('cf_email', $_POST)) {
			if(trim($_POST['cf_email']) == '') {
			$result['cf_errors']['email'] = 'Please fill in your email address.';
			}
			else {
				if(!filter_var($_POST['cf_email'], FILTER_VALIDATE_EMAIL)) {
					$result['cf_errors']['email'] = 'Sorry, <span class="text-muted">' . $_POST['cf_email'] . '</span> is not a valid email address!<br>Extra spaces before/after or forbidden characters could prevent validation.';
				}
				else {
					unset($result['cf_errors']['email']);
				}
				$result['email'] = $filteredValues['cf_email'];
			}
		}

		// Message
		if(array_key_exists('cf_message', $_POST)) {
			if(trim($_POST['cf_message']) == '') {
				$result['cf_errors']['message'] = 'Please fill in your message.';
			}
			elseif(!is_string($_POST['cf_message'])) {
				$result['cf_errors']['message'] = 'Please verify your message format.';
			}
			else {
				$result['message'] = $filteredValues['cf_message'];
				unset($result['cf_errors']['message']);
			}
		}

		// Google recaptcha antispam
		if(isset($_POST['g-recaptcha-response'])) {
			$googleRecaptchaResponse = $this->checkGoogleRecaptchaResponse($_POST['g-recaptcha-response']);

			if(is_bool($googleRecaptchaResponse) && $googleRecaptchaResponse) {
				$result['googleRecaptchaResponse'] = true;
			}
			elseif(is_array($googleRecaptchaResponse) && !$googleRecaptchaResponse[0]) {
				$result['cf_errors']['g-recaptcha-response'] = 'Please confirm you are a human.';
				$result['googleRecaptchaResponse'] = false;
			}
			else {
				$result['cf_errors']['g-recaptcha-response'] = 'Sorry, a technical error happened.<br>We were not able to check if you are a human.<br>Please try again later.';
				$result['googleRecaptchaResponse'] = false;
			}
		}

		// Check token to avoid CSRF: here it is more another antispam method for contact form!
		if(isset($_POST[$this->cfTokenIndex]) && isset($_SESSION['cf_token'])) {
			if($this->checkTokenValue($_POST[$this->cfTokenIndex], 'cf_token')) {
				$result['check'] = true;
			}
			else {
				$result['cf_errors']['check'] = '<span class="cf-check-notice">You are not allowed to use the form like this!<br>Please do not follow the dark side of the force... ;-)</span>';
				$result['check'] = false;
			}
		}
		// Wrong token index or anything else happened
		else {
			$result['cf_errors']['check'] = '<span class="cf-check-notice">You are not allowed to use the form like this!<br>Please do not follow the dark side of the force... ;-)</span>';
			$result['check'] = false;
		}

		// Submit
		if(isset($result) && empty($result['cf_errors']) && $result['googleRecaptchaResponse'] && $result['check']) {
			/* -- TODO: to add for next commit -- */
			
			// 1. Insert Contact entity in database
			// -> Do stuff here

			// 2. Send email
			// -> Do stuff here

			/* -- END TODO: to add for next commit -- */

			// Reset the form
			$result = [];
			
			// Delete current token
			unset($_SESSION['cf_check']);
			unset($_SESSION['cf_token']);

			// Regenerate token to be updated in form
			session_regenerate_id(true);
			$this->cfTokenIndex = $this->generateTokenIndex('cf_check');
			$this->cfTokenValue = $this->generateTokenValue('cf_token');

			// Show success message
			$_SESSION['cf_success'] = true;
		}
		else {
			// Return $result as an array of value(s)
			return $result;
		}
	}

	public function getCurrentToken() {
		$this->httpResponse->addHeader('Content-Type:application/json; charset=utf-8');
		echo json_encode(['key' => $this->cfTokenIndex,'value' => $this->cfTokenValue]);
	}
}