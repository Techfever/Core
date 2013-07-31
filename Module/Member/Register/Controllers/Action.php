<?php
namespace Member\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Kernel\Template\Template;
use Kernel\Form\Form;
use Kernel\Address\Address;
use Kernel\Bank\Bank;
use Kernel\Nationality\Nationality;
use Kernel\User\Management as UserManagement;
use Kernel\User\Rank;
use Kernel\Database\Database;

class RegisterActionController extends AbstractActionController {

	protected $userManagement = null;

	protected $form = null;

	public function __construct() {
		Template::addCSS("ui-lightness/jquery-ui.css", "jquery");
		$this->userManagement = new UserManagement();

		$response = $this->getResponse();
		$request = $this->getRequest();

		$this->form = new Form(array(
				'field' => $this->GetField(),
				'action' => 'Index',
				'method' => 'post',
				'response' => $response,
				'request' => $request
		));
	}
	public function IndexAction() {
		Template::addCSS("Vendor/Techfever/Theme/" . SYSTEM_THEME . "/CSS/tooltip.css");
		Template::addJavascript('Vendor/Techfever/Javascript/techfever/registerverify.js', array(
				'formid' => $this->MatchedRouteName()
		));

		$response = $this->getResponse();
		$request = $this->getRequest();
		$XMLHttpRequest = $this->form->getPost('XMLHttpRequest', 0);
		if ($XMLHttpRequest == 1) {
			$messages = array();
			$subaction = null;
			$input = null;
			$relation = null;
			$messagescount = 0;
			$valid = false;
			$redirect = null;
			$input = $this->form->getPost('Input', null);
			if (!is_null($input)) {
				$relation = $this->form->getValidatorRelation($input);
				$messages[$input] = "";
			}
			if ($this->form->isPost()) {
				if ($this->form->isValid()) {
					$subaction = $this->form->getPost('subaction', 'preview');
					if ($subaction == 'register') {
						$valid = true;
						$data = $this->form->getData();
						$id = $this->userManagement->createUser($data);
						if ($id !== false && $id > 0) {
							$this->PlaceHolder('id')->set($id);
							$this->FlashMessenger()->addMessage('text_success_msg_register_user');
							$redirect = $this->url()->fromRoute($this->MatchedRouteName(), array(
											'action' => 'Preview'
									));
						} else {
							$this->flashMessenger()->addMessage('text_error_msg_register_user');
							$redirect = $this->url()->fromRoute($this->MatchedRouteName(), array(
											'action' => 'Index'
									));
						}
					}
				} else {
					$messages_data = $this->form->getMessages();
					$messagescount = count($messages_data);
					if (count($messages_data) > 0) {
						foreach ($messages_data as $messages_key => $messages_data) {
							foreach ($messages_data as $messages_data) {
								$messages[$messages_key] = $messages_data;
							}
						}
					}
				}
			}
			$response->setContent(Json::encode(array(
							'input' => $input,
							'subaction' => $subaction,
							'relation' => $relation,
							'messages' => $messages,
							'messagescount' => $messagescount,
							'valid' => $valid,
							'redirect' => $redirect
					)));
			return $response;
		} else {
			return array(
					'title' => 'text_member_register_title',
					'form' => $this->form
			);
		}
	}

	public function PreviewAction() {
		$id = (int) $this->params()->fromRoute('id', 0);

		if (!$id) {
			return $this->redirect()->toRoute($this->MatchedRouteName(), array(
							'action' => 'Index'
					));
		}
		try {
			$data = $this->userManagement->getData($id);
			if (count($data) < 1) {
				throw new \Exception('Could not find the User ID ( $id )');
			}
			$timestampNow = new \DateTime();
			$timestampCreated = new \DateTime($data['user_access']['user_access_created_date']);
			$timestampDiff = $timestampNow->format('YmdHis') - $timestampCreated->format('YmdHis');
			if ($timestampDiff > 3600) {
				//throw new \Exception('User Register preview over time period');
			}
		} catch (\Exception $ex) {
			return $this->redirect()->toRoute($this->MatchedRouteName(), array(
							'action' => 'Index'
					));
		}
		//print_r($data);

		return array(
				'title' => 'text_success_member_register_title',
				'form' => $this->form
		);
	}

