<?php
namespace App\Controllers\Home;
use App\Controllers\BaseController;
use Core\AppPage;
use Core\AppHTTPResponse;
use Core\Routing\AppRouter;
use Core\Config\AppConfig;

// Composer autoloader
if( !class_exists('Composer\\Autoload\\ClassLoader') )
{
	require_once __DIR__ . '/../../../Libs/vendor/autoload.php';
}

// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
/**
 *
 */
class HomeController extends BaseController
{
	private $cfTokenIndex;
	private $cfTokenValue;
	private $previousSuccess;
	private $insertionInfos;
	private $sendingInfos = [];

	public function __construct(AppPage $page, AppHTTPResponse $httpResponse, AppRouter $router, AppConfig $config)
	{
		parent::__construct($page, $httpResponse, $router, $config);
		$this->currentModel = $this->getCurrentModel(__CLASS__);

		// Used to avoid CSRF
		$this->cfTokenIndex = $this->generateTokenIndex('cf_check');
		$this->cfTokenValue = $this->generateTokenValue('cf_token');
	}

	public function isCalled()
	{
		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			// Detect AJAX request to return token as JSON string for JS validation
			if(isset($_GET['cf_call']) && is_string($_GET['cf_call']) && $_GET['cf_call'] == 'check-ajax') { 
				$this->getCurrentToken();
			}
			// Detect AJAX contact form submission
			elseif(isset($_POST['cf_call']) && is_string($_POST['cf_call']) && $_POST['cf_call'] == 'contact-ajax') {
				$this->sendContactMessage();
			}
		}
		else {
			// Detect only server side contact form submission
			if(isset($_POST['cf_call']) && is_string($_POST['cf_call']) && $_POST['cf_call'] == 'contact') {
				$this->sendContactMessage();
			}
			// Execute showHome entirely
			elseif(isset($_GET['url']) && count($_GET) == 1) {
				$this->showHome();
			}
		}
	}

	public function isContactSuccess() {
		if(isset($_SESSION['cf_success'])) {
			return true;
		}
		else {
			return false;
		}
	}

	public function showHome()
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
			'ajaxModeForContactForm' => $this->config::$_params['contactForm']['ajaxMode'] ? 1 : 0,
			'JS' => $jsArray,
			'metaTitle' => 'Blog made with OOP in PHP code',
			'metaDescription' => 'This blog aims at showing and manage articles.',
			'imgBannerCSSClass' => 'home',
			'siteKey' => $this->config::$_params['googleRecaptcha']['siteKey'],
			'cfTokenIndex' => $this->cfTokenIndex,
			'cfTokenValue' => $this->cfTokenValue,
			'submit' => 0,
			'success' => isset($_SESSION['cf_success']) && $_SESSION['cf_success'] ? true : false,
			'sending' => 0
		];
		echo $this->page->renderTemplate('Home/home-index.tpl', $varsArray);

		// Is it already a succcess state?
		// this happens after no AJAX success redirection
		if($this->isContactSuccess()) {
			unset($_SESSION['cf_success']);
		}
	}

	public function sendContactMessage()
	{
		// Stores result of form validation
		$checkedForm = $this->validateContactForm();

		// Ajax mode is not used!
		if(!$this->config::$_params['contactForm']['ajaxMode']) {

			// Success state is not returned.
			if(!$this->isContactSuccess()) {
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
					'ajaxModeForContactForm' => $this->config::$_params['contactForm']['ajaxMode'] ? 1 : 0,
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
					'success' => isset($_SESSION['cf_success']) && $_SESSION['cf_success'] ? true : false,
					'sending' => isset($checkedForm['notSent']) && $checkedForm['notSent'] ? 1 : 0
				]; 

				// Render the entire page
				echo $this->page->renderTemplate('Home/home-index.tpl', $varsArray);
			}
			// Success state is returned: avoid previous $_POST with a redirection.
			else {
				$this->httpResponse->addHeader('Location: /');
			}
		}
		// Ajax mode
		else {
			$varsArray = [
				'ajaxModeForContactForm' => $this->config::$_params['contactForm']['ajaxMode'] ? 1 : 0,
				'familyName' => isset($checkedForm['familyName']) ? $checkedForm['familyName'] : '',
				'firstName' => isset($checkedForm['firstName']) ? $checkedForm['firstName'] : '',
				'email' => isset($checkedForm['email']) ? $checkedForm['email'] : '',
				'message' => isset($checkedForm['message']) ? $checkedForm['message'] : '',
				'siteKey' => $this->config::$_params['googleRecaptcha']['siteKey'],
				'cfTokenIndex' => $this->cfTokenIndex,
				'cfTokenValue' => $this->cfTokenValue,
				'submit' => isset($_SESSION['cf_success']) && $_SESSION['cf_success'] ? 1 : 0,	
				'errors' => isset($checkedForm['cf_errors']) ? $checkedForm['cf_errors'] : false,
				'success' => isset($_SESSION['cf_success']) && $_SESSION['cf_success'] ? true : false,
				'sending' => isset($checkedForm['notSent']) && $checkedForm['notSent'] ? 1 : 0
			]; 

			// Render AJAX response with contact form only
			$this->httpResponse->addHeader('Cache-Control: no-cache, must-revalidate');
			echo $this->page->renderBlock('Home/home-contact-form.tpl', 'contactForm',  $varsArray);

			// Is it already a succcess state?
			if($this->isContactSuccess()) {
				unset($_SESSION['cf_success']);
			}
		}
	}

	public function getCurrentToken() {
		$this->httpResponse->addHeader('Content-Type:application/json; charset=utf-8');
		echo json_encode(['key' => $this->cfTokenIndex,'value' => $this->cfTokenValue]);
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
					$result['cf_errors']['email'] = 'Sorry, <span class="text-muted">' . $_POST['cf_email'] . '</span> is not a valid email address!<br>Extra spaces before/after or forbidden characters<br>could prevent validation.';
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
		if(isset($result) && empty($result['cf_errors']) && isset($result['googleRecaptchaResponse']) && $result['googleRecaptchaResponse'] && isset($result['check']) && $result['check']) {
			
			// Insert Contact entity in database
			try {
				$this->currentModel->insertContact($result);
				$this->insertionInfos = '<span style="color:#ffffff;background-color:#18ce0f;padding:5px">Success notice - Contact entity was successfully saved in database.</span>';
			} catch (\PDOException $e) {
				$this->insertionInfos = '<span style="color:#ffffff;background-color:#ff3636;padding:5px">Error warning - Contact entity was unsaved in database:<br>' . $e->getMessage() . '</span>';
			}

			// Send email
			if($this->sendMailWithPHPMailer($result)) {
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
				// Feed "data-not-sent" attribute on contact form.
				$result['notSent'] = true;
				// Warn user if sending failed.
				$result['cf_errors']['sending'] = 'Sorry, a technical error happened.<br>Your message was not sent.';
				if($this->config::$_appDebug && !empty($this->sendingInfos['error'])) {
					// Show more details in case of debug mode
					$result['cf_errors']['sending'] .= '<br>' . $this->sendingInfos['error'];
				}
			}
		}
		return $result;
	}

	public function sendMailWithPHPMailer($datas)
	{
		$mail = new PHPMailer($this->config::$_params['contactPHPMailer']['EnableExceptions']); 
		$mail->isSMTP(); // use SMTP
		$mail->SMTPDebug = $this->config::$_params['contactPHPMailer']['SMTPDebug']; // enable SMTP debugging or not
		$mail->SMTPAuth  = $this->config::$_params['contactPHPMailer']['SMTPAuth'];
		$mail->Username = $this->config::$_params['contactPHPMailer']['SMTPUserName']; // username to use for SMTP authentication
		$mail->Password = $this->config::$_params['contactPHPMailer']['SMTPPwd']; // password to use for SMTP authentication
		$mail->Port = $this->config::$_params['contactPHPMailer']['Port'];
		$mail->Host = $this->config::$_params['contactPHPMailer']['Host']; // set the hostname of the mail server
		$mail->SMTPSecure = $this->config::$_params['contactPHPMailer']['SMTPSecure']; //set the encryption system to use
		
		//Recipients
		$mail->setFrom($this->config::$_params['contactForm']['contactEmail'], 'phpBlog - Contact form'); // sent from
		$mail->addAddress($this->config::$_params['contactForm']['contactEmail'], 'phpBlog'); // sent to	
		$mail->addReplyTo($this->config::$_params['contactForm']['contactEmail'], 'Reply to phpBlog'); //set an alternative reply-to address

		//Content
	    $mail->isHTML(true); // set email format to HTML
	    $mail->Subject = 'phpBlog - Contact form: someone sent a message!'; // Email subject
	    $mail->Body = '<p style="text-align:center;"><img src="' . $this->config::$_params['contactPHPMailer']['HostedImagesAbsoluteURL'] . 'dotprogs-logo-2016.png" alt="phpBlog contact form"></p>'; // Add custom header image
	    $mail->Body .= '<p style="text-align:center;"><strong>phpBlog - Contact form: someone sent a message!</strong></p>'; // html format
	    $mail->Body .= '<p style="width:50%;margin:auto;text-align:center;padding:10px;background-color:#bdbdbc;color:#ffffff;">' . $this->insertionInfos . '</p>'; // html format
	    $mail->Body .= '<p style="width:50%;margin:auto;text-align:center;padding:10px;background-color:#7b7c7c;color:#ffffff;">From: ' . $datas['firstName'] . ' ' . $datas['familyName'] . ' | <a href="#" style="color:#ffffff; text-decoration:none"><font color="#ffffff">' . $datas['email'] . '</font></a><br>- Message -<br>' . nl2br($datas['message']) . '</p>'; // html format
	    $mail->Body .= '<p style="width:50%;margin:auto;text-align:center;padding:10px;">&copy; ' . date('Y') . ' phpBlog</p>'; // html format
	    $mail->AltBody = $this->insertionInfos . "\n\r"; // text format
	    $mail->AltBody .= 'From:' . $datas['firstName'] . ' ' . $datas['familyName'] . ' | ' . $datas['email'] . "\n\r" . '- Message -' . "\n\r" . $datas['message']. "\n\r"; // text format
	    $mail->AltBody .= '&copy; ' . date('Y') . ' phpBlog'; // text format

		try {
		    if(!$mail->send()) {
		    	$this->sendingInfos['error'] = $mail->ErrorInfo;
		    	return false;
		    }
		    else {
		    	return true;
		    }
			
		} catch (Exception $e) {
			$this->sendingInfos['error'] = $e->errorMessage();
			return false;
		}
	}
}