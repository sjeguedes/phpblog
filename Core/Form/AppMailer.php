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
     * @var object: config to use
     */
    private $config;
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
        $this->config = $router->getConfig();
        $this->sendingMethod = $sendingMethod;
        $this->use = $use;
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
	    $this->mailer->Subject = 'phpBlog - Contact form: someone sent a message!'; // email subject
	    $this->mailer->Body = '<p style="width:600px; margin: 0 auto; text-align:center;"><img src="' . $this->config::getParam('mailing.hostedImagesAbsoluteURL') . 'dotprogs-logo-2016.jpg" alt="phpBlog contact form" with="150" height="150"></p>' . PHP_EOL; // add custom header image
	    $this->mailer->Body .= '<p style="width:600px; margin: 0 auto; text-align:center;"><strong>CONTACT FORM<br>Someone sent a message!</strong><br><br></p>' . PHP_EOL; // html format
	    $this->mailer->Body .= '<p style="width:600px; margin:auto; text-align:center; border-top: 2px solid #ffb236;"><br>' . $insertionInfos . '<br><br></p>' . PHP_EOL; // html format
	    $this->mailer->Body .= '<p style="width:600px; margin:auto; text-align:center; border-bottom: 2px solid #2ca8ff;">From: ' . $datas['firstName'] . ' ' . $datas['familyName'] . ' | <a href="#" style="color:#000; text-decoration:none;"><font color="#000">' . $datas['email'] . '</font></a><br>- Message -<br>' . nl2br($datas['message']) . '<br><br>&copy; ' . date('Y') . ' phpBlog<br><br></p>'; // html format
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