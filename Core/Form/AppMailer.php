<?php
namespace Core\Form;
use Core\Routing\AppRouter;
// Import PHPMailer component
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Enable use of different kind of mailers in form
 */
class AppMailer
{
	/**
	 * @var object: mailer to use
	 */
	private $mailer;
    /**
     * @var AppRouter: an instance of AppRouter
     */
    private $router;
	/**
	 * @var string: parameter to select a method
	 */
	private $sendingMethod;
	/**
	 * @var string: parameter to select a method
	 */
	private $use;

	/**
	 * Constructor
	 * @param object $mailer: an instance of one type of mailer object
     * @param object $router: an instance of AppRouter
     * @param string $sendingMethod: sending method parameter
     * @param string $use: use context parameter
	 * @return void
	 */
	public function __construct($mailer, AppRouter $router, $sendingMethod = null, $use = null)
    {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->sendingMethod = $sendingMethod;
        $this->use = $use;
        $this->config = $router->getConfig();
    }

    /**
     * Call the right method and depends on called object
     * @param array $arguments: an array of $arguments to feed a method
     * @return mixed: return depends on called method
     */
    public function call($arguments)
    {
    	switch((new \ReflectionClass($this->mailer))->getShortName()) {
    		case 'PHPMailer':
    			if (isset($arguments['customized'])) {
                    return call_user_func_array([$this->mailer, $arguments['customized'][0]], isset($arguments['customized'][1]) ? $arguments['customized'][1] : []);
    			} elseif ((is_array($arguments))) { // Only arguments
                    if ($this->sendingMethod == 'smtp' && $this->use == 'contactForm') {
                        return call_user_func_array([$this, 'sendContactFormMessageWithSMTP'], $arguments);
                    }
                    // Other methods: do stuff here
                }
    		break;
    		// Other types: do stuff here: example swiftMailer
    	}
    }

    /**
     * Send a message from a particular contact form with SMTP
     * @param array $datas: contact form datas to send
     * @param string $insertionInfos: a string to call which represents a notice message
     * to know if Contact entity is saved or not in database
     * @param string $sendingInfos: an empty string property to feed in controller
     * @return boolean: sending mail state
     */
    public function sendContactFormMessageWithSMTP($datas, $insertionInfos, $sendingInfos)
    {
    	$this->mailer->isSMTP(); // use SMTP
		$this->mailer->SMTPDebug = $this->config::getParam('contactPHPMailer.SMTPDebug'); // enable SMTP debugging or not
		$this->mailer->SMTPAuth  = $this->config::getParam('contactPHPMailer.SMTPAuth');
		$this->mailer->Username = $this->config::getParam('contactPHPMailer.SMTPUserName'); // username to use for SMTP authentication
		$this->mailer->Password = $this->config::getParam('contactPHPMailer.SMTPPwd'); // password to use for SMTP authentication
		$this->mailer->Port = $this->config::getParam('contactPHPMailer.port');
		$this->mailer->Host = $this->config::getParam('contactPHPMailer.host'); // set the hostname of the mail server
		$this->mailer->SMTPSecure = $this->config::getParam('contactPHPMailer.SMTPSecure'); //set the encryption system to use
		// Recipients
		$this->mailer->setFrom($this->config::getParam('contactForm.contactEmail'), 'phpBlog - Contact form'); // sent from (for instance, if Google mail SMTP is used, a restriction exists: it is better to use receiver address and not sender address.)
		$this->mailer->addAddress($this->config::getParam('contactForm.contactEmail'), 'phpBlog'); // sent to
		$this->mailer->ClearReplyTos();
		$this->mailer->addReplyTo($datas['email'], 'Reply to ' . $datas['firstName'] . ' ' . $datas['familyName']); //set an alternative reply-to address
		// Content
	    $this->mailer->isHTML(true); // set email format to HTML
	    $this->mailer->Subject = 'phpBlog - Contact form: someone sent a message!'; // Email subject
	    $this->mailer->Body = '<p style="text-align:center;"><img src="' . $this->config::getParam('contactPHPMailer.hostedImagesAbsoluteURL') . 'dotprogs-logo-2016.png" alt="phpBlog contact form"></p>'; // Add custom header image
	    $this->mailer->Body .= '<p style="text-align:center;"><strong>phpBlog - Contact form: someone sent a message!</strong></p>'; // html format
	    $this->mailer->Body .= '<p style="width:50%;margin:auto;text-align:center;padding:10px;background-color:#bdbdbc;color:#ffffff;">' . $insertionInfos . '</p>'; // html format
	    $this->mailer->Body .= '<p style="width:50%;margin:auto;text-align:center;padding:10px;background-color:#7b7c7c;color:#ffffff;">From: ' . $datas['firstName'] . ' ' . $datas['familyName'] . ' | <a href="#" style="color:#ffffff; text-decoration:none"><font color="#ffffff">' . $datas['email'] . '</font></a><br>- Message -<br>' . nl2br($datas['message']) . '</p>'; // html format
	    $this->mailer->Body .= '<p style="width:50%;margin:auto;text-align:center;padding:10px;">&copy; ' . date('Y') . ' phpBlog</p>'; // html format
	    $this->mailer->AltBody = $insertionInfos . "\n\r"; // text format
	    $this->mailer->AltBody .= 'From:' . $datas['firstName'] . ' ' . $datas['familyName'] . ' | ' . $datas['email'] . "\n\r" . '- Message -' . "\n\r" . $datas['message']. "\n\r"; // text format
	    $this->mailer->AltBody .= '&copy; ' . date('Y') . ' phpBlog'; // text format
		try {
		    if (!$this->mailer->send()) {
		    	$sendingInfos = $this->mailer->ErrorInfo;
		    	return false;
		    } else {
		    	return true;
		    }
		} catch (Exception $e) {
			$sendingInfos = $e->errorMessage();
			return false;
		}
    }
}