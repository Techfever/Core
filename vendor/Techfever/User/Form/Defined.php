<?php
namespace Techfever\User\Form;

use Techfever\Address\Address;
use Techfever\Bank\Bank;
use Techfever\Nationality\Nationality;
use Techfever\User\Rank;
use Techfever\Form\Form as BaseForm;

class Defined extends BaseForm {

	public function getVariable() {
		$request = $this->getRequest();

		$security = $request->getPost('user_access_security');
		$security_confirmation = $request->getPost('user_access_security_confirmation');
		$password = $request->getPost('user_access_password');
		$password_confirmation = $request->getPost('user_access_password_confirmation');
		$hierarchy_placement_position = $request->getPost('user_hierarchy_placement_position');

		$Nationality = new Nationality(array(
				'servicelocator' => $this->getServiceLocator(),
		));
		$nationality_country = $Nationality->countryToForm();

		$address_country_select = $request->getPost('address_country_select');
		$address_state_select = $request->getPost('address_state_select');
		$address_state_select_disable = true;
		$address_state_text_hidden = true;
		$Address = new Address(array(
				'country' => $address_country_select,
				'servicelocator' => $this->getServiceLocator(),
		));
		$address_state = array(
				0 => 'text_not_listed'
		);
		$address_country = $Address->countryToForm();
		if ($address_country_select >= "0") {
			if ($address_country_select > 0) {
				$address_state = $Address->stateToForm();
				$address_state[0] = 'text_not_listed';
			}
			if (count($address_state) < 2) {
				$address_state_select = 0;
				$request->getPost()->set('address_state_select', 0);
				$address_state_text_hidden = false;
			} else {
				$address_state_select_disable = false;
			}
		}

		$bank_name_select = $request->getPost('bank_name_select');
		$bank_country_select = $request->getPost('bank_country_select');
		$bank_state_select = $request->getPost('bank_state_select');
		$bank_branch_select = $request->getPost('bank_branch_select');
		$bank_name_text_hidden = true;
		$bank_country_select_disable = true;
		$bank_state_select_disable = true;
		$bank_state_text_hidden = true;
		$bank_branch_select_disable = true;
		$bank_branch_text_hidden = true;
		$Bank = new Bank(array(
				'bank' => $bank_name_select,
				'country' => $bank_country_select,
				'state' => $bank_state_select,
				'servicelocator' => $this->getServiceLocator(),
		));
		$bank_branch = array(
				0 => 'text_not_listed'
		);
		$bank_state = array(
				0 => 'text_not_listed'
		);
		$bank_country = array(
				0 => 'text_not_listed'
		);
		$bank_name = array(
				0 => 'text_not_listed'
		);
		$bank_name = $Bank->bankToForm();
		$bank_name[0] = 'text_not_listed';
		if ($bank_name_select >= "0") {
			$bank_country_select_disable = false;
			if ($bank_name_select == "0") {
				$bank_name_text_hidden = false;
			}
		}
		$bank_country = $Bank->countryToForm();
		if ($bank_country_select >= "0") {
			if ($bank_country_select > 0) {
				$bank_state = $Bank->stateToForm();
				$bank_state[0] = 'text_not_listed';
			}
			if (count($bank_state) < 2) {
				$bank_state_select = 0;
				$request->getPost()->set('bank_state_select', 0);
				$bank_state_text_hidden = false;
			} else {
				$bank_state_select_disable = false;
			}
			if ($bank_state_select >= "0") {
				if ($bank_state_select > 0) {
					$bank_branch = $Bank->branchToForm();
					$bank_branch[0] = 'text_not_listed';
				}
				if (count($bank_branch) < 2) {
					$bank_branch_select = 0;
					$request->getPost()->set('bank_branch_select', 0);
					$bank_branch_text_hidden = false;
				} else {
					$bank_branch_select_disable = false;
				}
			}
		}

		$Rank = new Rank(array(
				'group' => $this->getOption('rank'),
				'servicelocator' => $this->getServiceLocator(),
		));
		$rank = $Rank->rankToForm();
		return array(
				'rankgroup' => $this->getOption('rank'),
				'rank' => $rank,
				'password_confirmation' => $password_confirmation,
				'password' => $password,
				'security_confirmation' => $security_confirmation,
				'security' => $security,
				'hierarchy_placement_position' => $hierarchy_placement_position,
				'nationality_country' => $nationality_country,
				'address_country_select' => $address_country_select,
				'address_country' => $address_country,
				'address_state' => $address_state,
				'address_state_select_disable' => $address_state_select_disable,
				'address_state_text_hidden' => $address_state_text_hidden,
				'bank_name' => $bank_name,
				'bank_name_text_hidden' => $bank_name_text_hidden,
				'bank_country' => $bank_country,
				'bank_country_select_disable' => $bank_country_select_disable,
				'bank_state' => $bank_state,
				'bank_state_select_disable' => $bank_state_select_disable,
				'bank_state_text_hidden' => $bank_state_text_hidden,
				'bank_branch' => $bank_branch,
				'bank_branch_select_disable' => $bank_branch_select_disable,
				'bank_branch_text_hidden' => $bank_branch_text_hidden,
		);
	}
}
