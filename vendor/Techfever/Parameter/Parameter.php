<?php

namespace Techfever\Parameter;

use Techfever\Exception;
use Techfever\Functions\General as GeneralBase;

class Parameter extends GeneralBase {
	
	/**
	 *
	 * @var Data
	 *
	 */
	private $_parameter_data = array ();
	
	/**
	 *
	 * @var hasResult
	 *
	 */
	private $_hasResult = false;
	
	/**
	 *
	 * @var hasResult
	 *
	 */
	private $_test = false;
	
	/**
	 * Option
	 *
	 * @var option
	 *
	 */
	protected $options = array (
			'key' => 0,
			'id' => 0 
	);
	public function __construct($options = null) {
		if (! is_array ( $options )) {
			throw new Exception\RuntimeException ( 'Options has not been set or configured.' );
		}
		$options = array_merge ( $this->options, $options );
		$this->setServiceLocator ( $options ['servicelocator'] );
		parent::__construct ( $options );
		unset ( $options ['servicelocator'] );
		$this->setOptions ( $options );
	}
	public function getParameterData() {
		if (! is_array ( $this->_parameter_data ) || count ( $this->_parameter_data ) < 1) {
			if (strlen ( $this->getOption ( 'key' ) ) > 0 || $this->getOption ( 'id' ) > 0) {
				$DBParameter = $this->getDatabase ();
				$DBParameter->select ();
				$DBParameter->columns ( array (
						'id' => 'parameter_data_id',
						'key' => 'parameter_data_key',
						'value' => 'parameter_data_value',
						'status' => 'parameter_data_status',
						'order' => 'parameter_data_order' 
				) );
				$DBParameter->from ( array (
						'pd' => 'parameter_data' 
				) );
				$DBParameterWhere = array (
						'pd.parameter_data_status = 1' 
				);
				if (strlen ( $this->getOption ( 'key' ) ) > 0) {
					$DBParameterWhere [] = 'pd.parameter_data_key like "' . $this->getOption ( 'key' ) . '%"';
				}
				if ($this->getOption ( 'id' ) > 0) {
					$DBParameterWhere [] = 'ur.parameter_data_id = ' . $this->getOption ( 'id' );
				}
				$DBParameter->where ( $DBParameterWhere );
				$DBParameter->order ( array (
						'parameter_data_order ASC, parameter_data_key ASC' 
				) );
				$DBParameter->execute ();
				if ($DBParameter->hasResult ()) {
					$data = array ();
					while ( $DBParameter->valid () ) {
						$data = $DBParameter->current ();
						$data ['message'] = $this->getTranslate ( 'text_' . $data ['key'] );
						$this->_parameter_data [$data ['id']] = $data;
						$DBParameter->next ();
					}
				}
			}
		}
		return $this->_parameter_data;
	}
	
	/**
	 * Get Message
	 */
	public function getMessage($id = null) {
		$data = $this->getParameter ( $id );
		$key = $data ['key'];
		$name = "";
		if (strlen ( $key ) > 0) {
			$name = $this->getTranslate ( 'text_' . $key );
		}
		return $name;
	}
	
	/**
	 * Get PAramter
	 */
	public function getParameter($id = null) {
		$data = $this->getParameterData ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			if (! empty ( $id )) {
				return (array_key_exists ( $id, $data ) ? $data [$id] : null);
			}
		}
		return false;
	}
	public function toForm() {
		$data = $this->getParameterData ();
		$parameterData = null;
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $data_value ) {
				$parameterData [$data_value ['key']] = $this->getMessage ( $data_value ['id'] );
			}
		}
		return $parameterData;
	}
	public function getKeyByValue($value) {
		$data = $this->getParameterData ();
		$key = null;
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $data_value ) {
				if ($data_value ['value'] == $value) {
					$key = $data_value ['key'];
				}
			}
		}
		return $key;
	}
	public function isValidByValue($value) {
		$data = $this->getParameterData ();
		$status = false;
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $data_value ) {
				if ($data_value ['value'] == $value) {
					$status = true;
				}
			}
		}
		return $status;
	}
	public function getValueByKey($key) {
		$data = $this->getParameterData ();
		$value = null;
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $data_value ) {
				if ($data_value ['key'] == $key) {
					$value = $data_value ['value'];
				}
			}
		}
		return $value;
	}
	public function getMessageByValue($value) {
		$data = $this->getParameterData ();
		$name = null;
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $data_value ) {
				if ($data_value ['value'] == $value) {
					$name = $this->getMessage ( $data_value ['id'] );
				}
			}
		}
		return $name;
	}
	public function getMessageByKey($key) {
		$data = $this->getParameterData ();
		$name = null;
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $data_value ) {
				if ($data_value ['key'] == $key) {
					$name = $this->getMessage ( $data_value ['id'] );
				}
			}
		}
		return $name;
	}
	public function hasResult() {
		$data = $this->getParameterData ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			$this->_hasResult = true;
		}
		return $this->_hasResult;
	}
}
