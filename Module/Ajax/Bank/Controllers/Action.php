<?php
namespace Ajax\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Json\Json;
use Kernel\Bank\Bank;

class BankActionController extends AbstractActionController {

	public function getStateAction() {
		$translate = $this->getServiceLocator()->get('Translator');

		$request = $this->getRequest();
		$response = $this->getResponse();
		$success = 0;
		$valid = 0;
		$country = $request->getPost('country');
		$bank_data = array();
		$bank_data[] = array(
				'id' => '',
				'value' => ''
		);
		if (isset($country) && $country > 0) {
			$bank_state = array();
			$Bank = new Bank(array(
					'country' => $country
			));
			$bank_state = $Bank->stateToForm();
			if (is_array($bank_state)) {
				foreach ($bank_state as $bank_key => $bank_value) {
					$bank_data[] = array(
							'id' => $bank_key,
							'value' => $translate->translate($bank_value)
					);
					$valid = 1;
				}
			}
			$success = 1;
		}
		$bank_data[] = array(
				'id' => '0',
				'value' => $translate->translate('text_not_listed')
		);
		$response->setContent(Json::encode(array(
						'success' => $success,
						'valid' => $valid,
						'country' => $country,
						'data' => $bank_data
				)));
		return $response;
	}

	public function getBranchAction() {
		$translate = $this->getServiceLocator()->get('Translator');

		$request = $this->getRequest();
		$response = $this->getResponse();
		$success = 0;
		$valid = 0;
		$country = $request->getPost('country');
		$state = $request->getPost('state');
		$bank = $request->getPost('bank');
		$bank_data = array();
		$bank_data[] = array(
				'id' => '',
				'value' => ''
		);
		if ((isset($bank) && $bank > 0) && (isset($country) && $country > 0) && (isset($state) && $state > 0)) {
			$bank_state = array();
			$Bank = new Bank(array(
					'country' => $country,
					'state' => $state,
					'bank' => $bank
			));
			$bank_branch = $Bank->branchToForm();
			if (is_array($bank_branch)) {
				foreach ($bank_branch as $bank_value) {
					$bank_data[] = array(
							'id' => $bank_key,
							'value' => $translate->translate($bank_value)
					);
					$valid = 1;
				}
			}
			$success = 1;
		}
		$bank_data[] = array(
				'id' => '0',
				'value' => $translate->translate('text_not_listed')
		);
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
}
