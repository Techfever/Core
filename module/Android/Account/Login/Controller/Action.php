<?php

namespace Android\Account\Login\Controller;

use Techfever\Template\Plugin\AbstractActionController;
use Zend\Json\Json;
use Techfever\User\Form\Defined as UserLoginForm;

class ActionController extends AbstractActionController {
	protected $inputform = null;
	
	/**
	 * Index Action
	 *
	 * @return ViewModel
	 */
	public function IndexAction() {
		$request = $this->getRequest ();
		$response = $this->getResponse ();
		
		$success = false;
		$content = null;
		$message = null;
		$error_message = null;
		$json_data = array ();
		$InputForm = $this->InputForm ();
		
		if ($this->isAndroidRequest ()) {
			$id = 0;
			if ($InputForm->isPost () && $InputForm->isValid ()) {
				$username = $request->getPost ( 'account_username' );
				$password = $request->getPost ( 'account_password' );
				if ($this->getUserAccess ()->verifyPassword ( $username, $password )) {
					$success = true;
					$id = $this->getUserManagement ()->getID ( $username );
					$this->getUserAccess ()->setLogin ( $id );
				}
			}
			$Input = $request->getPost ( 'Input' );
			$error_message = $InputForm->getMessages ();
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
	protected function InputForm() {
		if (! is_object ( $this->inputform )) {
			$options = array (
					'servicelocator' => $this->getServiceLocator (),
					'action' => 'Index' 
			);
			$this->inputform = new UserLoginForm ( $options );
		}
		return $this->inputform;
	}
}
