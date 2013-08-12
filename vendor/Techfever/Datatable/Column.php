<?php

namespace Techfever\Datatable;

use Techfever\Exception;

class Column extends Search {

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
	 *
	 * @var Data
	 *
	 */
	private $_column_data = array();

	/**
	 * Constructor
	 */
	public function __construct($options = null) {
		if (!is_array($options)) {
			throw new Exception\RuntimeException('Options has not been set or configured.');
		}
		$options = array_merge($this->options, $options);
		$this->setServiceLocator($options['servicelocator']);
		parent::__construct($options);
		unset($options['servicelocator']);
		$this->setOptions($options);
	}

	/**
	 * Get
	 */
	public function getColumnData() {
		if (!is_array($this->_column_data) || count($this->_column_data) < 1) {
			$cachename = strtolower(str_replace('\\', '_', ($this->getController() . '\\' . $this->getRouteAction())));

			$DBColumn = $this->getDatabase();
			$DBColumn->select();
			$DBColumn->columns(array(
							'vid' => 'datatable_column_to_datatable_id',
							'primary' => 'datatable_column_to_datatable_primary',
							'default' => 'datatable_column_to_datatable_default'
					));
			$DBColumn->from(array(
							'dfv' => 'datatable_column_to_datatable'
					));
			$DBColumn
					->join(array(
									'df' => 'datatable_column'
							), 'df.datatable_column_id  = dfv.datatable_column_id',
							array(
									'id' => 'datatable_column_id',
									'key' => 'datatable_column_key',
									'table' => 'datatable_column_table',
									'field' => 'datatable_column_field',
									'column' => 'datatable_column_column'
							));
			$DBColumn->where(array(
							'dfv.datatable_column_to_datatable_status = 1',
							'dfv.datatable_id = ' . $this->getOption('datatable')
					));
			$DBColumn->order(array(
							'dfv.datatable_column_to_datatable_sort_order ASC, df.datatable_column_key ASC'
					));
			$DBColumn->setCacheName('datatable_column_' . $cachename);
			$DBColumn->execute();
			if ($DBColumn->hasResult()) {
				$data = array();
				while ($DBColumn->valid()) {
					$rawdata = $DBColumn->current();
					$data[$rawdata['id']] = $rawdata;
					$DBColumn->next();
				}
				$this->_column_data = $data;
			}
		}
		return $this->_column_data;
	}

	/**
	 * Get
	 */
	public function getColumnFieldByColumn($key) {
		$data = $this->getColumnData();
		if (is_array($data) && count($data) > 0) {
			if (!empty($key)) {
				foreach ($data as $value) {
					if ($value['column'] == $key) {
						return $value['field'];
					}
				}
			}
		}
		return false;
	}

	/**
	 * Get
	 */
	public function getColumnTableByColumn($key) {
		$data = $this->getColumnData();
		if (is_array($data) && count($data) > 0) {
			if (!empty($key)) {
				foreach ($data as $value) {
					if ($value['column'] == $key) {
						return $value['table'];
					}
				}
			}
		}
		return false;
	}

	/**
	 * Get
	 */
	public function getColumn($id = null) {
		$data = $this->getColumnData();
		if (is_array($data) && count($data) > 0) {
			if (!empty($id)) {
				return (array_key_exists($id, $data) ? $data[$id] : null);
			}
		}
		return false;
	}
}
