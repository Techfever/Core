<?php

namespace Techfever\Widget;

use Techfever\Exception;

class Widget extends Location {
	
	/**
	 * options
	 *
	 * @var mixed
	 */
	private $options = array (
			'controllerid' => '',
			'controllername' => '',
			'controlleraction' => 'Initial' 
	);
	
	/**
	 *
	 * @var widget
	 */
	private $widget = null;
	
	/**
	 *
	 * @var widget_data
	 */
	private $widget_data = null;
	
	/**
	 *
	 * @var widget_valid_data
	 */
	private $widget_valid_data = null;
	
	/**
	 *
	 * @var widget_location_valid_data
	 */
	private $widget_location_valid_data = null;
	
	/**
	 * Constructor
	 */
	public function __construct($options = null) {
		if (! is_array ( $options )) {
			throw new Exception\RuntimeException ( 'Options has not been set or configured.' );
		}
		if (! isset ( $options ['controllerid'] )) {
			throw new Exception\RuntimeException ( 'Controller ID has not been set or configured.' );
		}
		if (! isset ( $options ['controllername'] )) {
			throw new Exception\RuntimeException ( 'Controller Name has not been set or configured.' );
		}
		if (! isset ( $options ['controlleraction'] )) {
			throw new Exception\RuntimeException ( 'Controller Action has not been set or configured.' );
		}
		if (! isset ( $options ['servicelocator'] )) {
			throw new Exception\RuntimeException ( 'ServiceLocator has not been set or configured.' );
		}
		$options = array_merge ( $this->options, $options );
		parent::__construct ( $options );
		unset ( $options ['servicelocator'] );
		$this->setOptions ( $options );
	}
	
	/**
	 * Set widget
	 *
	 * @param string $widget        	
	 * @return Widget
	 */
	public function setWidget($widget) {
		$this->widget = $widget;
		return $this;
	}
	
	/**
	 * Get widget
	 *
	 * @return string
	 */
	public function getWidget() {
		return $this->widget;
	}
	
	/**
	 * Prepare widget
	 *
	 * @return array $widget
	 */
	public function prepareWidget() {
		$widget = false;
		$data = $this->getValidWidget ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			$dataRaw = $data [$this->getWidget ()];
			if (is_array ( $dataRaw ) && count ( $dataRaw ) > 0) {
				$widget = $dataRaw;
			}
		}
		return $widget;
	}
	
	/**
	 * Verify if widget valid
	 *
	 * @param string $widget        	
	 * @return bool
	 */
	public function verifyWidget($widget, $status = '3') {
		$data = $this->getWidgetData ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $widget_value ) {
				if ($widget_value ['key'] == $widget) {
					if ($status == "3") {
						return true;
					} elseif ($widget_value ['status'] == $status) {
						return true;
					}
				}
			}
		}
		return false;
	}
	
	/**
	 * Get Location Widget
	 *
	 * @return array $widget_data
	 */
	public function getWidgetData() {
		if (! is_array ( $this->widget_data ) || count ( $this->widget_data ) < 1) {
			$QWidget = $this->getDatabase ();
			$QWidget->select ();
			$QWidget->columns ( array (
					'id' => 'widget_to_module_controllers_id',
					'wid' => 'widget_controllers_id',
					'location' => 'widget_to_module_controllers_location',
					'status' => 'widget_to_module_controllers_status',
					'visitor' => 'widget_to_module_controllers_visitor',
					'sort' => 'widget_to_module_controllers_sort_order' 
			) );
			$QWidget->from ( array (
					'wtmc' => 'widget_to_module_controllers' 
			) );
			$QWidget->join ( array (
					'mc' => 'module_controllers' 
			), 'mc.module_controllers_id = wtmc.widget_controllers_id', array (
					'key' => 'module_controllers_key' 
			) );
			$QWidget->join ( array (
					'wmc' => 'module_controllers' 
			), 'wmc.module_controllers_id = wtmc.widget_controllers_id', array (
					'key' => 'module_controllers_key',
					'config' => 'module_controllers_config',
					'class' => 'module_controllers_class',
					'alias' => 'module_controllers_alias',
					'path' => 'module_controllers_path',
					'file' => 'module_controllers_file' 
			) );
			$QWidget->where ( array (
					'wtmc.module_controllers_id in ( "*", "' . $this->getControllerId () . '")' 
			) );
			$QWidget->order ( array (
					'wtmc.widget_to_module_controllers_sort_order ASC' 
			) );
			$QWidget->execute ();
			if ($QWidget->hasResult ()) {
				$data = array ();
				while ( $QWidget->valid () ) {
					$data = $QWidget->current ();
					$this->widget_data [$data ['id']] = $data;
					$QWidget->next ();
				}
			}
		}
		return $this->widget_data;
	}
	
	/**
	 * Get valid Widget
	 *
	 * @return array $widget_valid
	 */
	public function getValidWidget() {
		if (! is_array ( $this->widget_valid_data ) || count ( $this->widget_valid_data ) < 1) {
			$this->widget_valid_data = array ();
			$data = $this->getWidgetData ();
			if (is_array ( $data ) && count ( $data ) > 0) {
				foreach ( $data as $widget_value ) {
					if ($widget_value ['status'] == "1") {
						if ($widget_value ['visitor'] == "1") {
							$this->widget_valid_data [$widget_value ['wid']] = $widget_value;
						} else if ($widget_value ['visitor'] == "0" && $this->getUserAccess ()->isLogin ()) {
							$this->widget_valid_data [$widget_value ['wid']] = $widget_value;
						}
					}
				}
			}
		}
		return $this->widget_valid_data;
	}
	
	/**
	 * Get location valid Widget
	 *
	 * @return array $widget_valid
	 */
	public function getLocationValidWidget() {
		$location = $this->getLocation ();
		if (! is_array ( $this->widget_location_valid_data [$location] ) || array_key_exists ( $location, $this->widget_location_valid_data ) || count ( $this->widget_location_valid_data [$location] ) < 1) {
			$this->widget_location_valid_data [$location] = array ();
			$data = $this->getValidWidget ();
			if (is_array ( $data ) && count ( $data ) > 0) {
				foreach ( $data as $widget_value ) {
					if ($widget_value ['location'] == $location) {
						$this->widget_location_valid_data [$location] [$widget_value ['wid']] = $widget_value;
					}
				}
			}
		}
		return $this->widget_location_valid_data [$location];
	}
}
?>