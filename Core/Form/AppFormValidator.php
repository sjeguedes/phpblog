<?php
namespace Core\Form;
use Core\Routing\AppRouter;
use Core\Helper\AppStringModifier;

/**
 * Validate form user inputs
 */
class AppFormValidator
{
    /**
     * @var AppRouter: an AppRouter instance to use
     */
    private $router;
    /**
     * @var AppConfig: an AppConfig instance for configuration to use
     */
    private $config;
    /**
     * @var AppStringModifier: an AppStringModifier helper instance to use
     */
    private $helper;
    /**
     * @var array: $_POST/$_GET values before validation
     */
    private $datas = [];
    /**
     * @var string: form prefix name to distinguish values in $_POST/$_GET
     */
    private $formIdentifier;
        /**
     * @var string: index name based on $formIdentifier for errors which are stored in $result
     */
    private $errorIndex;
    /**
     * @var array: $_POST/$_GET values filtered with PHP filters
     */
    private $filteredDatas = [];
    /**
     * @var array: datas stored after validation (values and errors)
     */
    private $result = [];

    /**
     * Constructor
     * @param object $router: an AppRouter instance
     * @param array $datas to validate
     * @param string $formIdentifier
     * @return void
     */
    public function __construct(AppRouter $router, $datas, $formIdentifier)
    {
        $this->router = $router;
        $this->datas = $datas;
        $this->formIdentifier = $formIdentifier;
        $this->errorIndex = $this->formIdentifier . 'errors';
        $this->config = $this->router->getConfig();
        $this->helper = AppStringModifier::getInstance($router);
    }

    /**
     * Get form identifier
     * @return string: identifier name
     */
    public function getFormIdentifier()
    {
        return $this->formIdentifier;
    }

    /**
     * Get form helper
     * @return object: AppStringModifier instance
     */
    public function getFormHelper()
    {
        return $this->helper;
    }

    /**
     * Get result datas
     * @return array: an array which contains filtered datas and error messages
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Filter each user input
     * @param array $datas: datas to filter
     * @param int $inputType: chosen http request method
     * @return void
     */
    public function filterDatas($datas, $inputType = INPUT_POST)
    {
        // Declare current validator to use it as argument
        $validator = $this;
        // Apply filter for each type of data
        for ($i = 0; $i < count($datas); $i++) {
            $name = $this->formIdentifier . $datas[$i]['name'];
            $filterType = $datas[$i]['filter'];
            $modifiers = $datas[$i]['modifiers'];
            switch ($filterType) {
                case 'alphanum':
                    $this->filteredDatas[$name] = filter_input($inputType, $name, FILTER_CALLBACK, [
                        'options' => function($data) use($validator, $modifiers, $name) {
                            $data = $validator->modifyData($data, $modifiers);
                            $data = filter_var($data, FILTER_SANITIZE_STRING);
                            $this->result[$name] = $data;
                            return $data;
                        }
                    ]);
                break;
                case 'email':
                    $this->filteredDatas[$name] = filter_input($inputType, $name, FILTER_CALLBACK, [
                        'options' => function($data) use($validator, $modifiers, $name) {
                            $data = $validator->modifyData($data, $modifiers);
                            $data = filter_var($data, FILTER_SANITIZE_EMAIL);
                            $this->result[$name] = $data;
                            return $data;
                        }
                    ]);
                break;
                default:
                    $this->filteredDatas[$name] = filter_input($inputType, $name, FILTER_CALLBACK, [
                        'options' => function($data) use($validator, $modifiers, $name) {
                            $data = $validator->modifyData($data, $modifiers);
                            $data = filter_var($data, FILTER_UNSAFE_RAW);
                            $this->result[$name] = $data;
                            return $data;
                        }
                    ]);
                break;
            }
        }
    }

    /**
     * Apply modifiers on data
     * @param string $data: data to modify
     * @param array $helpers: format a data
     * @return string: formatted data
     */
    private function modifyData($data, $helpers)
    {
        if (!is_null($helpers) && is_array($helpers)) {
            foreach ($helpers as $modifier) {
                $data = $this->helper->$modifier($data);
            }
            return $data;
        }
    }

