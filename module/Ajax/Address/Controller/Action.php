<?php

namespace Ajax\Address\Controller;

use Techfever\Template\Plugin\AbstractActionController;
use Zend\Json\Json;

class ActionController extends AbstractActionController {
	public function getStateAction() {
		$request = $this->getRequest ();
		$response = $this->getResponse ();
		$address_data = array ();
		$country = $this->params ()->fromQuery ( 'country' );
		$state = $this->params ()->fromQuery ( 'state' );
		if ($request->isXmlHttpRequest ()) {
			$country = $this->getUserAddress ()->getCountryID ( $country );
			$this->getUserAddress ()->setOption ( 'country', $country );
			$this->getUserAddress ()->clearUserAddressData ();
			$address_data = $this->getUserAddress ()->getStateByExpr ( $state );
		} else {
			return $this->redirect ()->toRoute ( 'Index' );
		}
		$response->setContent ( Json::encode ( $address_data ) );
		return $response;
	}
	public function getCountryAction() {
		$request = $this->getRequest ();
		$response = $this->getResponse ();
		$address_data = array ();
		$country = $this->params ()->fromQuery ( 'country' );
		if ($request->isXmlHttpRequest ()) {
			$address_data = $this->getUserAddress ()->getCountryByExpr ( $country );
		} else {
			return $this->redirect ()->toRoute ( 'Index' );
		}
		$response->setContent ( Json::encode ( $address_data ) );
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
			$address = $this->getUserAddress ()->getUserAddressDefaultID ( $profile );
			$this->getUserAddress ()->setOption ( 'profile_id', $profile );
			$this->getUserAddress ()->setOption ( 'address_id', $address );
			$this->getUserAddress ()->clearUserAddressData ();
			$rawdata = $this->getUserAddress ()->getUserAddress ( $address );
			if (is_array ( $rawdata ) && count ( $rawdata ) > 0) {
				$data = array_merge ( $data, $rawdata );
				$data ['user_address_created_date_format'] = "";
				if ($data ['user_address_created_date'] != '0000-00-00 00:00:00') {
					$datetime = new \DateTime ( $data ['user_address_created_date'] );
					$data ['user_address_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				$data ['user_address_modified_date_format'] = "";
				if ($data ['user_address_modified_date'] != '0000-00-00 00:00:00') {
					$datetime = new \DateTime ( $data ['user_address_modified_date'] );
					$data ['user_address_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
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
