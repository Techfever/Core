<?php

namespace Techfever\Bonus;

use Techfever\Exception;

class Level extends ROI {
	
	/**
	 * Option
	 *
	 * @var option
	 *
	 */
	protected $options = array (
			'user_access_id' => 0,
			'user_rank_id' => 0,
			'amount' => 0,
			'user_sponsor_id' => 0,
			'user_sponsor_username' => null,
			'user_sponsor_rank_id' => 0,
			'user_placement_id' => 0,
			'user_placement_username' => null,
			'user_placement_rank_id' => 0,
			'execute_date' => null 
	);
	
	/**
	 *
	 * @var Bonus Level Data
	 *     
	 */
	private $user_level_data = null;
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
}
