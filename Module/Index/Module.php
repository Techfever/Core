<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Index;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Kernel\ServiceLocator;

class Module {
	public function onBootstrap(MvcEvent $e) {
		$application = $e->getApplication();
		$eventManager = $application->getEventManager();
		$moduleRouteListener = new ModuleRouteListener();
		$moduleRouteListener->attach($eventManager);
		$application->getEventManager()->attach('render', array(
					$this, 'setLayoutTitle'
				));
		$this->initTheme($e);
		//print_r($e->getApplication ()->getServiceManager ()->get('translator')->getLocale());
		//die();

		$viewModel = $e->getViewModel();
		$viewModel->setVariable('left', null);
		$viewModel->setVariable('right', null);
		$viewModel->setVariable('before', null);
		$viewModel->setVariable('after', null);
	}
	public function getConfig() {
		return include __DIR__ . '/Config/module.config.php';
	}
	public function getAutoloaderConfig() {
		return array(
			'Zend\Loader\ClassMapAutoloader' => array(
				__DIR__ . '/autoload_classmap.php'
			), 'Zend\Loader\StandardAutoloader' => array(
				'namespaces' => array(
					__NAMESPACE__ => __DIR__ . '/'
				)
			)
		);
	}
	public function initTheme($e) {
		$Template = $e->getApplication()->getServiceManager()->get('Template');
		$css = array(
				'Vendor/Techfever/Theme/' . SYSTEM_THEME . '/CSS/content.css',
				'Vendor/Techfever/Theme/' . SYSTEM_THEME . '/CSS/footer.css',
				'Vendor/Techfever/Theme/' . SYSTEM_THEME . '/CSS/header.css',
				'Vendor/Techfever/Theme/' . SYSTEM_THEME . '/CSS/layout.css',
				'Vendor/Techfever/Theme/' . SYSTEM_THEME . '/CSS/left.css',
				'Vendor/Techfever/Theme/' . SYSTEM_THEME . '/CSS/navigator.css',
				'Vendor/Techfever/Theme/' . SYSTEM_THEME . '/CSS/right.css',
				'Vendor/Techfever/Theme/' . SYSTEM_THEME . '/CSS/boxes.css',
				'Vendor/Techfever/Theme/' . SYSTEM_THEME . '/CSS/breadcrumb.css',
				'Vendor/Techfever/Theme/' . SYSTEM_THEME . '/CSS/form.css',
				'Vendor/Techfever/Theme/' . SYSTEM_THEME . '/CSS/datatable.css',
		);
		$Template->addCSS($css);
		$javascript = array(
			'Vendor/Techfever/Javascript/jquery/jquery.js', 
			'Vendor/Techfever/Javascript/jquery/jquery-ui.js', 
			'Vendor/Techfever/Javascript/bootstrap.min.js', 
			'Vendor/Techfever/Theme/' . SYSTEM_THEME . '/Js/main.js'
		);
		$Template->addJavascript($javascript);
	}

	/**
	 * @param  \Zend\Mvc\MvcEvent $e The MvcEvent instance
	 * @return void
	 */
	public function setLayoutTitle($e) {
		$matches = $e->getRouteMatch();

		$translator = $e->getApplication()->getServiceManager()->get('translator');

		$title = $translator->translate('text_system_title');
		$action = $translator->translate('text_action_' . strtolower($matches->getParam('action')));
		$controller = $translator->translate('text_' . strtolower($matches->getParam('controller')));

		// Getting the view helper manager from the application service manager
		$viewHelperManager = $e->getApplication()->getServiceManager()->get('viewHelperManager');

		// Getting the headTitle helper from the view helper manager
		$headTitleHelper = $viewHelperManager->get('headTitle');

		// Setting a separator string for segments
		$headTitleHelper->setSeparator(' - ');

		// Setting the action, controller, module and site name as title segments
		$headTitleHelper->append($title);
		$headTitleHelper->append($controller);
		if (strtolower($action) !== "index") {
			$headTitleHelper->append($action);
		}

		// Getting the contentTitle helper from the view helper manager
		$contentTitleHelper = $viewHelperManager->get('contentTitle');
		// Setting
		$contentTitleHelper->set($controller);
	}
}
