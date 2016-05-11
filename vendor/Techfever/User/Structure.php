<?php

namespace Techfever\User;

use Techfever\Exception;
use Techfever\Parameter\Parameter;
use Techfever\Functions\General as GeneralBase;

class Structure extends GeneralBase {
	/**
	 *
	 * @var Structure Own Data
	 *     
	 */
	private $_structure_own_data = array ();
	/**
	 *
	 * @var Structure Data
	 *     
	 */
	private $_structure_data = array ();
	/**
	 *
	 * @var Structure Report Data
	 *     
	 */
	private $_structure_report_data = array ();
	/**
	 *
	 * @var Structure Position Data
	 *     
	 */
	private $_structure_position_data = array ();
	
	/**
	 * Option
	 *
	 * @var option
	 *
	 */
	protected $options = array (
			'user' => 0,
			'level' => 6,
			'type' => 'sponsor',
			'sponsor_id' => 0,
			'placement_id' => 0 
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
		$this->setOption ( 'type', strtolower ( $this->getOption ( 'type' ) ) );
	}
	public function getStructure($key = null) {
		if (! is_array ( $this->_structure_own_data ) || count ( $this->_structure_own_data ) < 1) {
			$QHierarchy = $this->getDatabase ();
			$QHierarchy->select ();
			$QHierarchy->columns ( array (
					'*' 
			) );
			$QHierarchy->from ( array (
					'uh' => 'user_hierarchy' 
			) );
			$QHierarchy->where ( array (
					'uh.user_access_id' => $this->getOption ( 'user' ) 
			) );
			$QHierarchy->execute ();
			if ($QHierarchy->hasResult ()) {
				$this->_structure_own_data = $QHierarchy->current ();
			}
		}
		if (! empty ( $key )) {
			if (array_key_exists ( $key, $this->_structure_own_data )) {
				return $this->_structure_own_data [$key];
			}
			return null;
		}
		return $this->_structure_own_data;
	}
	
	/**
	 * Sponsor Path
	 */
	public function getSponsorPath() {
		$path = $this->getStructure ( 'user_hierarchy_sponsor' );
		return $path;
	}
	
	/**
	 * Placement Path
	 */
	public function getPlacementPath() {
		$path = $this->getStructure ( 'user_hierarchy_placement' );
		return $path;
	}
	
	/**
	 * Sponsor ID
	 */
	public function getSponsorID() {
		$sponsor_id = $this->getOption ( 'sponsor_id' );
		if (! is_numeric ( $sponsor_id ) || $sponsor_id < 1) {
			$sponsor_username = $this->getSponsorUsername ();
			$sponsor_id = $this->getUserManagement ()->getID ( $sponsor_username );
		}
		return $sponsor_id;
	}
	
	/**
	 * Placement ID
	 */
	public function getPlacementID() {
		$placement_id = $this->getOption ( 'placement_id' );
		if (! is_numeric ( $placement_id ) || $placement_id < 1) {
			$placement_username = $this->getPlacementUsername ();
			$placement_id = $this->getUserManagement ()->getID ( $placement_username );
		}
		return $placement_id;
	}
	
	/**
	 * Sponsor Username
	 */
	public function getSponsorUsername() {
		$username = $this->getStructure ( 'user_hierarchy_sponsor_username' );
		return $username;
	}
	
	/**
	 * Placement Username
	 */
	public function getPlacementUsername() {
		$username = $this->getStructure ( 'user_hierarchy_placement_username' );
		return $username;
	}
	
	/**
	 * Prepare
	 */
	public function getStructureData() {
		if (! is_array ( $this->_structure_data ) || count ( $this->_structure_data ) < 1) {
			$id = $this->getOption ( 'user' );
			$id = (! empty ( $id ) && $id > 0 ? $id : ($this->getUserAccess ()->isAdminUser () ? 1 : $this->getUserAccess ()->getID ()));
			$this->_structure_data = array (
					$this->getStructureTree ( $id ) 
			);
		}
		return $this->_structure_data;
	}
	
