<?php

namespace Index;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Loader\ClassMapAutoloader;
use Zend\Validator\AbstractValidator;
use Techfever\Functions\DirConvert;
use Techfever\Template\Plugin\Filters\ToUnderscore;
use Techfever\Template\InjectTemplateListener;

class Module {
	private $template = NULL;
	public function onBootstrap(MvcEvent $e) {
		$serviceManager = $e->getApplication ()->getServiceManager ();
		$serviceManager->get ( 'php' );
		
		$this->template = $serviceManager->get ( 'template' );
		
		$this->getClassMapAutoloaderConfig ();
		
		$this->getControllerLoader ( $serviceManager );
		
		$this->getRouter ( $serviceManager );
		
		$this->getViewResolver ( $serviceManager );
		
		$config = $serviceManager->get ( 'Config' );
		$config = array_merge ( $config, $this->getTemplate ()->getModuleManager () );
		$config = array_merge ( $config, array (
				'system' => $this->getTemplate ()->getConfig () 
		) );
		$serviceManager->setAllowOverride ( True );
		$serviceManager->setService ( 'Config', $config );
		$serviceManager->setAllowOverride ( False );
		
		$serviceManager->get ( 'snapshot' );
		
		$eventManager = $e->getApplication ()->getEventManager ();
		/*
		 * $eventManager->attach ( 'route', array ( $this, 'onRouteFinish' ), - 100 );
		 */
		
		$moduleRouteListener = new ModuleRouteListener ();
		$moduleRouteListener->attach ( $eventManager );
		
		$sharedManager = $e->getApplication ()->getEventManager ()->getSharedManager ();
		
		$injectTemplateListener = new InjectTemplateListener ();
		
		$sharedManager->attach ( 'Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH, array (
				$injectTemplateListener,
				'injectTemplate' 
		), - 81 );
		
		$ServiceListener = $serviceManager->get ( 'ServiceListener' );
		$ServiceListener->addServiceManager ( 'ViewElementManager', 'view_elements', 'Techfever\View\ViewElementProviderInterface', 'getViewElementConfig' );
		$eventManager->attach ( $ServiceListener );
		
