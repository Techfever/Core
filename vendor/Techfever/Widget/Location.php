<?php

namespace Techfever\Widget;

use Techfever\Exception;

class Location extends Controller {
	
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
	 * @var location
	 */
	private $location = null;
	
	/**
	 *
	 * @var location_width
	 */
	private $location_width = null;
	
	/**
	 * locations
	 *
	 * @var mixed
	 */
	private $location_data = array (
			'left',
			'right',
			'header',
			'footer',
			'before',
			'after',
			'dashboard' 
	);
	
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
	 * Set location
	 *
	 * @param string $location        	
	 * @return Location
	 */
	public function setLocation($location) {
		$location = strtolower ( $location );
		$this->location = $location;
		return $this;
	}
	
	/**
	 * Get location
	 *
	 * @return string
	 */
	public function getLocation() {
		return $this->location;
	}
	
	/**
	 * Get location width
	 *
	 * @return string
	 */
	public function getLocationWidth() {
		$location = $this->getLocation ();
		switch ($location) {
			case 'left' :
				$this->location_width = '240';
				break;
			case 'right' :
				$this->location_width = '240';
				break;
			case 'header' :
				$this->location_width = '';
				break;
			case 'footer' :
				$this->location_width = '';
				break;
			case 'before' :
				$this->location_width = '';
				break;
			case 'after' :
				$this->location_width = '';
				break;
			case 'dashboard' :
				$this->location_width = '';
				break;
		}
		return $this->location_width;
	}
	
	/**
	 * Verify if location valid
	 *
	 * @param string $location        	
	 * @return bool
	 */
	public function verifyLocation($location) {
		$location = strtolower ( $location );
		if (in_array ( $location, $this->location_data )) {
			return true;
		}
		return false;
	}
}
?>