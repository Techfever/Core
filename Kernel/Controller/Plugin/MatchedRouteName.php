<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Kernel\Controller\Plugin;

use Zend\EventManager\EventInterface;
use Zend\Mvc\InjectApplicationEventInterface;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Kernel\Exception;

class MatchedRouteName extends AbstractPlugin {

	/**
	 * @param MatchedRouteName
	 */
	public function __invoke() {
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

		return $matches->getMatchedRouteName();
	}
}
