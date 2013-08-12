<?php
namespace Techfever\Template\Plugin\Controllers;

use Zend\Session\Container;
use Zend\Session\ManagerInterface as Manager;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\InjectApplicationEventInterface;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\EventInterface;
use Techfever\Exception;

/**
 * Placeholder - implement session-based value
 */
class Placeholder extends AbstractPlugin {

	/**
	 * @var Container
	 */
	protected $container;

	/**
	 * values from previous request
	 * @var array
	 */
	protected $values = array();

	/**
	 * @var Manager
	 */
	protected $session;

	/**
	 * Whether a value has been added during this request
	 *
	 * @var bool
	 */
	protected $valueAdded = false;

	/**
	 * Instance namespace, default is 'default'
	 *
	 * @var string
	 */
	protected $namespace = 'default';

	/**
	 * Set the session manager
	 *
	 * @param  Manager        $manager
	 * @return Placeholder
	 */
	public function setSessionManager(Manager $manager) {
		$this->session = $manager;

		return $this;
	}

	/**
	 * Retrieve the session manager
	 *
	 * If none composed, lazy-loads a SessionManager instance
	 *
	 * @return Manager
	 */
	public function getSessionManager() {
		if (!$this->session instanceof Manager) {
			$this->setSessionManager(Container::getDefaultManager());
		}

		return $this->session;
	}

	/**
	 * Get session container for value
	 *
	 * @return Container
	 */
	public function getContainer() {
		if ($this->container instanceof Container) {
			return $this->container;
		}

		$manager = $this->getSessionManager();
		$this->container = new Container('Placeholder', $manager);

		return $this->container;
	}

	/**
	 * Change the namespace value are added to
	 *
	 * Useful for per action controller value between requests
	 *
	 * @param  string         $namespace
	 * @return Placeholder Provides a fluent interface
	 */
	public function setNamespace($namespace = null) {
		$this->namespace = $namespace;

		return $this;
	}

	/**
	 * Get the value namespace
	 *
	 * @return string
	 */
	public function getNamespace() {
		if (empty($this->namespace)) {
			$controller = $this->getController();
			if (!$controller instanceof InjectApplicationEventInterface) {
				throw new Exception\DomainException('MatchedRouteName plugin requires a controller that implements InjectApplicationEventInterface');
			}

			$event = $controller->getEvent();
			$matches = null;
			if ($event instanceof MvcEvent) {
				$matches = $event->getRouteMatch();
			} elseif ($event instanceof EventInterface) {
				$matches = $event->getParam('route-match', false);
			}
			if (!$matches) {
				throw new Exception\RuntimeException('No RouteMatch instance present');
			}

			$this->namespace = $matches->getMatchedRouteName();
		}
		return $this->namespace;
	}

	/**
	 * Set Value
	 *
	 * @param  Integer	$value
	 * @param  String	$route
	 * @return Placeholder Provides a fluent interface
	 */
	public function set($value, $namespace = null) {
		if (!empty($namespace)) {
			$this->setNamespace($namespace);
		}

		$container = $this->getContainer();
		$namespace = $this->getNamespace();

		$container->offsetSet($namespace, $value);

		if ($this->has($namespace)) {
			return true;
		}
		return false;
	}

	/**
	 * Whether a specific namespace has value
	 *
	 * @return bool
	 */
	public function has($namespace = null) {
		if (!empty($namespace)) {
			$this->setNamespace($namespace);
		}

		$container = $this->getContainer();
		$namespace = $this->getNamespace();

		if ($container->offsetExists($namespace)) {
			return true;
		}

		return false;
	}

	/**
	 * Get values from a specific namespace
	 *
	 * @return array
	 */
	public function get($namespace = null) {
		if (!empty($namespace)) {
			$this->setNamespace($namespace);
		}

		$container = $this->getContainer();
		$namespace = $this->getNamespace();

		$value = null;
		if ($this->has($namespace)) {
			$value = $container->offsetGet($namespace);
			$this->clearFromNamespace($namespace);
		}

		return $value;
	}

	/**
	 * Clear all values from specific namespace
	 *
	 * @param  string $namespaceToClear
	 * @return bool True if values were cleared, false if none existed
	 */
	public function clearFromNamespace($namespace = null) {
		if (!empty($namespace)) {
			$this->setNamespace($namespace);
		}

		$container = $this->getContainer();
		$namespace = $this->getNamespace();

		if ($this->has($namespace)) {
			$container->offsetUnset($namespace);
			return true;
		}
		return false;
	}

	/**
	 * Clear all values from the container
	 *
	 * @return bool True if values were cleared, false if none existed
	 */
	public function clearFromContainer() {
		$container = $this->getContainer();
		$container->getManager()->getStorage()->clear('user');

		return true;
	}
}
