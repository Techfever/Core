<?php
namespace Ajax\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Json\Json;
use Techfever\Bank\Bank;
use Techfever\User\Management as UserManagement;
use Techfever\Functions\Crypt\Decode as Decrypt;

class BankActionController extends AbstractActionController {

	public function getStateAction() {
		$request = $this->getRequest();
		$response = $this->getResponse();
		$success = 0;
		$valid = 0;
		$country = $request->getPost('country');
		$bank_data = array();
		if ($request->isXmlHttpRequest()) {
			$bank_data[] = array(
					'id' => '',
					'value' => ''
			);
			if (isset($country) && $country > 0) {
				$bank_state = array();
				$Bank = new Bank(array(
						'country' => $country,
						'servicelocator' => $this->getServiceLocator(),
				));
				$bank_state = $Bank->stateToForm();
				if (is_array($bank_state)) {
					foreach ($bank_state as $bank_key => $bank_value) {
						$bank_data[] = array(
								'id' => $bank_key,
								'value' => $this->getTranslate($bank_value)
						);
						$valid = 1;
					}
				}
				$success = 1;
			}
			$bank_data[] = array(
					'id' => '0',
					'value' => $this->getTranslate('text_not_listed')
			);
		} else {
			return $this->redirect()->toRoute('Index');
		}
		$response->setContent(Json::encode(array(
						'success' => $success,
						'valid' => $valid,
						'country' => $country,
						'data' => $bank_data
				)));
		return $response;
	}

	public function getBranchAction() {
		$request = $this->getRequest();
		$response = $this->getResponse();
		$success = 0;
		$valid = 0;
		$country = $request->getPost('country');
		$state = $request->getPost('state');
		$bank = $request->getPost('bank');
		$bank_data = array();
		if ($request->isXmlHttpRequest()) {
			$bank_data[] = array(
					'id' => '',
					'value' => ''
			);
			if ((isset($bank) && $bank > 0) && (isset($country) && $country > 0) && (isset($state) && $state > 0)) {
				$bank_state = array();
				$Bank = new Bank(array(
						'country' => $country,
						'state' => $state,
						'bank' => $bank,
						'servicelocator' => $this->getServiceLocator(),
				));
				$bank_branch = $Bank->branchToForm();
				if (is_array($bank_branch)) {
					foreach ($bank_branch as $bank_value) {
						$bank_data[] = array(
								'id' => $bank_key,
								'value' => $this->getTranslate($bank_value)
						);
						$valid = 1;
					}
				}
				$success = 1;
			}
			$bank_data[] = array(
					'id' => '0',
					'value' => $this->getTranslate('text_not_listed')
			);
		} else {
			return $this->redirect()->toRoute('Index');
		}
		$response->setContent(Json::encode(array(
						'success' => $success,
						'valid' => $valid,
						'country' => $country,
						'state' => $state,
						'bank' => $bank,
						'data' => $bank_data
				)));
		return $response;
	}

	public function getUserAction() {
		$request = $this->getRequest();
		$response = $this->getResponse();
		$success = 0;
		if ($request->isXmlHttpRequest()) {
			$id = new Decrypt($request->getPost('id'));
			$id = $id->__toString();
			$options = array(
					'servicelocator' => $this->getServiceLocator(),
			);
			$userManagement = new UserManagement($options);
			$profile = $userManagement->getProfileID($id);
			$bank = $userManagement->getBankDefaultID($id);

			$data = array();
			$Bank = new Bank(array(
					'profile_id' => $profile,
					'bank_id' => $bank,
					'servicelocator' => $this->getServiceLocator(),
			));
			$rawdata = $Bank->getUserBank($bank);
			if (is_array($rawdata) && count($rawdata) > 0) {
				$data = array_merge($data, $rawdata);
				$data['user_bank_created_date_format'] = "";
				if ($data['user_bank_created_date'] != '0000-00-00 00:00:00') {
					$datetime = new \DateTime($data['user_bank_created_date']);
					$data['user_bank_created_date_format'] = $datetime->format('H:i:s d-m-Y');
				}
				$data['user_bank_modified_date_format'] = "";
				if ($data['user_bank_modified_date'] != '0000-00-00 00:00:00') {
					$datetime = new \DateTime($data['user_bank_modified_date']);
					$data['user_bank_modified_date_format'] = $datetime->format('H:i:s d-m-Y');
				}

				$success = 1;
			}
		} else {
			return $this->redirect()->toRoute('Index');
		}
		$response->setContent(Json::encode(array(
						'success' => $success,
						'data' => $data,
						'length' => count($data),
				)));
		return $response;
	}
}