	/**
	 * Prepare
	 */
	public function getStructureReportData() {
		if (! is_array ( $this->_structure_report_data ) || count ( $this->_structure_report_data ) < 1) {
			$id = $this->getOption ( 'user' );
			$id = (! empty ( $id ) && $id > 0 ? $id : ($this->getUserAccess ()->isAdminUser () ? 1 : $this->getUserAccess ()->getID ()));
			
			$type = $this->getOption ( 'type' );
			$details = array ();
			$summary = array (
					'start_date' => '',
					'end_date' => '',
					'total_user' => '',
					'rank' => '' 
			);
			$count = 0;
			$rank = array ();
			if ($this->getUserManagement ()->verifyID ( $id )) {
				$rawdata = $this->getUserManagement ()->getData ( $id );
				$QHierarchy = $this->getDatabase ();
				$QHierarchy->select ();
				$QHierarchy->columns ( array (
						'*' 
				) );
				$QHierarchy->from ( array (
						'uh' => 'user_hierarchy' 
				) );
				$QHierarchy->join ( array (
						'ua' => 'user_access' 
				), 'ua.user_access_id = uh.user_access_id', array (
						'*' 
				) );
				$QHierarchy->where ( array (
						'uh.user_hierarchy_' . $type . ' like "' . $rawdata ['user_hierarchy_' . $type] . '|%"  and ( DATE(uh.user_hierarchy_created_date) >= "' . $this->getOption ( 'start_date' ) . '" and DATE(uh.user_hierarchy_created_date) <= "' . $this->getOption ( 'end_date' ) . '")' 
				) );
				$QHierarchy->order ( array (
						'user_hierarchy_' . $type . ' ASC',
						'user_hierarchy_created_date ASC' 
				) );
				;
				$QHierarchy->execute ();
				if ($QHierarchy->hasResult ()) {
					while ( $QHierarchy->valid () ) {
						$rawdata = $QHierarchy->current ();
						$position = substr ( $rawdata ['user_hierarchy_' . $type], - 1 );
						$rawdata ['user_hierarchy_' . $type . '_position'] = $position;
						
						if (! array_key_exists ( $rawdata ['user_rank_id'], $details )) {
							$details [$rawdata ['user_rank_id']] = array ();
						}
						$datetime = new \DateTime ( $rawdata ['user_access_created_date'] );
						$rawdata ['user_access_created_date_format'] = $datetime->format ( 'd-F-Y' );
						$rawdata ['user_access_created_date_format'] = $rawdata ['user_access_created_date'];
						
						$details [$rawdata ['user_rank_id']] [$rawdata ['user_access_id']] = $rawdata;
						$count ++;
						if (! array_key_exists ( $rawdata ['user_rank_id'], $rank )) {
							$rank_text = $this->getUserRank ()->getMessage ( $rawdata ['user_rank_id'] );
							$rank [$rawdata ['user_rank_id']] = array (
									'rank' => $rank_text,
									'total_user' => 0 
							);
						}
						$total_user = $rank [$rawdata ['user_rank_id']] ['total_user'];
						$rank [$rawdata ['user_rank_id']] ['total_user'] = $total_user + 1;
						$QHierarchy->next ();
					}
				}
			}
			
			$start_datetime = new \DateTime ( $this->getOption ( 'start_date' ) );
			$summary ['start_date'] = $start_datetime->format ( 'd-F-Y' );
			$end_datetime = new \DateTime ( $this->getOption ( 'end_date' ) );
			$summary ['end_date'] = $end_datetime->format ( 'd-F-Y' );
			$summary ['total_user'] = $count;
			$summary ['rank'] = $rank;
			$data = array (
					'summary' => $summary,
					'details' => $details 
			);
			$this->_structure_report_data = $data;
		}
		return $this->_structure_report_data;
	}
	
