<?php
namespace Core\Form\Element;

use Core\Routing\AppRouter;

/**
 * Create a class to avoid spam bots in forms
 * Based on Honeypot principle, timing, and html form elements (checkbox, ...) to validate
 */
class AppNoSpamTools
{
    /**
     * @var AppRouter: an instance of AppRouter
     */
    private $router;
    /**
     * @var object: config to use
     */
    private $config;
    /**
     * @var string: form prefix name to distinguish values in/$_GET...
     */
    private $formIdentifier;
    /**
     * @var int: the minimum amount of seconds a submission should take
     */
    private const TIME_LIMIT = 15;
    /**
     * @var boolean: is honeypot used?
     */
    private $isHoneyPot = false;
    /**
     * @var boolean: is time limit used?
     */
    private $isTimeLimit = false;
    /**
     * @var boolean: is human switch check used?
     */
    private $isHumanSwitch = false;
    /**
     * @var string: html output with used tools
     */
    private $tools;
    /**
     * @var array: values from captcha tools HTML elements to retrieve in,...
     */
    private $noSpamFormValues;

    /**
     * Constructor
     *
     * @param object $router: an instance of AppRouter
     * @param array $serviceParams: an array of parameters to initialize properties
     *
     * @return void
     */
    public function __construct(AppRouter $router, $serviceParams)
    {
        $this->router = $router;
        $this->config = $router->getConfig();
        $this->formIdentifier = $serviceParams['formIdentifier'];
        $this->tools = $serviceParams['tools'];
    }

    /**
     * Get a form identifier where no spam tools "captcha" is used
     *
     * @return string: identifier name
     */
    public function getFormIdentifier()
    {
        return $this->formIdentifier;
    }

    /**
     * Get defined time limit (amount of minimun time to fill a form)
     *
     * @return int: time limit
     */
    public function getTimeLimit()
    {
        return self::TIME_LIMIT;
    }

    /**
     * Get used tools in no spam tools "captcha"
     *
     * @return array: an array of activated tools
     * When a no spam tools captcha is defined, one or more tools can be used: it is a part of customization.
     */
    public function getNoSpamToolsUsed()
    {
        return [
            $this->formIdentifier . 'hpi' => $this->isHoneyPot,
            $this->formIdentifier . 'tli' => $this->isTimeLimit,
            $this->formIdentifier . 'hsi' => $this->isHumanSwitch
            // Other tools? Do stuff here!
        ];
    }

    /**
     * Set no spam tools values (before or after form submission)
     *
     * @param array $formValues: an array which contains $_POST, $_GET ... values (submission)
     * or no values (initialization must be done)
     *
     * @return void
     */
    public function setNoSpamFormValues($formValues = [])
    {
        // Form was submitted!
        if (!empty($formValues) || !isset($formValues[$this->formIdentifier . '_submit'])) {
            foreach ($formValues as $key => $value) {
                switch ($key) {
                    case $this->formIdentifier . 'hpi':
                        $this->noSpamFormValues[$this->formIdentifier . 'hpi'] = $value;
                        break;
                    case $this->formIdentifier . 'tli':
                        $this->noSpamFormValues[$this->formIdentifier . 'tli'] = $value;
                        break;
                    case $this->formIdentifier . 'hsi':
                        $this->noSpamFormValues[$this->formIdentifier . 'hsi'] = $value;
                        break;
                    default:
                        // Particular case: human control checkbox is not set
                        if (!isset($formValues[$this->formIdentifier . 'hsi'])) {
                            // return default "off" value instead to create $_POST value
                            $this->noSpamFormValues[$this->formIdentifier . 'hsi'] = 'off';
                        }
                        break;
                }
            }
            // Form was just loaded!
        } else {
            $this->noSpamFormValues[$this->formIdentifier . 'hpi'] = '';
            $this->noSpamFormValues[$this->formIdentifier . 'tli'] = time();
            $this->noSpamFormValues[$this->formIdentifier . 'hsi'] = 'off';
        }
    }