    /**
     * Check if user input is set and user input is not an empty string
     * @param string $name: field attribute name
     * @param string $label: field name to show
     * @param boolean $errorMessage: manage field error message
     * @return boolean|void
     */
    public function validateRequired($name, $label, $errorMessage = true)
    {
        $name = $this->formIdentifier . $name;
        if (!$errorMessage) {
            return array_key_exists($name, $this->datas) && trim($this->datas[$name]) != '';
        } else {
            if (array_key_exists($name, $this->datas) && trim($this->datas[$name]) != '') {
                $this->result[$name] = $this->filteredDatas[$name];
            } else {
                $this->result[$this->errorIndex][$name] = 'Please fill in your ' . $label . '.';
                $this->result[$name] = '';
            }
        }
    }

    /**
     * Check if user input is a valid email
     * @param string $name: field name
     * @param string $label: field name to show
     * @param string $value: field value
     * @return void
     */
    public function validateEmail($name, $label, $value)
    {
        $required = $this->validateRequired($name, $label, false);
        $name = $this->formIdentifier . $name;
        if ($required) {
            if (!filter_var(trim($value), FILTER_VALIDATE_EMAIL)) {
                $this->result[$this->errorIndex][$name] = 'Sorry, <span class="text-muted">' . $value . '</span> is not a valid email address!<br>Please check its format.';
                $this->result[$name] = $value;
            } else {
                $this->result[$name] = $this->filteredDatas[$name];
            }
        } else {
            $this->result[$this->errorIndex][$name] = 'Please fill in your email address.';
        }
    }

    /**
     * Check if user input is a valid password
     * @param string $name: field name
     * @param string $label: field name to show
     * @param string $value: field value
     * @return void
     */
    public function validatePassword($name, $label, $value)
    {
        $required = $this->validateRequired($name, $label, false);
        $name = $this->formIdentifier . $name;
        if ($required) {
            // At least 1 number, 1 lowercase letter, 1 uppercase letter, 1 special character, a minimum of 8 characters
            $passwordFormat = '#^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W]).{8,}$#';
            // A minimum of 8 characters
            if (strlen($value) < 8) {
                $this->result[$this->errorIndex][$name] = 'Sorry, your password must contain<br>at least 8 characters!';
                $this->result[$name] = $value;
            } elseif (!preg_match($passwordFormat, $value)) {
                $this->result[$this->errorIndex][$name] = 'Sorry, your password format is not valid!<br>Please check it or verify required characters<br>before login try.';
                $this->result[$name] = $value;
            } else {
                // No filter on user input: hash will only be checked with value in database.
                $this->result[$name] = $value;
            }
        } else {
            $this->result[$this->errorIndex][$name] = 'Please fill in your password.';
        }
    }

    /**
     * Check if password confirmation user input is identical to main password user input
     * @param string $name: field name
     * @param string $password: main password
     * @param string $passwordConfirmation: password confirmation
     * @return void
     */
    public function validatePasswordConfirmation($name, $password, $passwordConfirmation) {
        $name = $this->formIdentifier . $name;
        if ($passwordConfirmation !== $password) {
            $this->result[$this->errorIndex][$name] = 'Password confirmation does not match<br>your password!<br>Please check both to be identical.<br>Unwanted authorized space character(s) " "<br>may be an issue!';
        }
    }

    /**
     * Check if user password renewal authentication token in $_REQUEST value contains exactly 15 characters
     * @param string $name: field name
     * @param string $inputToken: user input token value
     * @return void
     */
    public function validatePasswordUpdateTokenLength($name, $inputToken)
    {
        $name = $this->formIdentifier . $name;
        if (strlen($inputToken) != 15) {
            $this->result[$this->errorIndex][$name] = 'Sorry, your token must contain<br>exactly 15 characters!<br>Please check it.';
        }
    }

