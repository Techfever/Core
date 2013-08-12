<?php
namespace Ajax\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Json\Json;
use Techfever\Address\Address;
use Techfever\User\Management as UserManagement;
use Techfever\Functions\Crypt\Decode as Decrypt;

class AddressActionController extends AbstractActionController {

	public function getStateAction() {
		$request = $this->getRequest();
		$response = $this->getResponse();
		$success = 0;
		$valid = 0;
		$country = $request->getPost('country');
		$address_data = array();
		if ($request->isXmlHttpRequest()) {
			$address_data[] = array(
					'id' => '',
					'value' => ''
			);
			if (isset($country) && $country > 0) {
				$address_state = array();
				$Address = new Address(array(
						'country' => $country,
						'servicelocator' => $this->getServiceLocator(),
				));
				$address_state = $Address->stateToForm();
				if (is_array($address_state)) {
					foreach ($address_state as $address_key => $address_value) {
						$address_data[] = array(
								'id' => $address_key,
								'value' => $this->getTranslate($address_value)
						);
						$valid = 1;
					}
				}
				$success = 1;
			}
			$address_data[] = array(
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
						'data' => $address_data
				)));
		return $response;
	}

	public function getUserAction() {
		$request = $this->getRequest();
		$response = $this->getResponse();
		$success = 0;
		$data = array();
		if ($request->isXmlHttpRequest()) {
			$id = new Decrypt($request->getPost('id'));
			$id = $id->__toString();
			$options = array(
					'servicelocator' => $this->getServiceLocator(),
			);
			$userManagement = new UserManagement($options);
			$profile = $userManagement->getProfileID($id);
			$address = $userManagement->getAddressDefaultID($id);

			$Address = new Address(array(
					'profile_id' => $profile,
					'address_id' => $address,
					'servicelocator' => $this->getServiceLocator(),
			));
			$rawdata = $Address->getUserAddress($address);
			if (is_array($rawdata) && count($rawdata) > 0) {
				$data = array_merge($data, $rawdata);
				$data['user_address_created_date_format'] = "";
				if ($data['user_address_created_date'] != '0000-00-00 00:00:00') {
					$datetime = new \DateTime($data['user_address_created_date']);
					$data['user_address_created_date_format'] = $datetime->format('H:i:s d-m-Y');
				}
				$data['user_address_modified_date_format'] = "";
				if ($data['user_address_modified_date'] != '0000-00-00 00:00:00') {
					$datetime = new \DateTime($data['user_address_modified_date']);
					$data['user_address_modified_date_format'] = $datetime->format('H:i:s d-m-Y');
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
