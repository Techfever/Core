<?php

namespace System\Desktop\Controller;

use Techfever\Template\Plugin\AbstractActionController;
use Zend\View\Model\ViewModel;

class ActionController extends AbstractActionController {
	
	/**
	 * Index Action
	 *
	 * @return ViewModel
	 */
	public function IndexAction() {
	}
	
	/**
	 * Loading Action
	 *
	 * @return LoadingModel
	 */
	public function LoadingAction() {
		$this->getLog ()->info ( "Loading" );
		$isLogin = $this->isLogin ();
		$isLoginWallet = $this->isLoginWallet ();
		$isAdminUser = $this->isAdminUser ();
		
		$menuLayout = "";
		$notificationLayout = "";
		$dashboardLayout = "";
		
		if ($this->isXmlHttpRequest ()) {
			$this->getLog ()->info ( "OK" );
			if ($isLogin) {
				$menuLayout = $this->ViewModal ( null, 'system/desktop/controller/action/menu' );
			}
		}
		$options = array (
				'isLogin' => $isLogin,
				'isLoginWallet' => $isLoginWallet,
				'isAdminUser' => $isAdminUser,
				'menuLayout' => $menuLayout,
				'notificationLayout' => $notificationLayout,
				'dashboardLayout' => $dashboardLayout 
		);
		return $this->renderJSONModal ( $options );
	}
}
