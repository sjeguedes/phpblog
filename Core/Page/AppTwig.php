<?php
namespace Core\Page;

/**
 * Manage Twig Template engine
 */
class AppTwig
{
    /**
     * @var Twig_Loader_Filesystem instance
     */
    private static $_templateEngineLoader;
    /**
     * @var Twig_Environment instance
     */
    private static $_templateEngineEnv;
    /**
     * @var array: an array of parameters to use for template engine configuration
     */
    private static $_envParams = [];

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        self::$_templateEngineLoader = new \Twig_Loader_Filesystem(__DIR__ . '/../../App/Views');
        self::$_templateEngineEnv = new \Twig_Environment(self::$_templateEngineLoader, self::$_envParams);
        // Add template_from_string function
        self::$_templateEngineEnv->addExtension(new \Twig_Extension_StringLoader());
        // Set environment parameters
        self::setTemplateEngineEnv();
    }

    /**
     * Set Twig template engine configuration
     *
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
     *
     * @param string $view: path for template to load
     * @param array $vars: parameters to use in template
     *
     * @return string: HTML content type
     */
    public function renderTwigTemplate($view, $vars = [])
    {
        // Render template
        $templateRendered = self::$_templateEngineEnv->render($view, $vars);
        return $templateRendered;
    }

    /**
     * Render only a part of a particular Twig template
     *
     * @param string $view: path for template to load
     * @param string $blockName: block name in template
     * @param array $vars: parameters to use in template
     *
     * @return string: HTML content type
     */
    public function renderTwigBlock($view, $blockName, $vars = [])
    {
        // Render block
        $template = self::$_templateEngineEnv->load($view);
        $templateRendered = $template->renderBlock($blockName, $vars);
        return $templateRendered;
    }
}