	/**
	 * Prepare
	 */
	public function getStructurePositionData() {
		$type = $this->getOption ( 'type' );
		if (! is_array ( $this->_structure_position_data ) || count ( $this->_structure_position_data ) < 1) {
			$Parameter = new Parameter ( array (
					'key' => 'user_hierarchy_' . $type . '_position',
					'servicelocator' => $this->getServiceLocator () 
			) );
			$this->_structure_position_data = $Parameter->getParameterData ();
		}
		return $this->_structure_position_data;
	}
	public function getStructureTree($parent = 0, $level = 1) {
		$data = array ();
		$type = $this->getOption ( 'type' );
		if ($this->getUserManagement ()->verifyID ( $parent )) {
			$rawdata = $this->getUserManagement ()->getData ( $parent );
			$data = $rawdata;
			$data ['downline'] = array ();
			$data ['downline_status'] = false;
			
			if ($type == 'placement') {
				$position = $this->getStructurePositionData ();
				if (is_array ( $position ) && count ( $position ) > 0) {
					foreach ( $position as $position_value ) {
						$position_place = $position_value ['value'];
						$data ['downline'] [$position_place] = array ();
					}
				}
			} elseif ($type == 'sponsor') {
				$data ['downline'] [0] = array ();
				// $data['downline'][1] = array();
			}
			
			$downline = $this->getStructureDownline ( $data ['user_access_username'] );
			if (is_array ( $downline ) && count ( $downline ) > 0) {
				$countdownline = 0;
				foreach ( $downline as $downline_value ) {
					$downlinedata = $this->getStructureTree ( $downline_value ['user_access_id'], ($level + 1) );
					if ($type == 'placement') {
						$data ['downline'] [$downline_value ['user_hierarchy_' . $type . '_position']] = $downlinedata;
					} else {
						$data ['downline'] [$countdownline] = $downlinedata;
					}
					$data ['downline_status'] = true;
					$countdownline ++;
				}
			}
		}
		return $data;
	}
	public function getStructureDownline($parent = null, $type = null) {
		$data = null;
		if (empty ( $type )) {
			$type = $this->getOption ( 'type' );
		}
		if (! empty ( $parent )) {
			$QHierarchy = $this->getDatabase ();
			$QHierarchy->select ();
			$QHierarchy->columns ( array (
					'*' 
			) );
			$QHierarchy->from ( array (
					'uh' => 'user_hierarchy' 
			) );
			$QHierarchy->where ( array (
					'uh.user_hierarchy_' . $type . '_username = "' . $parent . '"' 
			) );
			$QHierarchy->order ( array (
					'user_hierarchy_' . $type . ' ASC' 
			) );
			;
			$QHierarchy->execute ();
			if ($QHierarchy->hasResult ()) {
				$data = array ();
				$count = 1;
				while ( $QHierarchy->valid () ) {
					$rawdata = $QHierarchy->current ();
					$position = substr ( $rawdata ['user_hierarchy_' . $type], - 1 );
					$rawdata ['user_hierarchy_' . $type . '_position'] = $position;
					$data [] = $rawdata;
					$QHierarchy->next ();
				}
			}
		}
		return $data;
	}
	
