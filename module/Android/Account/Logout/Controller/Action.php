<?php

namespace Android\Account\Logout\Controller;

use Techfever\Template\Plugin\AbstractActionController;
use Zend\Json\Json;

class ActionController extends AbstractActionController {
	
	/**
	 * Index Action
	 *
	 * @return ViewModel
	 */
	public function IndexAction() {
		// $request = $this->getRequest ();
		$response = $this->getResponse ();
		
		$success = false;
		$content = null;
		$message = null;
		$error_message = null;
		$json_data = array ();
		
		if ($this->isAndroidRequest ()) {
			$this->getUserAccess ()->setLogout ();
			$success = true;
			$json_data = array (
					'success' => $success,
					'message' => $message,
					'error_message' => $error_message,
					'content' => $content 
			);
		}
		
		$response->setContent ( Json::encode ( $json_data ) );
		return $response;
	}
}
