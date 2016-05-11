<?php

namespace Wallet\Withdraw\Pending\Controller;

use Techfever\Template\Plugin\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Techfever\Datatable\Datatable;
use Techfever\View\View as WalletView;

class ActionController extends AbstractActionController {
	
	/**
	 *
	 * @var Type
	 *
	 */
	protected $type = 'wallet';
	
	/**
	 *
	 * @var Module
	 *
	 */
	protected $module = 'withdraw_pending';
	
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
		
		if ($Request->isXmlHttpRequest ()) {
			$index = 0;
			$perpage = 10;
			$order = 'uwh.user_wallet_history_created_date ASC';
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
					if ($table == 'user_wallet_history') {
						$orderraw [] = 'uwh.' . $column . ' ' . $arrange [1];
					} elseif ($table == 'user_access_to') {
						$orderraw [] = 'uat.' . $column . ' ' . $arrange [1];
					} elseif ($table == 'user_access_from') {
						$orderraw [] = 'uaf.' . $column . ' ' . $arrange [1];
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
					if ($table == 'user_wallet_history') {
						$tablealias = "uwh";
					} elseif ($table == 'user_access_to') {
						$tablealias = "uat";
					} elseif ($table == 'user_access_from') {
						$tablealias = "uaf";
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
				$search = array (
						'user_wallet_history' => array () 
				);
			}
			$search ['user_wallet_history'] [] = 'uwh.wallet_status_id = 1';
			if (! $this->isAdminUser ()) {
				$search ['user_wallet_history'] [] = 'uwh.user_access_id = ' . $this->getUserID ();
			}
			$listdataraw = $this->getUserWallet ()->getWithdrawHistoryListing ( $search, $order, $index, $perpage, true );
			$jsonData = array ();
			$jsonData ['Result'] = "OK";
			$jsonData ['Records'] = $listdataraw;
			$jsonData ['TotalRecordCount'] = $this->getUserWallet ()->getWithdrawHistoryListingTotal ( $search, true );
			$Response->setContent ( Json::encode ( $jsonData ) );
		}
		return $Response;
	}
	public function ApproveAction() {
		$this->layout ( 'blank/layout' );
		$Request = $this->getRequest ();
		$Response = $this->getResponse ();
		
		if ($Request->isXmlHttpRequest ()) {
			$crypt = ( string ) $this->params ()->fromRoute ( 'query', null );
			$id = $this->Decrypt ( $crypt );
			$title = $this->getTranslate ( 'text_wallet_approve_title' );
			$redirect = $this->url ()->fromRoute ( $this->getMatchedRouteName (), array (
					'action' => 'Index' 
			) );
			$messages = '';
			if ($this->getUserWallet ()->verifyHistoryID ( $id )) {
				$data = array ();
				$datetime = new \DateTime ();
				$data ['timestamp'] = $datetime->getTimestamp ();
				$data ['log_modified_by'] = ($this->getUserAccess ()->isLogin () ? $this->getUserAccess ()->getUsername () : 'Unknown');
				$data ['log_modified_date'] = $datetime->format ( 'Y-m-d H:i:s' );
				$rawdata = $this->getUserWallet ()->getHistoryData ( $id );
				$data = array_merge ( $data, $rawdata );
				if ($this->getUserWallet ()->updateHistoryStatus ( $id, 3, $data )) {
					$messages = $this->getTranslate ( 'text_success_wallet_approve_action' );
				} else {
					$messages = $this->getTranslate ( 'text_error_wallet_approve_action' );
				}
			}
			$Response->setContent ( Json::encode ( array (
					'redirect' => $redirect,
					'title' => $title,
					'messages' => $messages 
			) ) );
		}
		return $Response;
	}
	public function RejectAction() {
		$this->layout ( 'blank/layout' );
		$Request = $this->getRequest ();
		$Response = $this->getResponse ();
		
		if ($Request->isXmlHttpRequest ()) {
			$crypt = ( string ) $this->params ()->fromRoute ( 'query', null );
			$id = $this->Decrypt ( $crypt );
			$title = $this->getTranslate ( 'text_wallet_reject_title' );
			$redirect = $this->url ()->fromRoute ( $this->getMatchedRouteName (), array (
					'action' => 'Index' 
			) );
			$messages = '';
			if ($this->getUserWallet ()->verifyHistoryID ( $id )) {
				$data = array ();
				$datetime = new \DateTime ();
				$data ['timestamp'] = $datetime->getTimestamp ();
				$data ['log_modified_by'] = ($this->getUserAccess ()->isLogin () ? $this->getUserAccess ()->getUsername () : 'Unknown');
				$data ['log_modified_date'] = $datetime->format ( 'Y-m-d H:i:s' );
				$rawdata = $this->getUserWallet ()->getHistoryData ( $id );
				$data = array_merge ( $data, $rawdata );
				if ($this->getUserWallet ()->updateHistoryStatus ( $id, 2, $data )) {
					$messages = $this->getTranslate ( 'text_success_wallet_reject_action' );
				} else {
					$messages = $this->getTranslate ( 'text_error_wallet_reject_action' );
				}
			}
			$Response->setContent ( Json::encode ( array (
					'redirect' => $redirect,
					'title' => $title,
					'messages' => $messages 
			) ) );
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
			$crypt = ( string ) $this->params ()->fromRoute ( 'query', null );
			$id = $this->Decrypt ( $crypt );
			$legend = array ();
			$managedata = array ();
			if ($this->getUserWallet ()->verifyHistoryID ( $id )) {
				$data = $this->getUserWallet ()->getHistoryData ( $id );
				
				$legend ['details'] = $this->HrefLink ( array (
						'value' => $this->Img ( array (
								'folder' => 'Icons',
								'image' => 'details-24x24.png' 
						) ) . '<span>' . $this->getTranslate ( 'text_details' ) . '</span>',
						'attributes' => array (
								'href' => '#',
								'onclick' => '$(this).formWithdrawPreview({
												uri: "' . $this->url ()->fromRoute ( ucfirst ( str_replace ( '_', '/', $this->type ) ) . '/Pending', array (
										'action' => 'View',
										'query' => $crypt 
								) ) . '",  
												uriapprove: "' . $this->url ()->fromRoute ( ucfirst ( str_replace ( '_', '/', $this->type ) ) . '/Pending', array (
										'action' => 'Approve',
										'query' => $crypt 
								) ) . '",  
												urireject: "' . $this->url ()->fromRoute ( ucfirst ( str_replace ( '_', '/', $this->type ) ) . '/Pending', array (
										'action' => 'Reject',
										'query' => $crypt 
								) ) . '",  
												title: "' . $this->getTranslate ( "text_dialog_wallet_view_" . $this->module . "_title" ) . '",  
											});' 
						) 
				) );
				$managedata [] = array (
						'legend' => '<div id="listlegend">' . implode ( '</div><div id="listlegend">', $legend ) . '</div>' 
				);
			}
			
			$jsonData = array ();
			$jsonData ['Result'] = "OK";
			$jsonData ['Records'] = $managedata;
			$jsonData ['TotalRecordCount'] = count ( $managedata );
			$Response->setContent ( Json::encode ( $jsonData ) );
		}
		return $Response;
	}
	public function ViewAction() {
		$this->layout ( 'blank/layout' );
		$Request = $this->getRequest ();
		$Response = $this->getResponse ();
		$data = array ();
		$ViewModel = null;
		if ($Request->isXmlHttpRequest ()) {
			$id = 0;
			$valid = false;
			$id = $this->Decrypt ( ( string ) $this->params ()->fromRoute ( 'query', null ) );
			if ($this->getUserWallet ()->verifyHistoryID ( $id )) {
				$data = $this->getUserWallet ()->getHistoryData ( $id );
				if (count ( $data ) > 0) {
					$options = array (
							'servicelocator' => $this->getServiceLocator (),
							'variable' => $data 
					);
					$data = new WalletView ( $options );
					
					$ViewModel = new ViewModel ();
					$ViewModel->setTerminal ( true );
					$ViewModel->setTemplate ( 'share/form/preview' );
					$ViewModel->setVariables ( array (
							'view' => $data 
					) );
					$ViewModel = $this->getServiceLocator ()->get ( 'viewrenderer' )->render ( $ViewModel );
					$valid = true;
				}
			}
			$Response->setContent ( Json::encode ( array (
					'id' => $id,
					'valid' => $valid,
					'content' => $ViewModel,
					'height' => "250",
					'width' => "500" 
			) ) );
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
			$Status = $this->getUserWallet ()->StatusToForm ();
			$Transaction = $this->getUserWallet ()->TransactionToForm ();
			$Type_From = $this->getUserWallet ()->TypeToForm ();
			$Type_To = $this->getUserWallet ()->TypeToForm ();
			
			$this->variable = array (
					'wallet_status_id' => $Status,
					'wallet_transaction_id' => $Transaction,
					'wallet_type_id_from' => $Type_From,
					'wallet_type_id_to' => $Type_To 
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