	/**
	 * getStructureLinear
	 */
	public function getStructureLinear($data = null, $level = 1, $username = null, $structure_count = 1) {
		$structure = $this->getStructureData ();
		$downline_status = false;
		if ($level > 1) {
			$structure = null;
			$downline = (array_key_exists ( 'downline', $data ) ? $data ['downline'] : null);
			$downline_status = (array_key_exists ( 'downline_status', $data ) ? $data ['downline_status'] : null);
			if (is_array ( $downline ) && count ( $downline ) > 0) {
				$structure = $downline;
			}
		}
		$type = $this->getOption ( 'type' );
		$content = null;
		$space = '	';
		$tab = $space;
		$levelspace = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$tablevel = null;
		if (is_array ( $structure ) && count ( $structure ) > 0) {
			for($i = 1; $i < $level; $i ++) {
				$tab .= $space;
				$tablevel .= $levelspace;
			}
			if ($level == 1) {
				$content .= $tab . '<table cellpadding="0" cellspacing="0" border="1" class="structurelinear" id="' . ($downline_status ? $level : ($level - 1)) . (! empty ( $username ) ? '_' . $username : '') . '">' . "\n";
				$content .= $tab . '<thead>' . "\n";
				$content .= $tab . '	<tr>' . "\n";
				// $content .= $tab . ' <th id="no">'.$this->getTranslate('text_no').'</th>' . "\n";
				$content .= $tab . '		<th id="username">' . $this->getTranslate ( 'text_username' ) . '</th>' . "\n";
				$content .= $tab . '		<th id="level">' . $this->getTranslate ( 'text_level' ) . '</th>' . "\n";
				$content .= $tab . '		<th id="rank">' . $this->getTranslate ( 'text_rank' ) . '</th>' . "\n";
				$content .= $tab . '	</tr>' . "\n";
				$content .= $tab . '</thead>' . "\n";
				$content .= $tab . '<tbody>' . "\n";
			}
			$structure_count = 1;
			foreach ( $structure as $structure_key => $structure_value ) {
				$username = (array_key_exists ( 'user_access_username', $structure_value ) ? $structure_value ['user_access_username'] : '');
				$upline_username = (array_key_exists ( 'user_hierarchy_' . $type . '_username', $structure_value ) ? $structure_value ['user_hierarchy_' . $type . '_username'] : '');
				$rank = (array_key_exists ( 'user_rank_text', $structure_value ) ? $structure_value ['user_rank_text'] : '&nbsp;');
				$key = $level . '_' . (! empty ( $username ) ? $username : '') . '_' . (! empty ( $upline_username ) ? $upline_username : '');
				$downline = (array_key_exists ( 'downline', $structure_value ) ? $structure_value : null);
				$downline_status = false;
				$downline_total = 0;
				if (is_array ( $downline ) && count ( $downline ) > 0) {
					$downline_status = true;
					$downline_total = count ( $downline );
				}
				if (! empty ( $username )) {
					$content .= $tab . '	<tr id="' . $key . '">' . "\n";
					// $content .= $tab . ' <td id="no">' . $structure_count . '</td>' . "\n";
					$content .= $tab . '		<td id="username">' . $tablevel . '			' . (! empty ( $username ) ? $username : '&nbsp;') . '</td>' . "\n";
					$content .= $tab . '		<td id="level">' . $level . '</td>' . "\n";
					$content .= $tab . '		<td id="rank">' . (! empty ( $rank ) ? $rank : '&nbsp;') . '</td>' . "\n";
					$content .= $tab . '	</tr>' . "\n";
					$structure_count = $structure_count + count ( $downline );
					if ($downline_status) {
						$content .= $this->getStructureLinear ( $downline, ($level + 1), $username );
					}
				}
				$structure_count ++;
			}
			if ($level == 1) {
				$content .= $tab . '</tbody>' . "\n";
				$content .= $tab . '</table>' . "\n";
			}
		}
		return $content;
	}
	
