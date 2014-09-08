<?php

namespace Ajax\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Json\Json;

class BankActionController extends AbstractActionController {
	public function getStateAction() {
		$request = $this->getRequest ();
		$response = $this->getResponse ();
		$success = 0;
		$valid = 0;
		$country = $request->getPost ( 'country' );
		$bank_data = array ();
		if ($request->isXmlHttpRequest ()) {
			$bank_data [] = array (
					'id' => '',
					'value' => '' 
			);
			if (isset ( $country ) && $country > 0) {
				$bank_state = array ();
				
				$this->getUserBank ()->setOption ( 'country', $country );
				$this->getUserBank ()->clearUserBankData ();
				$bank_state = $this->getUserBank ()->stateToForm ();
				
				if (is_array ( $bank_state )) {
					foreach ( $bank_state as $bank_key => $bank_value ) {
						$bank_data [] = array (
								'id' => $bank_key,
								'value' => $this->getTranslate ( $bank_value ) 
						);
						$valid = 1;
					}
				}
				$success = 1;
			}
			$bank_data [] = array (
					'id' => '0',
					'value' => $this->getTranslate ( 'text_not_listed' ) 
			);
		} else {
			return $this->redirect ()->toRoute ( 'Index' );
		}
		$response->setContent ( Json::encode ( array (
				'success' => $success,
				'valid' => $valid,
				'country' => $country,
				'data' => $bank_data 
		) ) );
		return $response;
	}
	public function getBranchAction() {
		$request = $this->getRequest ();
		$response = $this->getResponse ();
		$success = 0;
		$valid = 0;
		$country = $request->getPost ( 'country' );
		$state = $request->getPost ( 'state' );
		$bank = $request->getPost ( 'bank' );
		$bank_data = array ();
		if ($request->isXmlHttpRequest ()) {
			$bank_data [] = array (
					'id' => '',
					'value' => '' 
			);
			if ((isset ( $bank ) && $bank > 0) && (isset ( $country ) && $country > 0) && (isset ( $state ) && $state > 0)) {
				$bank_state = array ();
				
				$this->getUserBank ()->setOption ( 'country', $country );
				$this->getUserBank ()->setOption ( 'state', $state );
				$this->getUserBank ()->setOption ( 'bank', $bank );
				$this->getUserBank ()->clearUserBankData ();
				$bank_branch = $this->getUserBank ()->branchToForm ();
				
				if (is_array ( $bank_branch )) {
					foreach ( $bank_branch as $bank_key => $bank_value ) {
						$bank_data [] = array (
								'id' => $bank_key,
								'value' => $this->getTranslate ( $bank_value ) 
						);
						$valid = 1;
					}
				}
				$success = 1;
			}
			$bank_data [] = array (
					'id' => '0',
					'value' => $this->getTranslate ( 'text_not_listed' ) 
			);
		} else {
			return $this->redirect ()->toRoute ( 'Index' );
		}
		$response->setContent ( Json::encode ( array (
				'success' => $success,
				'valid' => $valid,
				'country' => $country,
				'state' => $state,
				'bank' => $bank,
				'data' => $bank_data 
		) ) );
		return $response;
	}
	public function getUserAction() {
		$request = $this->getRequest ();
		$response = $this->getResponse ();
		$success = 0;
		$data = array ();
		if ($request->isXmlHttpRequest ()) {
			$id = $this->Decrypt ( $request->getPost ( 'id' ) );
			
			$profile = $this->getUserManagement ()->getProfileID ( $id );
			$bank = $this->getUserBank ()->getUserBankDefaultID ( $profile );
			$this->getUserBank ()->setOption ( 'profile_id', $profile );
			$this->getUserBank ()->setOption ( 'bank_id', $bank );
			$this->getUserBank ()->clearUserBankData ();
			$rawdata = $this->getUserBank ()->getUserBank ( $bank );
			if (is_array ( $rawdata ) && count ( $rawdata ) > 0) {
				$data = array_merge ( $data, $rawdata );
				$data ['user_bank_created_date_format'] = "";
				if ($data ['user_bank_created_date'] != '0000-00-00 00:00:00') {
					$datetime = new \DateTime ( $data ['user_bank_created_date'] );
					$data ['user_bank_created_date_format'] = $datetime->format ( 'H:i:s d-m-Y' );
				}
				$data ['user_bank_modified_date_format'] = "";
				if ($data ['user_bank_modified_date'] != '0000-00-00 00:00:00') {
					$datetime = new \DateTime ( $data ['user_bank_modified_date'] );
					$data ['user_bank_modified_date_format'] = $datetime->format ( 'H:i:s d-m-Y' );
				}
				$success = 1;
			}
		} else {
			return $this->redirect ()->toRoute ( 'Index' );
		}
		$response->setContent ( Json::encode ( array (
				'success' => $success,
				'data' => $data,
				'length' => count ( $data ) 
		) ) );
		return $response;
	}
}
