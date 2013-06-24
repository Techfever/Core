<?php
namespace Kernel\Template\Module;

use Kernel\Exception;
use Kernel\Database;

class Router implements ModuleInterface {

	/**
	 * @var Structure
	 **/
	private $_structure = array(
		'routes' => array()
	);

	/**
	 * @var Config
	 **/
	private $_config = array();

	/**
	 * @var Default Config
	 **/
	private $_defaultconfig = array();

	/**
	 * @var Query
	 **/
	private $_query = array();

	/**
	 * @var Constraints
	 **/
	private $_constraints = array();

	/**
	 * Constructor
	 *
	 * @param  null|array $config
	 **/
	public function __construct($config = null) {
		$this->_defaultconfig = $config['router'];
		$DBRoutes = new Database('select');
		$DBRoutes->columns(array(
					'root' => 'system_routes_id', 'uri' => 'system_routes_uri', 'route' => 'system_routes_route', 'type' => 'system_routes_type', 'controller' => 'system_routes_controller', 'action' => 'system_routes_action', 'parent' => 'system_routes_parent'
				));
		$DBRoutes->from(array(
					'sr' => 'system_routes'
				));
		$DBRoutes->where(array(
					'sr.system_routes_status = 1',
				));
		$DBRoutes->order(array(
					'system_routes_parent ASC', 'system_routes_order ASC', 'system_routes_uri ASC'
				));
		$DBRoutes->setCacheName('system_routes');
		$DBRoutes->execute();
		if ($DBRoutes->hasResult()) {
			$this->_query = $DBRoutes->toArray();
		}
		$DbConstraints = new Database('select');
		$DbConstraints->columns(array(
					'id' => 'system_routes_id', 'name' => 'system_routes_constraints_name', 'regex' => 'system_routes_constraints_regex'
				));
		$DbConstraints->from(array(
					'src' => 'system_routes_constraints'
				));
		$DBRoutes->order(array(
					'system_routes_constraints_order ASC'
				));
		$DbConstraints->setCacheName('system_routes_constraints');
		$DbConstraints->execute();
		if ($DbConstraints->hasResult()) {
			$this->_constraints = $DbConstraints->toArray();
		}
		$data = $this->getTree();
		//print_r($data);
		$this->_config = array(
			'routes' => $data
		);
	}

	/**
	 * Prepare
	 */
	public function getTree($parent = 0) {
		$data = null;
		if (is_array($this->_query) && count($this->_query) > 0) {
			foreach ($this->_query as $dbdata) {
				if ($dbdata['parent'] == $parent) {
					$rawdata = $this->getTree($dbdata['root']);
					if (is_array($rawdata) && count($rawdata) > 0) {
						$dbdata['may_terminate'] = true;
						$dbdata['child_routes'] = $rawdata;
					}

					$structure = array();
					if (!empty($dbdata['route'])) {
						$structure['route'] = $dbdata['route'];
					}
					if (!empty($dbdata['type'])) {
						$structure['type'] = $dbdata['type'];
					}
					if (!empty($dbdata['controller'])) {
						$structure['controller'] = $dbdata['controller'];
					}
					if (!empty($dbdata['action'])) {
						$structure['action'] = $dbdata['action'];
					}
					if (is_array($this->_constraints) && count($this->_constraints) > 0) {
						foreach ($this->_constraints as $dbconstraints) {
							if ($dbconstraints['id'] == $dbdata['root']) {
								$dbdata['constraints'][$dbconstraints['name']] = $dbconstraints['regex'];
								$structure['route'] .= '[/][:' . $dbconstraints['name'] . ']';
							}
						}
					}
					if (!empty($dbdata['constraints']) && is_array($dbdata['constraints'])) {
						$structure['constraints'] = $dbdata['constraints'];
					}
					if (!empty($dbdata['child_routes']) && is_array($dbdata['child_routes'])) {
						$structure['child_routes'] = $dbdata['child_routes'];
					}
					$data[$dbdata['uri']] = array(
						'type' => $structure['type'], 'options' => array()
					);
					$data[$dbdata['uri']]['options'] = array(
						'route' => $structure['route']
					);
					if (array_key_exists('constraints', $structure) && is_array($structure['constraints'])) {
						$data[$dbdata['uri']]['options']['constraints'] = $structure['constraints'];
					}
					$data[$dbdata['uri']]['options']['defaults'] = array(
						'controller' => $structure['controller'], 'action' => $structure['action']
					);
					if (array_key_exists('child_routes', $structure) && is_array($structure['child_routes'])) {
						$data[$dbdata['uri']]['may_terminate'] = true;
						$data[$dbdata['uri']]['child_routes'] = $structure['child_routes'];
					}
				}
			}
		}
		return $data;
	}

	/**
	 * Get Default Config
	 *
	 * @return array
	 */
	public function getDefaultConfig() {
		return $this->_defaultconfig;
	}

	/**
	 * Get Config
	 *
	 * @return array
	 */
	public function getConfig() {
		return $this->_config;
	}

	/**
	 * Get Structure
	 *
	 * @return array
	 */
	public function getStructure() {
		return $this->_structure;
	}
}
