<?php

namespace Member\Activation\Listing\Controller;

use Techfever\Template\Plugin\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Techfever\Datatable\Datatable;
use Techfever\Parameter\Parameter;
use Techfever\Nationality\Nationality;
use Techfever\User\Rank;

class ActionController extends AbstractActionController {
	
	/**
	 *
	 * @var Rank Group
	 *     
	 */
	protected $rankgroup = 10000;
	
	/**
	 *
	 * @var Type
	 *
	 */
	protected $type = 'member';
	
	/**
	 *
	 * @var Module
	 *
	 */
	protected $module = 'activation_listing';
	
	/**
	 *
	 * @var Datatable
	 *
	 */
	protected $datatable = null;
	
	/**
	 *
	 * @var Variables
	 *
	 */
	protected $variable = null;
	
	/**
	 * Index Action
	 *
	 * @return ViewModel
	 */
	public function IndexAction() {
		$this->addJavascript ( 'vendor/Techfever/Javascript/datatable/jquery.jtable.js' );
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/Js/datatable.menu.js", array (
				'datatableformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Index', '/' ),
				'datatableformuri' => $this->url ()->fromRoute ( $this->getMatchedRouteName () ),
				'datatableid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Datatable', '/' ),
				'datacolumn' => $this->getDatatable ()->getColumnData () 
		) );
		
		return array (
				'menumodel' => $this->getDatatable () 
		);
	}
	
	/**
	 * Get Action
	 *
	 * @return Json Response
	 */
	public function GetAction() {
		$this->layout ( 'blank/layout' );
		$Request = $this->getRequest ();
		$Response = $this->getResponse ();
		
		$PlacementParameter = new Parameter ( array (
				'key' => 'user_hierarchy_placement_position',
				'servicelocator' => $this->getServiceLocator () 
		) );
		
		$StatusParameter = new Parameter ( array (
				'key' => 'user_access_status',
				'servicelocator' => $this->getServiceLocator () 
		) );
		
		$DesignationParameter = new Parameter ( array (
				'key' => 'user_profile_designation',
				'servicelocator' => $this->getServiceLocator () 
		) );
		
		$GenderParameter = new Parameter ( array (
				'key' => 'user_profile_gender',
				'servicelocator' => $this->getServiceLocator () 
		) );
		
		if ($Request->isXmlHttpRequest ()) {
			$index = 0;
			$perpage = 10;
			$order = 'ua.user_access_id ASC';
			
			$routequery = ( string ) $_SERVER ['QUERY_STRING'];
			// $routequery = ( string ) $this->params ()->fromRoute ( 'query', null );
			$routequery = (substr ( $routequery, 0, 1 ) == '/' ? substr ( $routequery, 1 ) : $routequery);
			$routequery = explode ( '&', $routequery );
			if (is_array ( $routequery ) && count ( $routequery ) > 0) {
				foreach ( $routequery as $routequeryvalue ) {
					$routequeryraw = explode ( '=', $routequeryvalue );
					if ($routequeryraw [0] === "jtStartIndex") {
						$index = $routequeryraw [1];
					} elseif ($routequeryraw [0] === "jtPageSize") {
						$perpage = $routequeryraw [1];
					} elseif ($routequeryraw [0] === "jtSorting") {
						$order = $routequeryraw [1];
					}
				}
			}
			if (strpos ( $order, ',' ) !== false) {
				$order = explode ( ',', $order );
			} else {
				$order = array (
						$order 
				);
			}
			if (is_array ( $order ) && count ( $order ) > 0) {
				$orderraw = array ();
				foreach ( $order as $ordervalue ) {
					$arrange = explode ( ' ', $ordervalue );
					$column = $this->getDatatable ()->getColumnFieldByColumn ( $arrange [0] );
					$table = $this->getDatatable ()->getColumnTableByColumn ( $arrange [0] );
					if ($table == 'user_access') {
						$orderraw [] = 'ua.' . $column . ' ' . $arrange [1];
					} elseif ($table == 'user_profile') {
						$orderraw [] = 'up.' . $column . ' ' . $arrange [1];
					} elseif ($table == 'user_hierarchy') {
						$orderraw [] = 'uh.' . $column . ' ' . $arrange [1];
					}
				}
				$order = $orderraw;
			}
			
			$search = $Request->getPost ()->toArray ();
			if (is_array ( $search ) && count ( $search ) > 0) {
				$searchraw = array ();
				foreach ( $search as $searchkey => $searchvalue ) {
					$is_date = false;
					if (is_array ( $searchvalue )) {
						if (array_key_exists ( 'day', $searchvalue )) {
							$date_day = $searchvalue ['day'];
							if (! empty ( $date_day )) {
								$date_day = $searchvalue ['day'];
							}
						}
						if (array_key_exists ( 'month', $searchvalue )) {
							$date_month = $searchvalue ['month'];
							if (! empty ( $date_month )) {
								$date_month = $searchvalue ['month'] . "-";
							}
						}
						if (array_key_exists ( 'year', $searchvalue )) {
							$date_year = $searchvalue ['year'];
							if (! empty ( $date_year )) {
								$date_year = $searchvalue ['year'] . "-";
							}
						}
						$searchvalue = $date_year . $date_month . $date_day;
						if (! empty ( $searchvalue )) {
							$is_date = true;
						}
					}
					$column = $this->getDatatable ()->getSearchFieldByColumn ( $searchkey );
					$table = $this->getDatatable ()->getSearchTableByColumn ( $searchkey );
					$pattern = $this->getDatatable ()->getSearchPatternByColumn ( $searchkey );
					$tablealias = "";
					if ($table == 'user_access') {
						if ($column == 'user_access_status') {
							if ($StatusParameter->hasResult ()) {
								$searchvalue = $StatusParameter->getValueByKey ( $searchvalue );
							}
						}
						$tablealias = "ua";
					} elseif ($table == 'user_profile') {
						if ($column == 'user_profile_designation_text') {
							if ($DesignationParameter->hasResult ()) {
								$searchvalue = $DesignationParameter->getValueByKey ( $searchvalue );
							}
						} else if ($column == 'user_profile_gender_text') {
							if ($GenderParameter->hasResult ()) {
								$searchvalue = $GenderParameter->getValueByKey ( $searchvalue );
							}
						}
						$tablealias = "up";
					} elseif ($table == 'user_hierarchy') {
						if ($column == 'user_hierarchy_placement_position_text') {
							if ($PlacementParameter->hasResult ()) {
								$searchvalue = $PlacementParameter->getValueByKey ( $searchvalue );
							}
						}
						$tablealias = "uh";
					}
					if (! empty ( $searchvalue ) && ! empty ( $tablealias ) && ! empty ( $column )) {
						if ($is_date) {
							$searchraw [$table] [] = 'date(' . $tablealias . '.' . $column . ') ' . sprintf ( $pattern, $searchvalue );
						} else {
							$searchraw [$table] [] = $tablealias . '.' . $column . ' ' . sprintf ( $pattern, $searchvalue );
						}
					}
				}
				$search = $searchraw;
			} else {
				$search = null;
			}
			
			if (! $this->isAdminUser ()) {
				$search ['user_hierarchy'] [] = 'uh.user_hierarchy_sponsor_username = "' . $this->getUsername () . '"';
			}
			$search ['user_access'] [] = 'ua.user_access_activated_date = "0000-00-00 00:00:00"';
			$user_status = 1;
			if (defined ( "USER_REGISTER_ACTIVATION_STATUS_" . $this->rankgroup . "_LOGIN" )) {
				$user_status = constant ( "USER_REGISTER_ACTIVATION_STATUS_" . $this->rankgroup . "_LOGIN" );
				if ($user_status == "0") {
					$search ['user_access'] [] = 'ua.user_access_status = "0"';
				}
			}
			$user_status = 1;
			if (defined ( "USER_REGISTER_ACTIVATION_STATUS_" . $this->rankgroup . "_VISITOR" )) {
				$user_status = constant ( "USER_REGISTER_ACTIVATION_STATUS_" . $this->rankgroup . "_VISITOR" );
				if ($user_status == "0") {
					$search ['user_access'] [] = 'ua.user_access_status = "0"';
				}
			}
			
			$listdataraw = $this->getUserManagement ()->getListing ( $this->rankgroup, $search, $order, $index, $perpage, true );
			$jsonData = array ();
			$jsonData ['Result'] = "OK";
			$jsonData ['Records'] = $listdataraw;
			$jsonData ['TotalRecordCount'] = $this->getUserManagement ()->getListingTotal ( $this->rankgroup, $search, true );
			$Response->setContent ( Json::encode ( $jsonData ) );
		}
		return $Response;
	}
	
	/**
	 * Manage Action
	 *
	 * @return Json Response
	 */
	public function ManageAction() {
		$this->layout ( 'blank/layout' );
		$Request = $this->getRequest ();
		$Response = $this->getResponse ();
		
		if ($Request->isXmlHttpRequest ()) {
			$id = ( string ) $this->params ()->fromRoute ( 'query', null );
			$legend = array ();
			$legend ['status'] = $this->HrefLink ( array (
					'value' => $this->Img ( array (
							'folder' => 'Icons',
							'image' => 'status-24x24.png' 
					) ) . '<span>' . $this->getTranslate ( 'text_user_activation_approve_action' ) . '</span>',
					'route' => ucfirst ( $this->type ) . '/Activation/Approve',
					'params' => array (
							'action' => 'Update',
							'crypt' => $id 
					) 
			) );
			$managedata [] = array (
					'legend' => '<div id="listlegend">' . implode ( '</div><div id="listlegend">', $legend ) . '</div>' 
			);
			
			$jsonData = array ();
			$jsonData ['Result'] = "OK";
			$jsonData ['Records'] = $managedata;
			$jsonData ['TotalRecordCount'] = count ( $managedata );
			$Response->setContent ( Json::encode ( $jsonData ) );
		}
		return $Response;
	}
	
	/**
	 * Get Variable
	 *
	 * @return array
	 */
	private function getVariables() {
		if (! is_array ( $this->variable ) && empty ( $this->variable )) {
			$Rank = new Rank ( array (
					'group' => $this->rankgroup,
					'servicelocator' => $this->getServiceLocator () 
			) );
			$rank = $Rank->rankToForm ();
			$Nationality = new Nationality ( array (
					'servicelocator' => $this->getServiceLocator () 
			) );
			$nationality_country = $Nationality->countryToForm ();
			$this->variable = array (
					'user_rank_id' => $rank,
					'user_profile_nationality' => $nationality_country 
			);
		}
		return $this->variable;
	}
	
	/**
	 * Get Datatable
	 *
	 * @return datatable
	 */
	private function getDatatable() {
		if (! is_object ( $this->datatable )) {
			$options = array (
					'servicelocator' => $this->getServiceLocator (),
					'action' => 'Index',
					'variable' => $this->getVariables () 
			);
			$this->datatable = new Datatable ( $options );
		}
		return $this->datatable;
	}
}