		$sharedManager->attach ( 'Zend\Mvc\Controller\AbstractActionController', MvcEvent::EVENT_DISPATCH, function ($e) {
			$serviceManager = $e->getApplication ()->getServiceManager ();
			$controller = $e->getTarget ();
			
			$isBackend = $controller->isBackend ();
			$system_theme = SYSTEM_THEME;
			if ($isBackend) {
				$e->getViewModel ()->setTemplate ( 'backend/layout' );
				$system_theme = "Backend";
			}
			define ( 'SYSTEM_THEME_LOAD', $system_theme );
			
			$ToUnderscore = new ToUnderscore ( '\\' );
			$routematch = $controller->getEvent ()->getRouteMatch ();
			$controlleraction = strtolower ( $routematch->getParam ( 'action' ) );
			$controllername = strtolower ( $routematch->getParam ( 'controller' ) );
			$controlleruri = strtolower ( $routematch->getMatchedRouteName () );
			
			$routeMatchParams = $routematch->getParams ();
			if (isset ( $routeMatchParams ['controller'] )) {
				unset ( $routeMatchParams ['controller'] );
			}
			if (isset ( $routeMatchParams ['action'] )) {
				unset ( $routeMatchParams ['action'] );
			}
			$controllerquery = implode ( '/', $routeMatchParams );
			
			$e->getViewModel ()->setVariable ( 'controllerUri', $controlleruri );
			$e->getViewModel ()->setVariable ( 'controllerName', $controllername );
			$e->getViewModel ()->setVariable ( 'controllerAction', $controlleraction );
			$e->getViewModel ()->setVariable ( 'controllerQuery', $controllerquery );
			$e->getViewModel ()->setVariable ( 'controllerUriFull', $system_theme . '/' . $controlleruri . '/' . $controlleraction . '/' . $controllerquery );
			
			if ($controllername == "index\\controller\\action" && strtolower ( SYSTEM_BACKEND_ONLY ) == "true") {
				$url = $e->getRouter ()->assemble ( array (), array (
						'name' => SYSTEM_BACKEND_URI 
				) );
				$response = $serviceManager->get ( 'Response' );
				$response->getHeaders ()->addHeaderLine ( 'Location', $url );
				$response->setStatusCode ( 302 );
			} else {
				$UserPermission = $serviceManager->get ( 'UserPermission' );
				if (! $UserPermission->isAllow ( $controllername, $controlleraction )) {
					$url = $e->getRouter ()->assemble ( array (), array (
							'name' => 'Index' 
					) );
					$response = $serviceManager->get ( 'Response' );
					$response->getHeaders ()->addHeaderLine ( 'Location', $url );
					$response->setStatusCode ( 302 );
				}
			}
			
			$controlleraction = $ToUnderscore->filter ( $controlleraction );
			$controllername = $ToUnderscore->filter ( $controllername );
			$e->getViewModel ()->setVariable ( 'moduleTitle', 'text_' . $controllername . '_' . $controlleraction . '_title' );
			
			$moduleLogin = False;
			if ($controllername == "account_login_controller_action") {
				$moduleLogin = True;
			}
			$e->getViewModel ()->setVariable ( 'moduleLogin', $moduleLogin );
			
			$moduleDashboard = False;
			if ($controllername == "account_dashboard_controller_action") {
				$moduleDashboard = True;
			}
			$e->getViewModel ()->setVariable ( 'moduleDashboard', $moduleDashboard );
			
			$isLogin = False;
			$isAdminUser = False;
			$getUserIDAction = 0;
			$UserAccess = $serviceManager->get ( 'UserAccess' );
			if ($UserAccess->isLogin ()) {
				$isLogin = True;
			}
			if ($UserAccess->isAdminUser ()) {
				$isAdminUser = True;
				$getUserIDAction = 1;
			}
			$e->getViewModel ()->setVariable ( 'isLogin', $isLogin );
			$e->getViewModel ()->setVariable ( 'isAdminUser', $isAdminUser );
			$e->getViewModel ()->setVariable ( 'getUserIDAction', $getUserIDAction );
			
			$translator = $serviceManager->get ( 'translator' );
			$Locale = $translator->getLocale ();
			$AllLocale = $translator->getAllLocale ();
			$verifyLocale = $translator->checkLocale ( $Locale );
			if (! $verifyLocale) {
				$Locale = SYSTEM_DEFAULT_LOCALE;
			}
			$e->getViewModel ()->setVariable ( 'translatorLocale', $AllLocale );
			$e->getViewModel ()->setVariable ( 'Locale', $Locale );
			
			$viewHelperManager = $serviceManager->get ( 'viewHelperManager' );
			
			$headTitleHelper = $viewHelperManager->get ( 'headTitle' );
			
			$headTitleHelper->setSeparator ( ' - ' );
			
			$headTitleHelper->append ( 'text_system_title' );
			
			$headTitleHelper->append ( 'text_' . $controllername . '_' . $controlleraction . '_title' );
			
			$request = $serviceManager->get ( 'Request' );
			
			$addTheme = True;
			if (substr ( $controllername, 0, 5 ) == 'theme') {
				$addTheme = false;
			} else if (substr ( $controllername, 0, 4 ) == 'ajax') {
				$addTheme = false;
			} else if (substr ( $controllername, 0, 5 ) == 'image') {
				$addTheme = false;
			} else if ($request->isXmlHttpRequest ()) {
				$addTheme = false;
			}
			if ($addTheme) {
				$this->getThemes ();
			}
		}, 100 );
		$serviceManager->get ( 'UserLog' );
		
