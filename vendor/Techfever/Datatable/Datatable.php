<?php
namespace Techfever\Datatable;

use Techfever\Exception;

class Datatable extends Column {

	/**
	 * @var Options
	 */
	protected $options = array(
			'request' => null,
			'controller' => null,
			'route' => null,
			'action' => null,
			'variable' => null,
	);

	/**
	 * @var Data
	 **/
	private $_datatable_id = 0;

	/**
	 * Constructor
	 */
	public function __construct($options = null) {
		if (!is_array($options)) {
			throw new Exception\RuntimeException('Options has not been set or configured.');
		}
		$options = array_merge($this->options, $options);
		$this->setServiceLocator($options['servicelocator']);
		$this->setOptions($options);

		$options['datatable'] = $this->getDatatableID();
		$this->setOptions($options);

		parent::__construct($options);
		unset($this->options['servicelocator']);
	}

	/**
	 * Get Datatable Data
	 */
	private function getDatatableID() {
		if ($this->_datatable_id < 1) {
			$cachename = strtolower(str_replace('\\', '_', ($this->getController() . '\\' . $this->getRouteAction())));

			$QDatatable = $this->getDatabase();
			$QDatatable->select();
			$QDatatable->columns(array(
							'controller_id' => 'module_controllers_id'
					));
			$QDatatable->from(array(
							'mc' => 'module_controllers'
					));
			$QDatatable->join(array(
							'dt' => 'datatable'
					), 'dt.module_controllers_id  = mc.module_controllers_id', array(
							'id' => 'datatable_id'
					));
			$QDatatable->where(array(
							'dt.module_controllers_action = "' . $this->getRouteAction() . '"',
							'mc.module_controllers_alias = "' . str_replace('\\', '\\\\', $this->getController()) . '"',
					));
			$QDatatable->limit(1);
			$QDatatable->setCacheName('datatable_' . $cachename);
			$QDatatable->execute();
			if ($QDatatable->hasResult()) {
				$result = $QDatatable->current();
				$this->_datatable_id = $result['id'];
			}
		}
		return $this->_datatable_id;
	}
}