    /**
     * Check timestamp validity
     *
     * @param int $timestamp: time() is used to create the value automatically.
     *
     * @return boolean: validity of timestamp
     */
    public function checkTimestamp($timestamp)
    {
        return ((string) (int) $timestamp === $timestamp)
        && ($timestamp <= PHP_INT_MAX)
        && ($timestamp >= ~PHP_INT_MAX);
    }

    /**
     * Create a honeypot HTML element (empty text input)
     *
     * @param bool $print: should render directly HTML output
     *
     * @return string|array: HTMl element tag string or an array of parameters to create HTML element
     */
    public function createHoneyPotInput($print = false)
    {
        $this->isHoneyPot = true;
        if ($print) {
            $tool = '<input name="' . $this->formIdentifier . 'hpi" style="display:none" type="text" value="' . $this->noSpamFormValues[$this->formIdentifier . 'hpi'] . '" >';
        } else {
            $tool = [
                'label' => false,
                'input' => [
                    'class' => false,
                    'style' => 'display:none',
                    'id' => $this->formIdentifier . 'hpi',
                    'name' => $this->formIdentifier . 'hpi',
                    'type' => 'text',
                    'value' => $this->noSpamFormValues[$this->formIdentifier . 'hpi']
                ]
            ];
        }
        return $tool;
    }

    /**
     * Create a time check HTML element (hidden input):
     * Created value corresponds to timestamp when form was loaded.
     *
     * @param bool $print: should render directly HTML output
     *
     * @return string|array: HTMl element tag string or an array of parameters to create HTML element
     */
    public function createTimeLimitInput($print = false)
    {
        $this->isTimeLimit = true;
        $timeLimit = isset($_SESSION[$this->formIdentifier . 'success']) ? time() : $this->noSpamFormValues[$this->formIdentifier . 'tli'];
        if ($print) {
            $tool = '<input name="' . $this->formIdentifier . 'tli" type="hidden" value="' . $timeLimit . '" >';
        } else {
            $tool = [
                'label' => false,
                'input' => [
                    'class' => false,
                    'id' => $this->formIdentifier . 'tli',
                    'name' => $this->formIdentifier . 'tli',
                    'type' => 'hidden',
                    'value' => $timeLimit
                ]
            ];
        }
        return $tool;
    }

    /**
     * Create a human checkbox control HTML element (checkbox input)
     *
     * @param bool $print: should render directly HTML output
     * @param string $label: label string to render next to HTML switch input
     *
     * @return string|array: HTMl element tag string or an array of parameters to create HTML element
     */
    public function createHumanSwitchInput($print = false, $label)
    {
        $this->isHumanSwitch = true;
        $checked = $this->noSpamFormValues[$this->formIdentifier . 'hsi'];
        $checkedAttribute = ($checked == 'on') ? ' checked' : false;
        if ($print) {
            $tool = '<input type="checkbox" name="' . $this->formIdentifier . 'hsi" class="bootstrap-switch" data-on-label="YES" data-off-label="NO"' .$checkedAttribute . 'value="' . $checked . '" >';
        } else {
            $tool = [
                'label' => $label,
                'input' => [
                    'class' => 'bootstrap-switch',
                    'data-on-label' => 'YES',
                    'data-off-label' => 'NO',
                    'id' => $this->formIdentifier . 'hsi',
                    'name' => $this->formIdentifier . 'hsi',
                    'type' => 'checkbox',
                    'value' => $checked,
                    'checked' => $checkedAttribute
                ]
            ];
        }
        return $tool;
    }

    /**
     * Set all activated tools in no spam tools "captcha"
     *
     * @return array: an array of tools parameters or tools HTML ouputs
     */
    public function setNoSpamFormElements()
    {
        for ($i = 0; $i < count($this->tools); $i++) {
            $toolsHTMLParams[$i] = call_user_func_array([$this, $this->tools[$i]['callable']], $this->tools[$i]['arguments']);
        }
        return $toolsHTMLParams;
    }
}