	public function GetField() {

		$request = $this->getRequest();

		$security = $request->getPost('security');
		$security_confirmation = $request->getPost('security_confirmation');
		$password = $request->getPost('password');
		$password_confirmation = $request->getPost('password_confirmation');

		$Nationality = new Nationality();
		$nationality_country = $Nationality->countryToForm();

		$address_country_select = $request->getPost('address_country_select');
		$address_state_select = $request->getPost('address_state_select');
		$address_state_select_disable = true;
		$address_state_text_hidden = true;
		$Address = new Address(array(
				'country' => $address_country_select
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
				'state' => $bank_state_select
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
				'group' => 10000
		));
		$rank = $Rank->rankToForm();

		$field = array(
				'rank' => array(
						'type' => 'Select',
						'options' => array(
								'empty_option' => '',
								'value_options' => $rank,
						),
						'attributes' => array(
								'require' => true
						),
						'validators' => array(
								array(
										'name' => 'Select',
										'options' => array(
												'type' => 'Integer'
										)
								)
						),
						'table' => array(
								'name' => 'user_access',
								'column' => 'user_rank_id'
						),
				),
				'rankseperator' => array(
						'type' => 'Seperator',
						'attributes' => array(
								'class' => 'seperator'
						)
				),
				'password' => array(
						'type' => 'Password',
						'attributes' => array(
								'require' => true,
						),
						'validators' => array(
								array(
										'name' => 'Match',
										'options' => array(
												'min' => 6,
												'match' => $password_confirmation,
												'chain' => 'password_confirmation'
										)
								)
						),
						'table' => array(
								'name' => 'user_access',
								'column' => 'user_access_password'
						),
				),
				'password_confirmation' => array(
						'type' => 'Password',
						'attributes' => array(
								'require' => true,
						),
						'validators' => array(
								array(
										'name' => 'Match',
										'options' => array(
												'min' => 6,
												'match' => $password,
												'chain' => 'password'
										)
								)
						),
				),
				'passwordseperator' => array(
						'type' => 'Seperator',
						'attributes' => array(
								'class' => 'seperator'
						)
				),
				'security' => array(
						'type' => 'Password',
						'attributes' => array(
								'require' => true,
						),
						'validators' => array(
								array(
										'name' => 'Match',
										'options' => array(
												'min' => 6,
												'match' => $security_confirmation,
												'chain' => 'security_confirmation'
										)
								)
						),
						'table' => array(
								'name' => 'user_access',
								'column' => 'user_access_security'
						),
				),
				'security_confirmation' => array(
						'type' => 'Password',
						'attributes' => array(
								'require' => true,
						),
						'validators' => array(
								array(
										'name' => 'Match',
										'options' => array(
												'min' => 6,
												'match' => $security,
												'chain' => 'security'
										)
								)
						),
				),
				'securityseperator' => array(
						'type' => 'Seperator',
						'attributes' => array(
								'class' => 'seperator'
						)
				),
				'profile_designation' => array(
						'type' => 'Select',
						'options' => array(
								'empty_option' => '',
						),
						'attributes' => array(
								'require' => false
						),
						'validators' => array(
								array(
										'name' => 'Select',
										'options' => array(
												'type' => 'String'
										)
								)
						),
						'table' => array(
								'name' => 'user_profile',
								'column' => 'user_profile_designation'
						),
				),
				'profile_firstname' => array(
						'type' => 'Text',
						'attributes' => array(
								'require' => false,
						),
						'validators' => array(
								array(
										'name' => 'Text',
										'options' => array(
												'min' => 1
										)
								)
						),
						'table' => array(
								'name' => 'user_profile',
								'column' => 'user_profile_firstname'
						),
				),
				'profile_lastname' => array(
						'type' => 'Text',
						'attributes' => array(
								'require' => false,
						),
						'validators' => array(
								array(
										'name' => 'Text',
										'options' => array(
												'min' => 1
										)
								)
						),
						'table' => array(
								'name' => 'user_profile',
								'column' => 'user_profile_lastname'
						),
				),
				'profile_nric_passport' => array(
						'type' => 'Text',
						'attributes' => array(
								'require' => false,
						),
						'validators' => array(
								array(
										'name' => 'Text',
										'options' => array(
												'min' => 6
										)
								)
						),
						'table' => array(
								'name' => 'user_profile',
								'column' => 'user_profile_nric_passport'
						),
				),
				'profile_gender' => array(
						'type' => 'Radio',
						'options' => array(
								'empty_option' => ''
						),
						'attributes' => array(
								'require' => false,
						),
						'validators' => array(
								array(
										'name' => 'Select',
										'options' => array(
												'type' => 'String'
										)
								)
						),
						'table' => array(
								'name' => 'user_profile',
								'column' => 'user_profile_gender'
						),
				),
				'profile_dob' => array(
						'type' => 'SelectDate',
						'attributes' => array(
								'require' => true,
						),
						'table' => array(
								'name' => 'user_profile',
								'column' => 'user_profile_dob'
						),
				),
				'profile_nationality' => array(
						'type' => 'Select',
						'options' => array(
								'empty_option' => '',
								'value_options' => $nationality_country,
						),
						'attributes' => array(
								'require' => false
						),
						'validators' => array(
								array(
										'name' => 'Select',
										'options' => array(
												'type' => 'String'
										)
								)
						),
						'table' => array(
								'name' => 'user_profile',
								'column' => 'user_profile_nationality'
						),
				),
				'profile_email_address' => array(
						'type' => 'Text',
						'attributes' => array(
								'require' => false,
						),
						'validators' => array(
								array(
										'name' => 'Email'
								)
						),
						'table' => array(
								'name' => 'user_profile',
								'column' => 'user_profile_email_address'
						),
				),
				'profile_mobile_no' => array(
						'type' => 'Text',
						'attributes' => array(
								'require' => false,
						),
						'validators' => array(
								array(
										'name' => 'Text',
										'options' => array(
												'min' => 6,
												'type' => 'Numeric',
										)
								)
						),
						'table' => array(
								'name' => 'user_profile',
								'column' => 'user_profile_mobile_no'
						),
				),
				'profile_telephone_no' => array(
						'type' => 'Text',
						'attributes' => array(
								'require' => false,
						),
						'validators' => array(
								array(
										'name' => 'Text',
										'options' => array(
												'min' => 6,
												'type' => 'Numeric',
										)
								)
						),
						'table' => array(
								'name' => 'user_profile',
								'column' => 'user_profile_telephone_no'
						),
				),
				'profile_office_no' => array(
						'type' => 'Text',
						'attributes' => array(
								'require' => false,
						),
						'validators' => array(
								array(
										'name' => 'Text',
										'options' => array(
												'min' => 6,
												'type' => 'Numeric',
										)
								)
						),
						'table' => array(
								'name' => 'user_profile',
								'column' => 'user_profile_office_no'
						),
				),
				'profile_fax_no' => array(
						'type' => 'Text',
						'attributes' => array(
								'require' => false,
						),
						'validators' => array(
								array(
										'name' => 'Text',
										'options' => array(
												'min' => 6,
												'type' => 'Numeric',
										)
								)
						),
						'table' => array(
								'name' => 'user_profile',
								'column' => 'user_profile_fax_no'
						),
				),
				'profileseperator' => array(
						'type' => 'Seperator',
						'attributes' => array(
								'class' => 'seperator'
						)
				),
				'address_street_1' => array(
						'type' => 'Text',
						'attributes' => array(
								'require' => false,
						),
						'validators' => array(
								array(
										'name' => 'Text',
										'options' => array(
												'min' => 2
										)
								)
						),
						'table' => array(
								'name' => 'user_address',
								'column' => 'user_address_street_1'
						),
				),
				'address_street_2' => array(
						'type' => 'Text',
						'attributes' => array(
								'require' => false,
						),
						'validators' => array(
								array(
										'name' => 'Text',
										'options' => array(
												'min' => 2
										)
								)
						),
						'table' => array(
								'name' => 'user_address',
								'column' => 'user_address_street_2'
						),
				),
				'address_city' => array(
						'type' => 'Text',
						'attributes' => array(
								'require' => false,
						),
						'validators' => array(
								array(
										'name' => 'Text',
										'options' => array(
												'min' => 3
										)
								)
						),
						'table' => array(
								'name' => 'user_address',
								'column' => 'user_address_city'
						),
				),
				'address_postcode' => array(
						'type' => 'Text',
						'attributes' => array(
								'require' => false,
						),
						'validators' => array(
								array(
										'name' => 'PostalCode',
										'options' => array(
												'min' => 3,
												'allowed' => true,
												'country' => $address_country_select,
												'chain' => 'address_postcode'
										)
								)
						),
						'table' => array(
								'name' => 'user_address',
								'column' => 'user_address_postcode'
						),
				),
				'address_country_select' => array(
						'type' => 'Select',
						'options' => array(
								'empty_option' => '',
								'value_options' => $address_country,
						),
						'attributes' => array(
								'require' => false,
						),
						'validators' => array(
								array(
										'name' => 'Select',
										'options' => array(
												'type' => 'Integer',
												'chain' => 'address_postcode'
										)
								)
						),
						'table' => array(
								'name' => 'user_address',
								'column' => 'user_address_country_id'
						),
				),
				'address_state_select' => array(
						'type' => 'Select',
						'options' => array(
								'empty_option' => '',
								'value_options' => $address_state,
						),
						'attributes' => array(
								'disabled' => $address_state_select_disable,
								'require' => false,
						),
						'validators' => array(
								array(
										'name' => 'Select',
										'options' => array(
												'type' => 'Integer'
										)
								)
						),
						'table' => array(
								'name' => 'user_address',
								'column' => 'user_address_state_id'
						),
				),
				'address_state' => array(
						'type' => 'Text',
						'attributes' => array(
								'not_show_label' => true,
								'is_hidden' => $address_state_text_hidden,
								'require' => false,
						),
						'validators' => array(
								array(
										'name' => 'Text',
										'options' => array(
												'min' => 2
										)
								)
						),
						'table' => array(
								'name' => 'user_address',
								'column' => 'user_address_state'
						),
				),
				'addressseperator' => array(
						'type' => 'Seperator',
						'attributes' => array(
								'class' => 'seperator'
						)
				),
				'bank_holder_name' => array(
						'type' => 'Text',
						'attributes' => array(
								'require' => false,
						),
						'validators' => array(
								array(
										'name' => 'Text',
										'options' => array(
												'min' => 2
										)
								)
						),
						'table' => array(
								'name' => 'user_bank',
								'column' => 'user_bank_holder_name'
						),
				),
				'bank_holder_no' => array(
						'type' => 'Text',
						'attributes' => array(
								'require' => false,
						),
						'validators' => array(
								array(
										'name' => 'Text',
										'options' => array(
												'min' => 6,
												'type' => 'Numeric',
										)
								)
						),
						'table' => array(
								'name' => 'user_bank',
								'column' => 'user_bank_holder_no'
						),
				),
				'bank_name_select' => array(
						'type' => 'Select',
						'options' => array(
								'empty_option' => '',
								'value_options' => $bank_name,
						),
						'attributes' => array(
								'require' => false,
						),
						'validators' => array(
								array(
										'name' => 'Select',
										'options' => array(
												'type' => 'Integer'
										)
								)
						),
						'table' => array(
								'name' => 'user_bank',
								'column' => 'user_bank_name_id'
						),
				),
				'bank_name' => array(
						'type' => 'Text',
						'attributes' => array(
								'not_show_label' => true,
								'is_hidden' => $bank_name_text_hidden,
								'require' => false,
						),
						'validators' => array(
								array(
										'name' => 'Text',
										'options' => array(
												'min' => 2
										)
								)
						),
						'table' => array(
								'name' => 'user_bank',
								'column' => 'user_bank_name'
						),
				),
				'bank_country_select' => array(
						'type' => 'Select',
						'options' => array(
								'empty_option' => '',
								'value_options' => $bank_country,
						),
						'attributes' => array(
								'disabled' => $bank_country_select_disable,
								'require' => false,
						),
						'validators' => array(
								array(
										'name' => 'Select',
										'options' => array(
												'type' => 'Integer'
										)
								)
						),
						'table' => array(
								'name' => 'user_bank',
								'column' => 'user_bank_country_id'
						),
				),
				'bank_state_select' => array(
						'type' => 'Select',
						'options' => array(
								'empty_option' => '',
								'value_options' => $bank_state,
						),
						'attributes' => array(
								'disabled' => $bank_state_select_disable,
								'require' => false,
						),
						'validators' => array(
								array(
										'name' => 'Select',
										'options' => array(
												'type' => 'Integer'
										)
								)
						),
						'table' => array(
								'name' => 'user_bank',
								'column' => 'user_bank_state_id'
						),
				),
				'bank_state' => array(
						'type' => 'Text',
						'attributes' => array(
								'not_show_label' => true,
								'is_hidden' => $bank_state_text_hidden,
								'require' => false,
						),
						'validators' => array(
								array(
										'name' => 'Text',
										'options' => array(
												'min' => 2
										)
								)
						),
						'table' => array(
								'name' => 'user_bank',
								'column' => 'user_bank_state'
						),
				),
				'bank_branch_select' => array(
						'type' => 'Select',
						'options' => array(
								'empty_option' => '',
								'value_options' => $bank_branch,
						),
						'attributes' => array(
								'disabled' => $bank_branch_select_disable,
								'require' => false,
						),
						'validators' => array(
								array(
										'name' => 'Select',
										'options' => array(
												'type' => 'Integer'
										)
								)
						),
						'table' => array(
								'name' => 'user_bank',
								'column' => 'user_bank_branch_id'
						),
				),
				'bank_branch' => array(
						'type' => 'Text',
						'attributes' => array(
								'not_show_label' => true,
								'is_hidden' => $bank_branch_text_hidden,
								'require' => false,
						),
						'validators' => array(
								array(
										'name' => 'Text',
										'options' => array(
												'min' => 2
										)
								)
						),
						'table' => array(
								'name' => 'user_bank',
								'column' => 'user_bank_branch'
						),
				),
				'bankseperator' => array(
						'type' => 'Seperator',
						'attributes' => array(
								'class' => 'seperator'
						)
				),
				'subaction' => array(
						'type' => 'hidden',
						'attributes' => array(
								'value' => 'preview'
						),
				),
				'action' => array(
						'type' => 'Submit',
						'attributes' => array(
								'value' => 'text_preview',
								'class' => 'button green',
								'rowspan' => 2
						)
				),
				'cancel' => array(
						'type' => 'Button',
						'options' => array(
								'label' => 'text_clear',
						),
						'attributes' => array(
								'class' => 'button green'
						)
				)
		);
		return $field;
	}
}
