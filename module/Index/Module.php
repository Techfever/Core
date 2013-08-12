<?php
namespace Index;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Loader\ClassMapAutoloader;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\ModuleManager;
use Zend\Validator\AbstractValidator;
use Techfever\Functions\DirConvert;

class Module {
	private $template = NULL;

	public function onBootstrap(MvcEvent $e) {

		$serviceManager = $e->getApplication()->getServiceManager();
		$serviceManager->get('php');

		$this->template = $serviceManager->get('template');
		$this->getThemes();

		$this->getClassMapAutoloaderConfig();

		$this->getControllerLoader($serviceManager);

		$this->getRouter($serviceManager);

		$this->getViewResolver($serviceManager);

		$config = $serviceManager->get('Config');
		$config = array_merge($config, $this->getTemplate()->getModuleManager());
		$config = array_merge($config, array(
				'system' => $this->getTemplate()->getConfig()
		));
		$serviceManager->setAllowOverride(True);
		$serviceManager->setService('Config', $config);
		$serviceManager->setAllowOverride(False);

		$serviceManager->get('snapshot');

		$eventManager = $e->getApplication()->getEventManager();

		$moduleRouteListener = new ModuleRouteListener();
		$moduleRouteListener->attach($eventManager);
		/*
		        $eventManager
		                ->attach(\Zend\Mvc\MvcEvent::EVENT_RENDER,
		                        function ($e) {
		                            $flashMessenger = new \Zend\Mvc\Controller\Plugin\FlashMessenger();
		                            $flashMessages = array(
		                                    'error' => array(),
		                                    'info' => array(),
		                                    'success' => array(),
		                                    'general' => array()
		                            );
		                            if ($flashMessenger->hasErrorMessages()) {
		                                $flashMessages['error'] = $flashMessenger->getErrorMessages();
		                            }
		                            if ($flashMessenger->hasInfoMessages()) {
		                                $flashMessages['info'] = $flashMessenger->getInfoMessage();
		                            }
		                            if ($flashMessenger->hasSuccessMessages()) {
		                                $flashMessages['success'] = $flashMessenger->getSuccessMessage();
		                            }
		                            if ($flashMessenger->hasMessages()) {
		                                $flashMessages['general'] = $flashMessenger->getMessages();
		                            }
		                            $e->getViewModel()->setVariable('flashMessenges', $flashMessages);
		                        });
		 */
		$sharedManager = $e->getApplication()->getEventManager()->getSharedManager();
		$sharedManager
				->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch',
						function ($e) {
							$controller = $e->getTarget();
							$controlleraction = strtolower($controller->getEvent()->getRouteMatch()->getParam('action'));
							$controlleraction = str_replace('\\', '_', $controlleraction);
							$controllername = strtolower($controller->getEvent()->getRouteMatch()->getParam('controller'));
							$controllername = str_replace('\\', '_', $controllername);
							$e->getViewModel()->setVariable('moduleTitle', 'text_' . $controllername . '_' . $controlleraction . '_title');

							$moduleLogin = False;
							if ($controllername == "account_controller_loginaction") {
								$moduleLogin = True;
							}
							$e->getViewModel()->setVariable('moduleLogin', $moduleLogin);

							$isLogin = False;
							$serviceManager = $e->getApplication()->getServiceManager();
							$UserAccess = $serviceManager->get('UserAccess');
							if ($UserAccess->isLogin()) {
								$isLogin = True;
							}
							$e->getViewModel()->setVariable('UserAccess', $UserAccess);
							$e->getViewModel()->setVariable('isLogin', $isLogin);
						}, 100);

