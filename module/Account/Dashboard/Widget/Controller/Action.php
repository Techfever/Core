<?php

namespace Account\Dashboard\Widget\Controller;

use Techfever\Widget\Controller\General;

class ActionController extends General {
	public function InitialAction() {
		$this->setControllerName ( __NAMESPACE__ );
		$content = array ();
		$status = false;
		
		$user_id = $this->getUserID ();
		if ($this->getUserManagement ()->verifyID ( $user_id )) {
			$status = true;
		}
		$this->setContent ( array (
				'title' => $this->getTranslate ( 'text_widget_account_dashboard' ),
				'content' => $content,
				'success' => $status 
		) );
		$this->setSuccess ( $status );
		
		return $this->getWidgetModel ( $this->getOptions () );
	}
}
