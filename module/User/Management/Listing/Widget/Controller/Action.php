<?php

namespace User\Management\Listing\Widget\Controller;

use Techfever\Widget\Controller\General;

class ActionController extends General {
	private $rankgroup = 88888;
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
						'user_hierarchy' => array (
								'uh.user_hierarchy_sponsor_username = "' . $this->getUsername () . '"' 
						) 
				);
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
				'title' => $this->getTranslate ( 'text_widget_management_listing' ),
				'content' => $content,
				'route' => 'Management/List',
				'action' => '',
				'success' => $status 
		) );
		$this->setSuccess ( $status );
		
		return $this->getWidgetModel ( $this->getOptions () );
	}
}
