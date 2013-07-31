<?php
namespace Kernel;

use Zend\Session\Container as SessionContainer;
use Kernel\Database\Database;
use Kernel\ServiceLocator;

class Navigator {
	/**
	 * @var Data
	 **/
	private $_data = null;

	/**
	 * @var Query
	 **/
	private $_query = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$DBNavigator = new Database('select');
		$DBNavigator
				->columns(
						array(
								'root' => 'system_navigator_id',
								'label' => 'system_navigator_label',
								'route' => 'system_navigator_route',
								'action' => 'system_navigator_action',
								'controller' => 'system_navigator_controller',
								'uri' => 'system_navigator_uri',
								'class' => 'system_navigator_css_class',
								'order' => 'system_navigator_order',
								'visible' => 'system_navigator_visible',
								'parent' => 'system_navigator_parent'
						));
		$DBNavigator->from(array(
						'sn' => 'system_navigator'
				));
		$DBNavigator->order(array(
						'system_navigator_parent ASC',
						'system_navigator_order ASC',
						'system_navigator_label ASC'
				));
		$DBNavigator->setCacheName('system_navigator');
		$DBNavigator->execute();
		if ($DBNavigator->hasResult()) {
			$this->_query = $DBNavigator->toArray();
		}
		$this->_data = $this->getTree(0);
	}

	/**
	 * Prepare
	 */
	public function getTree($parent = 0) {
		$data = null;
		$translator = ServiceLocator::getServiceManager('translator');
		if (is_array($this->_query) && count($this->_query) > 0) {
			foreach ($this->_query as $dbdata) {
				if ($dbdata['parent'] == $parent) {
					$rawdata = $this->getTree($dbdata['root']);
					if (is_array($rawdata) && count($rawdata) > 0) {
						$dbdata['pages'] = $rawdata;
					}
					$structure = array();
					if (!empty($dbdata['label'])) {
						$structure['label'] = $translator->translate('text_navigator_' . strtolower($dbdata['label']));
						$structure['id'] = strtolower($dbdata['label']);
					}
					if (!empty($dbdata['route'])) {
						$structure['route'] = $dbdata['route'];
					}
					if (!empty($dbdata['action'])) {
						$structure['action'] = $dbdata['action'];
					}
					if (!empty($dbdata['controller'])) {
						$structure['controller'] = $dbdata['controller'];
					}
					if (!empty($dbdata['uri'])) {
						$structure['uri'] = $dbdata['uri'];
					}
					if (!empty($dbdata['class'])) {
						$structure['class'] = $dbdata['class'];
					}
					if (!empty($dbdata['order'])) {
						$structure['order'] = $dbdata['order'];
					}
					if (!empty($dbdata['visible'])) {
						$structure['visible'] = ($dbdata['visible'] == 1 ? True : False);
					}
					if (!empty($dbdata['pages'])) {
						$structure['pages'] = $dbdata['pages'];
					}
					$data[] = $structure;
				}
			}
		}
		return $data;
	}

	/**
	 * Data List
	 */
	public function getData() {
		return $this->_data;
	}
}
