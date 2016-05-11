<?php

namespace Bonus\Execute\Controller;

use Techfever\Template\Plugin\AbstractActionController;
use Techfever\Bonus\Bonus as UserBonus;

class ActionController extends AbstractActionController {
	/**
	 *
	 * @var Bonus Object
	 *     
	 */
	private $_bonus_object = null;
	
	/**
	 * Index Action
	 *
	 * @return ViewModel
	 */
	public function IndexAction() {
		return $this->redirect ()->toRoute ( $this->getMatchedRouteName (), array (
				'action' => 'Start' 
		) );
	}
	public function StartAction() {
		$year = ( string ) $this->params ()->fromRoute ( 'year', null );
		$month = ( string ) $this->params ()->fromRoute ( 'month', null );
		$day = ( string ) $this->params ()->fromRoute ( 'day', null );
		$date = $year . '-' . $month . '-' . $day;
		
		$QUser = $this->getDatabase ();
		$QUser->select ();
		$QUser->columns ( array (
				'user' => 'user_access_id' 
		) );
		$QUser->from ( array (
				'ua' => 'user_access' 
		) );
		$QUser->where ( array (
				'DATE(ua.user_access_activated_date) = "' . $date . '"' 
		) );
		$QUser->order ( array (
				'ua.user_access_id ASC' 
		) );
		$QUser->execute ();
		if ($QUser->hasResult ()) {
			while ( $QUser->valid () ) {
				$rawdata = $QUser->current ();
				$id = $rawdata ['user'];
				$this->getBonus ( $date, $id )->calculateBonus ();
				$this->_bonus_object = null;
				$QUser->next ();
			}
		} else {
			$this->getBonus ( $date, 1 )->generatePairingLine ();
		}
		$this->getBonus ( $date, 1 )->startCredit ();
		die ();
	}
	public function getBonus($date = null, $id = null) {
		if (! is_object ( $this->_bonus_object ) && ! empty ( $id ) && ! empty ( $date )) {
			$options = array (
					'servicelocator' => $this->getServiceLocator (),
					'user_access_id' => $id,
					'execute_date' => $date 
			);
			$this->_bonus_object = new UserBonus ( $options );
		}
		return $this->_bonus_object;
	}
}