    /**
     * Validate anti CSRF token
     * @param string $dynamicToken: dynamic token value in $_REQUEST with dynamic token index name
     * @param string $tokenPrefix: prefix used to override form identifier
     * if form validator manages multiple forms
     * @return void
     */
    public function validateToken($dynamicToken, $tokenPrefix = null) {
        $prefix = !is_null($tokenPrefix) ? $tokenPrefix : $this->formIdentifier;
        if (isset($dynamicToken) && isset($_SESSION[$prefix . 'token'])) {
            // Get dynamic token index
            foreach ($_REQUEST as $key => $value) {
                if ($value === $dynamicToken) {
                    $tokenIndex =  $key;
                    break;
                }
            }
            // Check if form token value matches value stored in $_SESSION
            if ($this->checkTokenValue($dynamicToken, $prefix . 'token')) {
                // Check if form token index matches value stored in $_SESSION
                if (isset($tokenIndex) && isset($_SESSION[$prefix . 'check']) && $tokenIndex === $_SESSION[$prefix . 'check']) {
                    $this->result[$this->formIdentifier . 'check'] = true;
                }
            } else {
                // Wrong token value or wrong token dynamic index!
                $this->result[$this->formIdentifier . 'check'] = false;
            }
        // Anything else happened.
        } else {
            $this->result[$this->formIdentifier . 'check'] = false;
        }
        // Wrong token (expired or invalid (hacked) token)
        if (!$this->result[$this->formIdentifier . 'check']) {
            // Set 401 error page
            $this->setUnauthorizedFormSubmissionResponse();
        }

    }

    /**
     * Render a 401 ("Unauthorized") form submission response
     * @return string: page HTML content
     */
    public function setUnauthorizedFormSubmissionResponse()
    {
        // Call session expiration when user is authenticated and use back office
        // Redirect to login page for security reason thanks to this session var
        // Look at AdminUserController->showAdminAccess()
        if ($this->router->getSession()::isUserAuthenticated()) {
            // Run disconnection
            $this->router->getSession()::destroy();
            // Initialize state
            $_SESSION['expiredSession']['state'] = true;
            $_SESSION['expiredSession']['unauthorizedFromAdmin'] = true;
        // Call a 401 error response for forms which are not part of back office.
        } else {
            $_SESSION['unauthorizedFormSubmission'] = true;
            $this->router->getHTTPResponse()->set401ErrorResponse('<strong>Form submission is unauthorized.</strong><br>this message is mainly due to security reason.<br>This issue could also be due to inactivity (session expiration) on our website.<br>Please go back to <a href="/' . $this->router->getUrl() . '" class="normal-link" title="Previous visited page">previous page</a> and try again.', $this->router);
            exit();
        }
    }

    /**
     * Create a dynamic $_POST/$_GET check (token) index in addition to token value to prevent CSRF
     * @param string $name: name of field which contains token
     * @return string stored in $_SESSION
     */
    public function generateTokenIndex($name)
    {
        if (!isset($_SESSION[$name])) {
            $_SESSION[$name] = $name . mt_rand(0, mt_getrandmax());
        }
        return $_SESSION[$name];
    }

    /**
     * Create a token value to fight against CSRF
     * @param string $varName: name which corresponds to token index
     * @return string: value stored in $_SESSION
     */
    public function generateTokenValue($varName)
    {
        // Check if a token does not exist,
        if (!isset($_SESSION[$varName])) {
            $_SESSION[$varName] = hash('sha256', $varName . bin2hex(openssl_random_pseudo_bytes(8)) . session_id());
        }
        return $_SESSION[$varName];
    }

    /**
     * Check if created CSRF token matches token in $_POST/$_GET value
     * @param string $token: $_POST/$_GET value
     * @param string $varName: name which corresponds to token index in $_SESSION
     * @return boolean
     */
    public function checkTokenValue($token, $varName)
    {
        return $token === $this->generateTokenValue($varName);
    }
}