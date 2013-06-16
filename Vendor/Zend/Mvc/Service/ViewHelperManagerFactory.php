<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Service;

use Zend\Console\Console;
use Zend\Mvc\Exception;
use Zend\Mvc\Router\RouteMatch;
use Zend\ServiceManager\ConfigInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper as ViewHelper;
use Zend\View\Helper\HelperInterface as ViewHelperInterface;

class ViewHelperManagerFactory extends AbstractPluginManagerFactory
{
    const PLUGIN_MANAGER_CLASS = 'Zend\View\HelperPluginManager';

    /**
     * An array of helper configuration classes to ensure are on the helper_map stack.
     *
     * @var array
     */
    protected $defaultHelperMapClasses = array(
        'Zend\Form\View\HelperConfig',
        'Zend\I18n\View\HelperConfig',
        'Zend\Navigation\View\HelperConfig'
    );

    /**
     * Create and return the view helper manager
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return ViewHelperInterface
     * @throws Exception\RuntimeException
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $plugins = parent::createService($serviceLocator);

        foreach ($this->defaultHelperMapClasses as $configClass) {
            if (is_string($configClass) && class_exists($configClass)) {
                $config = new $configClass;

                if (!$config instanceof ConfigInterface) {
                    throw new Exception\RuntimeException(sprintf(
                        'Invalid service manager configuration class provided; received "%s", expected class implementing %s',
                        $configClass,
                        'Zend\ServiceManager\ConfigInterface'
                    ));
                }

                $config->configureServiceManager($plugins);
            }
        }

        // Configure URL view helper with router
        $plugins->setFactory('url', function ($sm) use($serviceLocator) {
            $helper = new ViewHelper\Url;
            $router = Console::isConsole() ? 'HttpRouter' : 'Router';
            $helper->setRouter($serviceLocator->get($router));

            $match = $serviceLocator->get('application')
                ->getMvcEvent()
                ->getRouteMatch()
            ;

            if ($match instanceof RouteMatch) {
                $helper->setRouteMatch($match);
            }

            return $helper;
        });

        $plugins->setFactory('basepath', function ($sm) use($serviceLocator) {
            $config = $serviceLocator->get('Config');
            $basePathHelper = new ViewHelper\BasePath;
            if (isset($config['view_manager']) && isset($config['view_manager']['base_path'])) {
                $basePath = $config['view_manager']['base_path'];
            } else {
                $basePath = $serviceLocator->get('Request')->getBasePath();
            }
            $basePathHelper->setBasePath($basePath);
            return $basePathHelper;
        });

        $plugins->setFactory('basehref', function ($sm) use($serviceLocator) {
            $config = $serviceLocator->get('Config');
            $baseHrefHelper = new ViewHelper\BaseHref;
            if (isset($config['view_manager']) && isset($config['view_manager']['base_href'])) {
                $baseHref = $config['view_manager']['base_href'];
            } else {
                $baseHref = $serviceLocator->get('Request')->getBaseHref();
            }
            $baseHrefHelper->setBaseHref($baseHref);
            return $baseHrefHelper;
        });

        /**
         * Configure doctype view helper with doctype from configuration, if available.
         *
         * Other view helpers depend on this to decide which spec to generate their tags
         * based on. This is why it must be set early instead of later in the layout phtml.
         */
        $plugins->setFactory('doctype', function ($sm) use($serviceLocator) {
            $config = $serviceLocator->get('Config');
            $config = $config['view_manager'];
            $doctypeHelper = new ViewHelper\Doctype;
            if (isset($config['doctype'])) {
                $doctypeHelper->setDoctype($config['doctype']);
            }
            return $doctypeHelper;
        });

        return $plugins;
    }
}