<?php
namespace Core\Form;
use Core\Config\AppConfig;
use Core\Service\AppContainer;
use Core\Helper\AppStringModifier;

/**
 * Validate form user inputs
 */
class AppFormValidator
{
    /**
     * @var array: $_POST values before validation
     */
    private $datas = [];
    /**
     * @var string: form prefix name to distinguish values in $_POST
     */
    private $formIdentifier;
        /**
     * @var string: index name based on $formIdentifier for errors which are stored in $result
     */
    private $errorIndex;
    /**
     * @var array: $_POST values filtered with PHP filters
     */
    private $filteredDatas = [];
    /**
     * @var object: configuration to use
     */
    private $config;
    /**
     * @var object: helper to use
     */
    private $helper;
    /**
     * @var array: datas stored after validation (values and errors)
     */
    private $result = [];

    /**
     * Constructor
     * @param array $datas to validate
     * @param string $formIdentifier
     * @return void
     */
    public function __construct($datas, $formIdentifier)
    {
        $this->datas = $datas;
        $this->formIdentifier = $formIdentifier;
        $this->errorIndex = $this->formIdentifier . 'errors';
        $this->config = AppConfig::getInstance();
        $this->helper = AppStringModifier::getInstance();
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
                        'options' => function($data) use($validator, $modifiers) {
                            $data = $validator->modifyData($data, $modifiers);
                            return $data = filter_var($data, FILTER_SANITIZE_STRING);

                        }
                    ]);
                break;
                case 'email':
                    $this->filteredDatas[$name] = filter_input($inputType, $name, FILTER_CALLBACK, [
                        'options' => function($data) use($validator, $modifiers) {
                            $data = $validator->modifyData($data, $modifiers);
                            return $data = filter_var($data, FILTER_SANITIZE_EMAIL);
                        }
                    ]);
                break;
                default:
                    $this->filteredDatas[$name] = filter_input($inputType, $name, FILTER_CALLBACK, [
                        'options' => function($data) use($validator, $modifiers) {
                            $data = $validator->modifyData($data, $modifiers);
                            return $data = filter_var($data, FILTER_SANITIZE_STRING);
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
     * Check if user input is an email
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
            if(!filter_var(trim($value), FILTER_VALIDATE_EMAIL)) {
                $this->result[$this->errorIndex][$name] = 'Sorry, <span class="text-muted">' . $value . '</span> is not a valid email address!<br>Please check its format.';
                $this->result[$name] = $value;
            } else {
                $this->result[$name] = $this->filteredDatas[$name];
            }
        } else {
            $this->result[$this->errorIndex][$name] = 'Please fill in your email.';
        }
    }

    /**
     * Validate anti CSRF token
     * @param string $dynamicToken: dynamic token value in $_POST with dynamic token index name
     * @param string $tokenPrefix: prefix used to override form identifier
     * if form validator manages multiple forms
     * @return void
     */
    public function validateToken($dynamicToken, $tokenPrefix = null) {
        $prefix = !is_null($tokenPrefix) ? $tokenPrefix : $this->formIdentifier;

        // Check if value from form match value stored in $_SESSION
        if (isset($dynamicToken) && isset($_SESSION[$prefix . 'token'])) {
            if($this->checkTokenValue($dynamicToken, $prefix . 'token')) {
                $this->result[$this->formIdentifier . 'check'] = true;
            }
            else {
                $this->result[$this->errorIndex][$this->formIdentifier . 'check'] = '<span class="form-check-notice">- Wrong token -<br>You are not allowed to use the form like this!<br>Please do not follow the dark side of the force... ;-)</span>';
                $this->result[$this->formIdentifier . 'check'] = false;
            }
        // Wrong token index or anything else happened
        } else {

            $this->result[$this->errorIndex][$this->formIdentifier . 'check'] = '<span class="form-check-notice"> - Wrong token -<br>You are not allowed to use the form like this!<br>Please do not follow the dark side of the force... ;-)</span>';
            $this->result[$this->formIdentifier . 'check'] = false;
        }
    }

    /**
     * Create a dynamic $_POST check (token) index in addition to token value to prevent CSRF
     * @param string $name: name of field which contains token
     * @return string stored in $_SESSION
     */
    public function generateTokenIndex($name)
    {
        if (!isset($_SESSION[$name])) {
            $_SESSION[$name] = $name . mt_rand(0,mt_getrandmax());
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
        if (!isset($_SESSION[$varName])) {
            $_SESSION[$varName] = hash('sha256', $varName . bin2hex(openssl_random_pseudo_bytes(8)) . session_id());
        }
        return $_SESSION[$varName];
    }

    /**
     * Check if created token matches with token in $_POST value
     * @param string $token: $_POST value
     * @param string $varName: name which corresponds to token index in $_SESSION
     * @return boolean
     */
    public function checkTokenValue($token, $varName)
    {
        return $token === $this->generateTokenValue($varName);
    }
}