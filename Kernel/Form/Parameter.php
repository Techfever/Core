<?php
namespace Kernel\Form;

use Kernel\Database;
use Kernel\Exception;

class Parameter {

	/**
	 * @var Data
	 **/
	private $_data = array();

	/**
	 * Constructor
	 *
	 * @return	void
	 **/
	public function __construct() {
	}

	public function prepare($key) {
		if (!empty($key)) {
			$DBParameter = new Database('select');
			$DBParameter->columns(array(
							'id' => 'field_parameter_data_id',
							'key' => 'field_parameter_data_key',
							'value' => 'field_parameter_data_value',
							'status' => 'field_parameter_data_status',
							'order' => 'field_parameter_data_order'
					));
			$DBParameter->from(array(
							'pd' => 'field_parameter_data'
					));
			$DBParameter->where(array(
							'pd.field_parameter_data_status = 1',
							'pd.field_parameter_data_key like "' . $key . '%"',
					));
			$DBParameter->order(array(
							'field_parameter_data_order ASC, field_parameter_data_key ASC'
					));
			$DBParameter->setCacheName('field_parameter_data_' . $key);
			$DBParameter->execute();
			if ($DBParameter->hasResult()) {
				$this->_data = $DBParameter->toArray();
			}
		}
	}

	public function toForm() {
		$data = null;
		foreach ($this->_data as $data_value) {
			$data[$data_value['key']] = 'text_' . $data_value['key'];
		}
		return $data;
	}
}