		AbstractValidator::setDefaultTranslator ( $serviceManager->get ( 'MvcTranslator' ) );
	}
	public function onRouteFinish($e) {
		$matches = $e->getRouteMatch ();
		$controller = $matches->getParam ( 'controller' );
		var_dump ( $matches );
		die ();
	}
	public function getAutoloaderConfig() {
		return array (
				'Zend\Loader\StandardAutoloader' => array (
						'namespaces' => array (
								__NAMESPACE__ => __DIR__ . '/' 
						) 
				) 
		);
	}
	public function getClassMapAutoloaderConfig() {
		$AutoloadClassmap = new ClassMapAutoloader ();
		$AutoloadClassmap->registerAutoloadMap ( $this->getTemplate ()->getClassMapAutoloader () );
		$AutoloadClassmap->register ();
	}
	public function getControllerLoader($serviceManager) {
		$controllerLoader = $serviceManager->get ( 'ControllerLoader' );
		$controllers = $this->getTemplate ()->getControllers ()->getStructure ();
		if (is_array ( $controllers ) && count ( $controllers ) > 0) {
			foreach ( $controllers as $key => $services ) {
				if (is_array ( $services ) && count ( $services ) > 0) {
					foreach ( $services as $servicealias => $serviceclass ) {
						if ($key == 'invokables') {
							$controllerLoader->setInvokableClass ( $servicealias, $serviceclass );
						} elseif ($key == 'factories') {
							$controllerLoader->setFactory ( $servicealias, $serviceclass );
						}
					}
				}
			}
		}
	}
	public function getRouter($serviceManager) {
		$router = $serviceManager->get ( 'Router' );
		$routers = $this->getTemplate ()->getRouter ()->getStructure ();
		$router->addRoutes ( $routers ['routes'] );
	}
	public function getViewResolver($serviceManager) {
		$viewTemplateMapResolver = $serviceManager->get ( 'ViewTemplateMapResolver' );
		$viewTemplateMapResolver->setMap ( $this->getTemplate ()->getViewManager ()->getTemplateMap () );
		
		$viewTemplatePathStack = $serviceManager->get ( 'ViewTemplatePathStack' );
		$viewTemplatePathStack->addPaths ( $this->getTemplate ()->getViewManager ()->getTemplatePathStack () );
		
		$resolver = $serviceManager->get ( 'ViewResolver' );
		$resolver->attach ( $viewTemplateMapResolver );
		$resolver->attach ( $viewTemplatePathStack );
	}
	
	/**
	 * Expected to return \Zend\ServiceManager\Config object or array to
	 * seed such an object.
	 *
	 * @return array \Zend\ServiceManager\Config
	 */
	public function getViewHelperConfig() {
		$plugin = $this->getPlugin ( 'Helpers' );
		return array (
				'invokables' => $plugin 
		);
	}
	
	/**
	 * Expected to return \Zend\ServiceManager\Config object or array to
	 * seed such an object.
	 *
	 * @return array \Zend\ServiceManager\Config
	 */
	public function getValidatorConfig() {
		$plugin = $this->getPlugin ( 'Validators' );
		return array (
				'invokables' => $plugin 
		);
	}
	
	/**
	 * Expected to return \Zend\ServiceManager\Config object or array to
	 * seed such an object.
	 *
	 * @return array \Zend\ServiceManager\Config
	 */
	public function getFilterConfig() {
		$plugin = $this->getPlugin ( 'Filters' );
		return array (
				'invokables' => $plugin 
		);
	}
	
	/**
	 * Expected to return \Zend\ServiceManager\Config object or array to
	 * seed such an object.
	 *
	 * @return array \Zend\ServiceManager\Config
	 */
	public function getFormElementConfig() {
		$plugin = $this->getPlugin ( 'Forms' );
		return array (
				'invokables' => $plugin 
		);
	}
	
	/**
	 * Expected to return \Zend\ServiceManager\Config object or array to
	 * seed such an object.
	 *
	 * @return array \Zend\ServiceManager\Config
	 */
	public function getViewElementConfig() {
		$plugin = $this->getPlugin ( 'Views' );
		return array (
				'invokables' => $plugin 
		);
	}
	
	/**
	 * Expected to return \Zend\ServiceManager\Config object or array to
	 * seed such an object.
	 *
	 * @return array \Zend\ServiceManager\Config
	 */
	public function getControllerPluginConfig() {
		$plugin = $this->getPlugin ( 'Controllers' );
		return array (
				'invokables' => $plugin 
		);
	}
	public function getThemes() {
		$css = 'vendor/Techfever/Theme/' . SYSTEM_THEME_LOAD . '/css.php';
		$javascript = 'vendor/Techfever/Theme/' . SYSTEM_THEME_LOAD . '/javascript.php';
		if (file_exists ( $css )) {
			$DirConvert = new DirConvert ( $css );
			$css = $DirConvert->__toString ();
			$css_content = include $css;
			$this->getTemplate ()->addCSS ( $css_content );
		}
		
		if (file_exists ( $javascript )) {
			$DirConvert = new DirConvert ( $javascript );
			$javascript = $DirConvert->__toString ();
			$javascript_content = include $javascript;
			$this->getTemplate ()->addJavascript ( $javascript_content );
		}
	}
	public function getTemplate() {
		return $this->template;
	}
	
	/**
	 * Get Plugin
	 *
	 * @return string
	 *
	 */
	public function getPlugin($path = null) {
		$DirConvert = new DirConvert ( getcwd () . '/vendor/Techfever/Template/Plugin/' . (! empty ( $path ) ? $path . '/' : null) );
		$dir = $DirConvert->__toString ();
		$data = array ();
		if (file_exists ( $dir )) {
			$dh = opendir ( $dir );
			while ( false !== ($filename = readdir ( $dh )) ) {
				$fileinfo = pathinfo ( $dir . $filename );
				if ($fileinfo ['basename'] != '.' && $fileinfo ['basename'] != '..' && is_dir ( $dir . $fileinfo ['basename'] )) {
					$datachild = $this->getList ( $path . '/' . $fileinfo ['basename'] );
					$data = array_merge ( $data, $datachild );
				} elseif ($fileinfo ['extension'] == 'php' && $fileinfo ['filename'] != 'HelperConfig') {
					$data [$fileinfo ['filename']] = 'Techfever\Template\Plugin\\' . ucfirst ( $path ) . '\\' . $fileinfo ['filename'];
				}
			}
		}
		return $data;
	}
}
