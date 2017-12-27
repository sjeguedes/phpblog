<?php
namespace Core;

// Composer autoloader
if( !class_exists('Composer\\Autoload\\ClassLoader') )
{
	require_once __DIR__ . '/../Libs/vendor/autoload.php';
}

class AppPage 
{
	private $templateEngineLoader;
	private $templateEngineEnv;
	private static $_envParams = [];

	private $pageMetaTags = [];
	private $pageAssets = [];

	public function __construct()   
	{
		$this->templateEngineLoader = new \Twig_Loader_Filesystem(__DIR__ . '/../App/Views');
    	$this->templateEngineEnv = new \Twig_Environment($this->templateEngineLoader, self::$_envParams);
    	self::setTemplateEngineEnv();
	}

	private static function setTemplateEngineEnv()
	{
		self::$_envParams['cache'] = false;
		self::$_envParams['debug'] = true;
		//...
	}

	public function renderTemplate($view, $vars = [])
	{
		$templateRendered = $this->templateEngineEnv->render($view, $vars);
		return $templateRendered;
	}

	public function renderBlock($view, $blockName, $vars = [])
	{
		$template = $this->templateEngineEnv->load($view);
		$templateRendered = $template->renderBlock($blockName, $vars);
		return $templateRendered;
	}

	public function getPageTitle()
	{
		// Do stuff if declared in file or database
	}

	public function getMetaTags()
	{
		// Do stuff if declared in file or database
	}

	public function getCssAssets()
	{
		// Do stuff if declared in file or database
	}

	public function getJSAssets()
	{
		// Do stuff if declared in file or database
	}
}