<?php
namespace Core;

/**
 * Load template engine to show front-end and set its parameters:
 * Example: here Twig template engine is used
 */
class AppPage 
{
	/**
	 * @var Twig_Loader_Filesystem instance
	 */
	private $templateEngineLoader;
	/**
	 * @var Twig_Environment instance
	 */
	private $templateEngineEnv;
	/**
	 * @var array: an array of parameters to use for template engine configuration
	 */
	private static $_envParams = [];

	/**
	 * Constructor
	 * @return void
	 */
	public function __construct()   
	{
		// TODO: call DIC to instantiate objects!
		$this->templateEngineLoader = new \Twig_Loader_Filesystem(__DIR__ . '/../App/Views');
    	$this->templateEngineEnv = new \Twig_Environment($this->templateEngineLoader, self::$_envParams);
    	self::setTemplateEngineEnv();
	}

	/**
	 * Set template engine configuration
	 * @return void
	 */
	private static function setTemplateEngineEnv()
	{
		self::$_envParams['cache'] = false;
		self::$_envParams['debug'] = true;
		// Other parameters to declare: do stuff here!
	}

	/**
	 * Render Entirely a particular Twig template
	 * @param string $view: path for template to load
	 * @param array $vars: parameters to use in template 
	 * @return string: HTML content type
	 */
	public function renderTemplate($view, $vars = [])
	{
		$templateRendered = $this->templateEngineEnv->render($view, $vars);
		return $templateRendered;
	}

	/**
	 * Render only a part of a particular Twig template
	 * @param string $view: path for template to load
	 * @param string $blockName: block name in template 
	 * @param array $vars: parameters to use in template 
	 * @return string: HTML content type
	 */
	public function renderBlock($view, $blockName, $vars = [])
	{
		$template = $this->templateEngineEnv->load($view);
		$templateRendered = $template->renderBlock($blockName, $vars);
		return $templateRendered;
	}
}