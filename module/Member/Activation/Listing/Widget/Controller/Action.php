<?php

namespace Member\Activation\Listing\Widget\Controller;

use Techfever\Widget\Controller\General;

class ActionController extends General {
	private $rankgroup = 10000;
	public function InitialAction() {
		$this->setControllerName ( __NAMESPACE__ );
		$content = array ();
		$status = false;
		
		$user_id = $this->getUserID ();
		if ($this->getUserManagement ()->verifyID ( $user_id )) {
			$index = 0;
			$perpage = 10;
			$search = array ();
			
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
			
			$order = array (
					'ua.user_access_created_date ASC' 
			);
			
			$content = $this->getUserManagement ()->getListing ( $this->rankgroup, $search, $order, $index, $perpage, true );
			if ($content != false) {
				$status = true;
			}
		}
		$this->setContent ( array (
				'title' => $this->getTranslate ( 'text_widget_member_activation_listing' ),
				'content' => $content,
				'route' => 'Member/Activation/List',
				'action' => '',
				'success' => $status 
		) );
		$this->setSuccess ( $status );
		
		return $this->getWidgetModel ( $this->getOptions () );
	}
}
