<?php
namespace Techfever\View;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ViewAbstractServiceFactory implements AbstractFactoryInterface {
	/**
	 * @var array
	 */
	protected $config;

	/**
	 * @var string Top-level configuration key indicating Views configuration
	 */
	protected $configKey = 'views';

	/**
	 * @var Factory View factory used to create Views
	 */
	protected $factory;

	/**
	 * Can we create the requested service?
	 *
	 * @param  ServiceLocatorInterface $services
	 * @param  string $name Service name (as resolved by ServiceManager)
	 * @param  string $rName Name by which service was requested
	 * @return bool
	 */
	public function canCreateServiceWithName(ServiceLocatorInterface $services, $name, $rName) {
		$config = $this->getConfig($services);
		if (empty($config)) {
			return false;
		}

		return (isset($config[$rName]) && is_array($config[$rName]) && !empty($config[$rName]));
	}

	/**
	 * Create a View
	 *
	 * @param  ServiceLocatorInterface $services
	 * @param  string $name Service name (as resolved by ServiceManager)
	 * @param  string $rName Name by which service was requested
	 * @return View
	 */
	public function createServiceWithName(ServiceLocatorInterface $services, $name, $rName) {
		$config = $this->getConfig($services);
		$config = $config[$rName];
		$factory = $this->getViewFactory($services);

		return $factory->createView($config);
	}

	/**
	 * Get Views configuration, if any
	 *
	 * @param  ServiceLocatorInterface $services
	 * @return array
	 */
	protected function getConfig(ServiceLocatorInterface $services) {
		if ($this->config !== null) {
			return $this->config;
		}

		if (!$services->has('Config')) {
			$this->config = array();
			return $this->config;
		}

		$config = $services->get('Config');
		if (!isset($config[$this->configKey]) || !is_array($config[$this->configKey])) {
			$this->config = array();
			return $this->config;
		}

		$this->config = $config[$this->configKey];
		return $this->config;
	}

	/**
	 * Retrieve the View factory, creating it if necessary
	 *
	 * @param  ServiceLocatorInterface $services
	 * @return Factory
	 */
	protected function getViewFactory(ServiceLocatorInterface $services) {
		if ($this->factory instanceof Factory) {
			return $this->factory;
		}

		$elements = null;
		if ($services->has('ViewElementManager')) {
			$elements = $services->get('ViewElementManager');
		}

		$this->factory = new Factory($elements);
		return $this->factory;
	}
}
