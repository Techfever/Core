<?php

namespace Wallet\Listing\Widget\Controller;

use Techfever\Widget\Controller\General;

class ActionController extends General {
	public function InitialAction() {
		$this->setControllerName ( __NAMESPACE__ );
		$content = array ();
		$status = false;
		
		$user_id = $this->getUserID ();
		if ($this->getUserManagement ()->verifyID ( $user_id )) {
			$index = 0;
			$perpage = 10;
			$search = null;
			if (! $this->isAdminUser ()) {
				$search = array (
						'user_wallet_history' => array (
								'uwh.user_access_id = "' . $user_id . '"' 
						) 
				);
			}
			$order = array (
					'uwh.user_wallet_history_created_date ASC' 
			);
			
			$content = $this->getUserWallet ()->getHistoryListing ( $search, $order, $index, $perpage, true );
			if ($content != false) {
				$status = true;
			}
		}
		$this->setContent ( array (
				'title' => $this->getTranslate ( 'text_widget_wallet_listing' ),
				'content' => $content,
				'route' => 'Wallet/List',
				'action' => '',
				'success' => $status 
		) );
		$this->setSuccess ( $status );
		
		return $this->getWidgetModel ( $this->getOptions () );
	}
}
