<?php
namespace Core;
require_once __DIR__ . '/../Libs/vendor/autoload.php';

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
		//$template = header('Content-Type:text/html');
		$template = $this->templateEngineEnv->render($view, $vars);
		return $template;
	}

	public function getPageTitle()
	{

	}

	public function getMetaTags()
	{
		
	}

	public function getCssAssets()
	{
		
	}

	public function getJSAssets()
	{
		
	}
}