	/**
	 * getStructureHierarchy
	 */
	public function getStructureHierarchy($data = null, $level = 1, $username = null) {
		$structure = $this->getStructureData ();
		$downline_status = false;
		if ($level > 1) {
			$structure = null;
			$downline = (array_key_exists ( 'downline', $data ) ? $data ['downline'] : null);
			$downline_status = (array_key_exists ( 'downline_status', $data ) ? $data ['downline_status'] : null);
			if (is_array ( $downline ) && count ( $downline ) > 0) {
				$structure = $downline;
			}
		}
		$type = $this->getOption ( 'type' );
		$content = null;
		$space = '	';
		$tab = $space;
		if (is_array ( $structure ) && count ( $structure ) > 0) {
			for($i = 1; $i <= $level; $i ++) {
				$tab .= $space;
			}
			$structure_count = 1;
			$content .= $tab . '<table cellpadding="0" cellspacing="0" border="0" class="structurehierarchy" id="' . ($downline_status ? $level : ($level - 1)) . (! empty ( $username ) ? '_' . $username : '') . '">' . "\n";
			foreach ( $structure as $structure_key => $structure_value ) {
				$username = (array_key_exists ( 'user_access_username', $structure_value ) ? $structure_value ['user_access_username'] : '');
				$upline_username = (array_key_exists ( 'user_hierarchy_' . $type . '_username', $structure_value ) ? $structure_value ['user_hierarchy_' . $type . '_username'] : '');
				$rank = (array_key_exists ( 'user_rank_text', $structure_value ) ? $structure_value ['user_rank_text'] : '&nbsp;');
				$key = $level . '_' . (! empty ( $username ) ? $username : '') . '_' . (! empty ( $upline_username ) ? $upline_username : '');
				$downline = (array_key_exists ( 'downline', $structure_value ) ? $structure_value : null);
				$current_downline_status = (array_key_exists ( 'downline_status', $structure_value ) ? $structure_value ['downline_status'] : false);
				$downline_status = false;
				$downline_total = 0;
				if (is_array ( $downline ) && count ( $downline ) > 0) {
					$downline_status = true;
					$downline_total = count ( $downline );
				}
				if ($level > 1 && $structure_count > 1 && count ( $structure ) > 1) {
					$content .= $tab . '	<tr id="' . $key . '">' . "\n";
					$content .= $tab . '		<td class="connector_hline"></td>' . "\n";
					$content .= $tab . '		<td class="connector_colspan" colspan="6">&nbsp;</td>' . "\n";
					$content .= $tab . '	</tr>' . "\n";
				}
				$content .= $tab . '	<tr id="' . $key . '">' . "\n";
				if ($level > 1) {
					$content .= $tab . '		<td class="' . ($structure_count == 1 ? "connector_space" : "connector_hline") . '"></td>' . "\n";
					$content .= $tab . '		<td class="connector_space">&nbsp;</td>' . "\n";
				}
				$content .= $tab . '		<td class="root" rowspan="3">' . "\n";
				$content .= $tab . '			<a ' . (! $downline_status ? ' id="disabled"' : ' id="enabled" onclick=\'$(this).HierarchyClick("' . $key . '", "' . ($current_downline_status ? 'True' : 'False') . '");\'') . '>' . "\n";
				$content .= $tab . '			<div id="details">' . "\n";
				$content .= $tab . '				<div id="username">';
				$content .= (! empty ( $username ) ? $username : '&nbsp;') . "\n";
				$content .= $tab . '				</div>' . "\n";
				$content .= $tab . '				<div id="rank">';
				$content .= (! empty ( $rank ) ? $rank : '&nbsp;') . "\n";
				$content .= $tab . '				</div>' . "\n";
				$content .= $tab . '			</div>' . "\n";
				$content .= $tab . '			</a>' . "\n";
				$content .= $tab . '		</td>' . "\n";
				$content .= $tab . '		<td class="connector_space">&nbsp;</td>' . "\n";
				$content .= $tab . '		<td class="child" rowspan="3">' . "\n";
				if ($downline_status) {
					$content .= $this->getStructureHierarchy ( $downline, ($level + 1), $username );
				}
				$content .= $tab . '		</td>' . "\n";
				$content .= $tab . '	</tr>' . "\n";
				$content .= $tab . '	<tr id="' . $key . '">' . "\n";
				if ($level > 1) {
					$content .= $tab . '		<td class="connector_vline"></td>' . "\n";
					$content .= $tab . '		<td class="connector_vline"></td>' . "\n";
				}
				$content .= $tab . '		<td class="connector_vline"></td>' . "\n";
				$content .= $tab . '	</tr>' . "\n";
				$content .= $tab . '	<tr id="' . $key . '">' . "\n";
				if ($level > 1) {
					$content .= $tab . '		<td class="' . (count ( $structure ) <= 1 ? '' : ($structure_count == 1 ? "connector_hline" : ($structure_count < count ( $structure ) ? "connector_hline" : "connector_hspace"))) . '"></td>' . "\n";
					$content .= $tab . '		<td class="connector_space">&nbsp;</td>' . "\n";
				}
				$content .= $tab . '		<td class="connector_space">&nbsp;</td>' . "\n";
				$content .= $tab . '	</tr>' . "\n";
				if ($level > 1 && $structure_count == 1 && count ( $structure ) > 1) {
					$content .= $tab . '	<tr id="' . $key . '">' . "\n";
					$content .= $tab . '		<td class="connector_hline"></td>' . "\n";
					$content .= $tab . '		<td class="connector_colspan" colspan="6">&nbsp;</td>' . "\n";
					$content .= $tab . '	</tr>' . "\n";
				}
				$structure_count ++;
			}
			$content .= $tab . '</table>' . "\n";
		}
		return $content;
	}
}
