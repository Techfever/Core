<?php
namespace Ajax\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Json\Json;
use Kernel\Country;

class AddressActionController extends AbstractActionController {

	public function getStateAction() {
		$translate = $this->getServiceLocator()->get('Translator');

		$request = $this->getRequest();
		$response = $this->getResponse();
		$success = 0;
		$valid = 0;
		$id = $request->getPost('id');
		$address_data = array();
		$address_data[] = array(
				'id' => '',
				'value' => ''
		);
		if (isset($id) && $id > 0) {
			$address_state = array();
			$Country = new Country();
			$address_state = $Country->getState(null, $id, 'address');
			if (is_array($address_state)) {
				foreach ($address_state as $address_value) {
					if ($address_value['id'] > 0) {
						$address_data[] = array(
								'id' => $address_value['id'],
								'value' => $translate->translate('text_country_state_' . $id . '_' . strtolower(str_replace(' ', '_', $address_value['iso'])))
						);
						$valid = 1;
					}
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
						'id' => $id,
						'data' => $address_data
				)));
		return $response;
	}
}
