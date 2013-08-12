<?php
namespace Ajax\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Json\Json;

class UserActionController extends AbstractActionController {
	public function checkUsernameAction() {
		$request = $this->getRequest();
		$response = $this->getResponse();
		$success = 0;
		$valid = 0;
		$username = $request->getPost('username');
		if ($request->isXmlHttpRequest()) {
			if (isset($username) && strlen($username) > 0) {
				$valid = 1;
				$success = 1;
			}
		} else {
			return $this->redirect()->toRoute('Index');
		}
		$response->setContent(Json::encode(array(
						'success' => $success,
						'valid' => $valid,
						'username' => $username
				)));
		return $response;
	}
}
