<?php

namespace Wallet\Balance\Widget\Controller;

use Techfever\Widget\Controller\General;

class ActionController extends General {
	public function InitialAction() {
		$this->setControllerName ( __NAMESPACE__ );
		$content = array ();
		$status = false;
		
		$user_id = $this->getUserID ();
		if ($this->getUserManagement ()->verifyID ( $user_id )) {
			$this->getUserWallet ()->setOption ( 'from_user', $user_id );
			$content = $this->getUserWallet ()->getWalletData ();
			$status = true;
		}
		$this->setContent ( array (
				'title' => $this->getTranslate ( 'text_widget_wallet_balance' ),
				'content' => $content,
				'success' => $status 
		) );
		$this->setSuccess ( $status );
		
		return $this->getWidgetModel ( $this->getOptions () );
	}
}
