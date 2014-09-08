<?php

namespace Wallet\Withdraw\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Techfever\Datatable\Datatable;
use Techfever\View\View as WalletView;

class PendingActionController extends AbstractActionController {
	protected $type = 'Wallet_Withdraw';
	protected $module = 'pending';
	protected $datatable = null;
	protected $datacolumn = null;
	protected $datasearch = null;
	public function IndexAction() {
		$this->datacolumn = $this->getDatatable ()->getColumnData ();
		$this->datasearch = $this->getDatatable ()->getSearchData ();
		$this->addCSS ( "vendor/Techfever/Javascript/jquery/themes/jtable/lightcolor/gray/jtable.css" );
		$this->addJavascript ( 'vendor/Techfever/Javascript/jquery/ui/jquery.jtable.js' );
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME . "/Js/datatable.menu.js", array (
				'datatableid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Datatable', '/' ),
				'datalisturi' => $this->url ()->fromRoute ( ucfirst ( str_replace ( '_', '/', $this->type ) ) . '/Pending' ),
				'datacolumn' => $this->datacolumn,
				'datasearch' => $this->datasearch 
		) );
		
		return array (
				'menumodel' => $this->MenuModel (),
				'isAdminUser' => $this->isAdminUser () 
		);
	}
	private function getDatatable() {
		if (! is_object ( $this->datatable )) {
			$options = array (
					'route' => $this->getMatchedRouteName (),
					'action' => 'Index',
					'variable' => $this->getVariable (),
					'servicelocator' => $this->getServiceLocator () 
			);
			$this->datatable = new Datatable ( $options );
		}
		return $this->datatable;
	}
	public function GetAction() {
		$this->layout ( 'blank/layout' );
		$Request = $this->getRequest ();
		$Response = $this->getResponse ();
		
		if ($Request->isXmlHttpRequest ()) {
			$index = 0;
			$perpage = 10;
			$order = 'ua.user_access_id ASC';
			
			$routequery = ( string ) $this->params ()->fromRoute ( 'query', null );
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
			
			$cache = $this->convertToUnderscore ( $order, ',' );
			$cache = $this->convertToUnderscore ( $cache, ',' );
			$cache = $this->convertToUnderscore ( $cache, ' ' );
			$cache = $cache . '_' . $perpage . '_' . $index;
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
					} elseif ($table == 'user_wallet_history_bank') {
						$orderraw [] = 'uwhb.' . $column . ' ' . $arrange [1];
					} elseif ($table == 'user_access_from') {
						$orderraw [] = 'uaf.' . $column . ' ' . $arrange [1];
					} elseif ($table == 'user_access_to') {
						$orderraw [] = 'uat.' . $column . ' ' . $arrange [1];
					}
				}
				$order = $orderraw;
			}
			
			$search = $Request->getPost ()->toArray ();
			if (is_array ( $search ) && count ( $search ) > 0) {
				$searchraw = array ();
				foreach ( $search as $searchkey => $searchvalue ) {
					$column = $this->getDatatable ()->getSearchFieldByColumn ( $searchkey );
					$table = $this->getDatatable ()->getSearchTableByColumn ( $searchkey );
					$pattern = $this->getDatatable ()->getSearchPatternByColumn ( $searchkey );
					if ($table == 'user_wallet_history' && ! empty ( $searchvalue )) {
						$searchraw [$table] [] = 'uwh.' . $column . ' ' . sprintf ( $pattern, $searchvalue );
					} elseif ($table == 'user_wallet_history_bank' && ! empty ( $searchvalue )) {
						$searchraw [$table] [] = 'uwhb.' . $column . ' ' . sprintf ( $pattern, $searchvalue );
					} elseif ($table == 'user_access_from' && ! empty ( $searchvalue )) {
						$searchraw [$table] [] = 'uaf.' . $column . ' ' . sprintf ( $pattern, $searchvalue );
					} elseif ($table == 'user_access_to' && ! empty ( $searchvalue )) {
						$searchraw [$table] [] = 'uat.' . $column . ' ' . sprintf ( $pattern, $searchvalue );
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
				$search ['user_wallet_history'] [] = 'uwh.user_access_id = ' . $this->getUserIDn ();
			}
			$listdataraw = $this->getUserWallet ()->getWithdrawHistoryListing ( $search, $order, $index, $perpage, $cache, true );
			$jsonData = array ();
			$jsonData ['Result'] = "OK";
			$jsonData ['Records'] = $listdataraw;
			$jsonData ['TotalRecordCount'] = count ( $listdataraw );
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
	private function MenuModel() {
		$menu = array (
				array (
						'label' => $this->getTranslate ( 'text_search' ),
						'tab' => 'tabs-search',
						'istab' => True,
						'content' => $this->SearchModel () 
				),
				array (
						'label' => $this->getTranslate ( 'text_column' ),
						'tab' => 'tabs-column',
						'istab' => True,
						'content' => $this->ColumnModel () 
				) 
		);
		$MenuModel = new ViewModel ();
		$MenuModel->setTerminal ( true );
		$MenuModel->setTemplate ( 'share/datatable/menu' );
		$MenuModel->setVariables ( array (
				'menu' => $menu 
		) );
		
		return $this->getServiceLocator ()->get ( 'viewrenderer' )->render ( $MenuModel );
	}
	private function SearchModel() {
		$SearchSimpleModel = new ViewModel ();
		$SearchSimpleModel->setTerminal ( true );
		$SearchSimpleModel->setTemplate ( 'share/wallet/searchsimple' );
		$SearchSimpleModel->setVariables ( array (
				'search' => $this->datasearch 
		) );
		$SearchSimpleModel = $this->getServiceLocator ()->get ( 'viewrenderer' )->render ( $SearchSimpleModel );
		
		$SearchAdvanceModel = new ViewModel ();
		$SearchAdvanceModel->setTerminal ( true );
		$SearchAdvanceModel->setTemplate ( 'share/wallet/searchadvance' );
		$SearchAdvanceModel->setVariables ( array (
				'search' => $this->datasearch 
		) );
		$SearchAdvanceModel = $this->getServiceLocator ()->get ( 'viewrenderer' )->render ( $SearchAdvanceModel );
		
		$SearchModel = new ViewModel ();
		$SearchModel->setTerminal ( true );
		$SearchModel->setTemplate ( 'share/datatable/menu/search' );
		$SearchModel->setVariables ( array (
				'simple' => $SearchSimpleModel,
				'advance' => $SearchAdvanceModel 
		) );
		return $this->getServiceLocator ()->get ( 'viewrenderer' )->render ( $SearchModel );
	}
	private function ColumnModel() {
		$ColumnModel = new ViewModel ();
		$ColumnModel->setTerminal ( true );
		$ColumnModel->setTemplate ( 'share/datatable/menu/column' );
		$ColumnModel->setVariables ( array (
				'column' => $this->datacolumn 
		) );
		return $this->getServiceLocator ()->get ( 'viewrenderer' )->render ( $ColumnModel );
	}
	private function getVariable() {
		$Status = $this->getUserWallet ()->StatusToForm ();
		;
		$Transaction = $this->getUserWallet ()->TransactionToForm ();
		;
		$Type_From = $this->getUserWallet ()->TypeToForm ();
		;
		$Type_To = $this->getUserWallet ()->TypeToForm ();
		;
		return array (
				'user_wallet_status_text' => $Status,
				'user_wallet_transaction_text' => $Transaction,
				'user_wallet_type_from_text' => $Type_From,
				'user_wallet_type_to_text' => $Type_To 
		);
	}
}
