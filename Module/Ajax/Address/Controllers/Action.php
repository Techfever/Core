<?php
namespace Ajax\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Json\Json;
use Kernel\Address\Address;

class AddressActionController extends AbstractActionController {

	public function getStateAction() {
		$translate = $this->getServiceLocator()->get('Translator');

		$request = $this->getRequest();
		$response = $this->getResponse();
		$success = 0;
		$valid = 0;
		$country = $request->getPost('country');
		$address_data = array();
		$address_data[] = array(
				'id' => '',
				'value' => ''
		);
		if (isset($country) && $country > 0) {
			$address_state = array();
			$Address = new Address(array(
					'country' => $country
			));
			$address_state = $Address->stateToForm();
			if (is_array($address_state)) {
				foreach ($address_state as $address_key => $address_value) {
					$address_data[] = array(
							'id' => $address_key,
							'value' => $translate->translate($address_value)
					);
					$valid = 1;
				}
			}
			$success = 1;
		}
		$address_data[] = array(
				'id' => '0',
				'value' => $translate->translate('text_not_listed')
		);
		$response->setContent(Json::encode(array(
						'success' => $success,
						'valid' => $valid,
						'country' => $country,
						'data' => $address_data
				)));
		return $response;
	}
}
