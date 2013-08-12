<?php
namespace Techfever\Form;

use Techfever\Exception;

class Filter extends Validator {
	/**
	 * @var Options
	 */
	protected $options = array();

	/**
	 * @var Variables
	 */
	protected $variables = array();

	/**
	 * @var Filter Data
	 **/
	private $filter_data = null;

	public function __construct($options = null) {
		if (!is_array($options)) {
			throw new Exception\RuntimeException('Options has not been set or configured.');
		}
		$options = array_merge($this->options, $options);
		$this->setServiceLocator($options['servicelocator']);
		$this->setOptions($options);
		parent::__construct($options);
		unset($this->options['servicelocator']);
	}

	/**
	 * Get Element ID
	 * 
	 * @return array id
	 **/
	public function getElementID() {
		return $this->getOption('element_id');
	}

	/**
	 * Get Filter Data
	 * 
	 * @return array data
	 **/
	public function getFiltersData() {
		if (!is_array($this->filter_data) || count($this->filter_data) < 1) {
			$element = $this->getElementID();
			if (is_array($element) && count($element) > 0) {
				$config = array();
				$QFilter = $this->getDatabase();
				$QFilter->select();
				$QFilter->columns(array(
								'id' => 'form_element_filters_id',
								'element' => 'form_element_id',
								'key' => 'form_element_filters_key',
						));
				$QFilter->from(array(
								'fe' => 'form_element_filters'
						));
				$QFilter->where(array(
								'fe.form_element_id in (' . implode(', ', $element) . ')'
						));
				$QFilter->order(array(
								'fe.form_element_filters_key ASC'
						));
				$QFilter->setCacheName('form_element_filters');
				$QFilter->execute();
				if ($QFilter->hasResult()) {
					while ($QFilter->valid()) {
						$config[] = $QFilter->current();
						$QFilter->next();
					}
				}
				$this->filter_data = $config;
			}
		}
		return $this->filter_data;
	}

	/**
	 * Get Filters by ID
	 * 
	 * @return array
	 **/
	public function getFiltersByID($id) {
		$data = $this->getFiltersData();
		$filters = array();
		if (is_array($data) && count($data) > 0) {
			foreach ($data as $value) {
				if ($id == $value['element']) {
					$filters[] = array(
							'name' => $value['key']
					);
				}
			}
		}
		return $filters;
	}
}