		AbstractValidator::setDefaultTranslator($serviceManager->get('MvcTranslator'));
	}

	public function getAutoloaderConfig() {
		return array(
				'Zend\Loader\StandardAutoloader' => array(
						'namespaces' => array(
								__NAMESPACE__ => __DIR__ . '/',
						),
				),
		);
	}

	public function getClassMapAutoloaderConfig() {
		$AutoloadClassmap = new ClassMapAutoloader();
		$AutoloadClassmap->registerAutoloadMap($this->getTemplate()->getClassMapAutoloader());
		$AutoloadClassmap->register();
	}

	public function getControllerLoader($serviceManager) {
		$controllerLoader = $serviceManager->get('ControllerLoader');
		$controllers = $this->getTemplate()->getControllers()->getStructure();
		if (is_array($controllers) && count($controllers) > 0) {
			foreach ($controllers as $key => $services) {
				if (is_array($services) && count($services) > 0) {
					foreach ($services as $servicealias => $serviceclass) {
						if ($key == 'invokables') {
							$controllerLoader->setInvokableClass($servicealias, $serviceclass);
						} elseif ($key == 'factories') {
							$controllerLoader->setFactory($servicealias, $serviceclass);
						}
					}
				}
			}
		}
	}

	public function getRouter($serviceManager) {
		$router = $serviceManager->get('Router');
		$routers = $this->getTemplate()->getRouter()->getStructure();
		$router->addRoutes($routers['routes']);
	}

	public function getViewResolver($serviceManager) {
		$viewTemplateMapResolver = $serviceManager->get('ViewTemplateMapResolver');
		$viewTemplateMapResolver->setMap($this->getTemplate()->getViewManager()->getTemplateMap());

		$viewTemplatePathStack = $serviceManager->get('ViewTemplatePathStack');
		$viewTemplatePathStack->addPaths($this->getTemplate()->getViewManager()->getTemplatePathStack());

		$resolver = $serviceManager->get('ViewResolver');
		$resolver->attach($viewTemplateMapResolver);
		$resolver->attach($viewTemplatePathStack);
	}

	/**
	 * Expected to return \Zend\ServiceManager\Config object or array to
	 * seed such an object.
	 *
	 * @return array|\Zend\ServiceManager\Config
	 */
	public function getViewHelperConfig() {
		$plugin = $this->getPlugin('Helpers');
		return array(
				'invokables' => $plugin
		);
	}

	/**
	 * Expected to return \Zend\ServiceManager\Config object or array to
	 * seed such an object.
	 *
	 * @return array|\Zend\ServiceManager\Config
	 */
	public function getValidatorConfig() {
		$plugin = $this->getPlugin('Validators');
		return array(
				'invokables' => $plugin
		);
	}

	/**
	 * Expected to return \Zend\ServiceManager\Config object or array to
	 * seed such an object.
	 *
	 * @return array|\Zend\ServiceManager\Config
	 */
	public function getFilterConfig() {
		$plugin = $this->getPlugin('Filters');
		return array(
				'invokables' => $plugin
		);
	}

	/**
	 * Expected to return \Zend\ServiceManager\Config object or array to
	 * seed such an object.
	 *
	 * @return array|\Zend\ServiceManager\Config
	 */
	public function getFormElementConfig() {
		$plugin = $this->getPlugin('Forms');
		return array(
				'invokables' => $plugin
		);
	}

	/**
	 * Expected to return \Zend\ServiceManager\Config object or array to
	 * seed such an object.
	 *
	 * @return array|\Zend\ServiceManager\Config
	 */
	public function getControllerPluginConfig() {
		$plugin = $this->getPlugin('Controllers');
		return array(
				'invokables' => $plugin
		);
	}

	public function getThemes() {
		//$this->getTemplate()->resetCSS();
		$css = array(
				'vendor/Techfever/Javascript/jquery/themes/ui-lightness/jquery-ui.css',
				'vendor/Techfever/Theme/' . SYSTEM_THEME . '/CSS/content.css',
				'vendor/Techfever/Theme/' . SYSTEM_THEME . '/CSS/footer.css',
				'vendor/Techfever/Theme/' . SYSTEM_THEME . '/CSS/header.css',
				'vendor/Techfever/Theme/' . SYSTEM_THEME . '/CSS/layout.css',
				'vendor/Techfever/Theme/' . SYSTEM_THEME . '/CSS/left.css',
				'vendor/Techfever/Theme/' . SYSTEM_THEME . '/CSS/right.css',
				'vendor/Techfever/Theme/' . SYSTEM_THEME . '/CSS/boxes.css',
				'vendor/Techfever/Theme/' . SYSTEM_THEME . '/CSS/breadcrumb.css',
				'vendor/Techfever/Theme/' . SYSTEM_THEME . '/CSS/form.css',
				'vendor/Techfever/Theme/' . SYSTEM_THEME . '/CSS/datatable.css',
				'vendor/Techfever/Theme/' . SYSTEM_THEME . '/CSS/preview.css',
				'vendor/Techfever/Theme/' . SYSTEM_THEME . '/CSS/modern-menu.css',
		);
		$this->getTemplate()->addCSS($css);
		//$this->getTemplate()->resetJavascript();
		$javascript = array(
				'vendor/Techfever/Javascript/jquery/jquery.js',
				'vendor/Techfever/Javascript/jquery/ui/jquery-ui.js',
				'vendor/Techfever/Javascript/jquery/ui/jquery.modern-menu.min.js',
				'vendor/Techfever/Theme/' . SYSTEM_THEME . '/Js/main.js',
		);
		$this->getTemplate()->addJavascript($javascript);
	}

	public function getTemplate() {
		return $this->template;
	}

	/**
	 * Get Plugin
	 *
	 * @return string
	 **/
	public function getPlugin($path = null) {
		$DirConvert = new DirConvert(getcwd() . '/vendor/Techfever/Template/Plugin/' . (!empty($path) ? $path . '/' : null));
		$dir = $DirConvert->__toString();
		$data = array();
		if (file_exists($dir)) {
			$dh = opendir($dir);
			while (false !== ($filename = readdir($dh))) {
				$fileinfo = pathinfo($dir . $filename);
				if ($fileinfo['basename'] != '.' && $fileinfo['basename'] != '..' && is_dir($dir . $fileinfo['basename'])) {
					$datachild = $this->getList($path . '/' . $fileinfo['basename']);
					$data = array_merge($data, $datachild);
				} elseif ($fileinfo['extension'] == 'php' && $fileinfo['filename'] != 'HelperConfig') {
					$data[$fileinfo['filename']] = 'Techfever\Template\Plugin\\' . ucfirst($path) . '\\' . $fileinfo['filename'];
				}
			}
		}
		return $data;
	}
}
