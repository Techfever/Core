<?php

namespace Techfever\User;

use Techfever\Exception;
use Techfever\Parameter\Parameter;
use Techfever\Functions\General as GeneralBase;

class Structure extends GeneralBase {
	/**
	 *
	 * @var Structure Data
	 *     
	 */
	private $_structure_data = array ();
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
			'type' => 'sponsor' 
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
	public function getStructureDownline($parent = null) {
		$data = null;
		$type = $this->getOption ( 'type' );
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
			$QHierarchy->setCacheName ( 'user_hierarchy_' . $parent );
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
					$content .= $tab . '		<td class="' . (count ( $structure ) <= 1 ? '' : ($structure_count == 1 ? "connector_hline" : "connector_hspace")) . '"></td>' . "\n";
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
