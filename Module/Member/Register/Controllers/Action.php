<?php
namespace Member\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Kernel\Template;
use Kernel\Form;
use Kernel\Country;

class RegisterActionController extends AbstractActionController {
	public function __construct() {
		Template::addCSS("ui-lightness/jquery-ui.css", "jquery");
	}
	public function IndexAction() {
		Template::addCSS("Vendor/Techfever/Theme/" . SYSTEM_THEME . "/CSS/tooltip.css");
		Template::addJavascript('Vendor/Techfever/Javascript/techfever/registerverify.js');
		$formOptions = array(
				'field' => $this->GetField(),
				'route' => $this->GetRoute(),
				'action' => 'Index',
				'method' => 'post'
		);
		$form = new Form($formOptions);

		$response = $this->getResponse();
		$request = $this->getRequest();
		$XMLHttpRequest = $request->getPost('XMLHttpRequest', 0);
		$messages = array();
		if ($request->isPost()) {
			if ($XMLHttpRequest == 1) {
				$input = $request->getPost('input', null);
				$value = $request->getPost('value', null);
				$request->getPost()->fromArray(array(
								$input => $value
						));
			}
			$form->setData($request->getPost());
			if ($form->isValid()) {
				//$this->contentManagement->saveData($form->getData());
				//return $this->redirect()->toRoute();
			} else {
				$element = null;
				if ($XMLHttpRequest == 1) {
					$element = $form->get($input);
					$element->setValue($value);
					$messages[$input] = "";
				}
				$messages_data = $form->getMessages($element);
				if (count($messages_data) > 0) {
					foreach ($messages_data as $messages_key => $messages_data) {
						foreach ($messages_data as $messages_data) {
							$messages[$messages_key] = $messages_data;
						}
					}
				}
			}
		}

		if ($XMLHttpRequest == 1) {
			$response->setContent(Json::encode(array(
							'input' => $input,
							'messages' => $messages
					)));
			return $response;
		} else {
			return array(
					'title' => 'text_member_register_title',
					'form' => $form,
					'messages' => $messages
			);
		}
	}

	public function PreviewAction() {
	}

	public function DoneAction() {
	}

	public function GetRoute() {
		return $this->getEvent()->getRouteMatch()->getMatchedRouteName();
	}

	public function GetField() {

		$Country = new Country();

		$nationality_country = $Country->nationalityToForm();

		$address_country = $Country->addressToForm();
		$address_state = array(
				0 => 'text_not_listed'
		);
		if (isset($_POST['address_country_select']) && $_POST['address_country_select'] > 0) {
			$address_state = $Country->stateToForm($_POST['address_country_select'], 'address');
			$address_state[0] = 'text_not_listed';
		}

		$bank_country = $Country->bankToForm();
		$bank_state = array(
				0 => 'text_not_listed'
		);
		if (isset($_POST['bank_country_select']) && $_POST['bank_country_select'] > 0) {
			$bank_state = $Country->stateToForm($_POST['bank_country_select'], 'bank');
			$bank_state[0] = 'text_not_listed';
		}
		$bank_branch = array(
				0 => 'text_not_listed'
		);
		if (isset($_POST['bank_state_select']) && $_POST['bank_state_select'] > 0) {
			$bank_branch = $Country->stateToForm($_POST['bank_state_select'], 'bank');
			$bank_branch[0] = 'text_not_listed';
		}

		$field = array(
				'profile_designation' => array(
						'type' => 'Select',
						'options' => array(
								'empty_option' => '',
						),
						'attributes' => array(
								'require' => true
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
				'profile_fullname' => array(
						'type' => 'Text',
						'attributes' => array(
								'require' => true,
						),
						'validators' => array(
								array(
										'name' => 'StringLength',
										'options' => array(
												'min' => 2
										)
								)
						),
						'table' => array(
								'name' => 'user_profile',
								'column' => 'user_profile_firstname'
						),
				),
				'profile_firstname' => array(
						'type' => 'Text',
						'attributes' => array(
								'require' => false,
						),
						'validators' => array(
								array(
										'name' => 'StringLength',
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
										'name' => 'StringLength',
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
								'require_comment' => array(
										'text_error_input_text_min'
								),
								'require_min' => 6,
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
								'require_comment' => array(
										'text_error_input_radio'
								)
						),
						'table' => array(
								'name' => 'user_profile',
								'column' => 'user_profile_gender'
						),
				),
				'profile_dob' => array(
						'type' => 'Text',
						'attributes' => array(
								'id' => 'profile_dob',
								'readonly' => false,
								'size' => '11',
								'maxlength' => '10',
								'require' => false,
								'require_comment' => array(
										'text_error_input_select'
								)
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
								'require' => false,
								'require_comment' => array(
										'text_error_input_select'
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
								'require_comment' => array(
										'text_error_input_text_min'
								),
								'require_min' => 6,
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
								'require_comment' => array(
										'text_error_input_text_numeric_min'
								),
								'require_min' => 6,
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
								'require_comment' => array(
										'text_error_input_text_numeric_min'
								),
								'require_min' => 6,
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
								'require_comment' => array(
										'text_error_input_text_numeric_min'
								),
								'require_min' => 6,
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
								'require_comment' => array(
										'text_error_input_text_numeric_min'
								),
								'require_min' => 6,
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
								'require_comment' => array(
										'text_error_input_text_min'
								),
								'require_min' => 3,
						),
				),
				'address_street_2' => array(
						'type' => 'Text',
						'attributes' => array(
								'require' => false,
								'require_comment' => array(
										'text_error_input_text_min'
								),
								'require_min' => 3,
						),
				),
				'address_city' => array(
						'type' => 'Text',
						'attributes' => array(
								'require' => false,
								'require_comment' => array(
										'text_error_input_text_min'
								),
								'require_min' => 3,
						),
				),
				'address_postcode' => array(
						'type' => 'Text',
						'attributes' => array(
								'require' => false,
								'require_comment' => array(
										'text_error_input_text_min'
								),
								'require_min' => 3,
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
								'require_comment' => array(
										'text_error_input_select'
								)
						),
				),
				'address_state_select' => array(
						'type' => 'Select',
						'options' => array(
								'empty_option' => '',
								'value_options' => $address_state,
						),
						'attributes' => array(
								'disabled' => true,
								'require' => false,
								'require_comment' => array(
										'text_error_input_select'
								)
						),
				),
				'address_state' => array(
						'type' => 'Text',
						'attributes' => array(
								'not_show_label' => true,
								'is_hidden' => (isset($_POST['address_state_select']) && $_POST['address_state_select'] == "0" ? false : (isset($_POST['address_state_select']) && $_POST['address_state_select'] >= 0 ? true : ($address_country > 1 ? true : false))),
								'require' => false,
								'require_comment' => array(
										'text_error_input_text_min'
								),
								'require_min' => 2,
						)
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
								'require_comment' => array(
										'text_error_input_text_min'
								),
								'require_min' => 2,
						),
				),
				'bank_holder_no' => array(
						'type' => 'Text',
						'attributes' => array(
								'require' => false,
								'require_comment' => array(
										'text_error_input_text_min'
								),
								'require_min' => 6,
						),
				),
				'bank_name' => array(
						'type' => 'Text',
						'attributes' => array(
								'require' => false,
								'require_comment' => array(
										'text_error_input_text_min'
								),
								'require_min' => 2,
						),
				),
				'bank_country_select' => array(
						'type' => 'Select',
						'options' => array(
								'empty_option' => '',
								'value_options' => $bank_country,
						),
						'attributes' => array(
								'require' => false,
								'require_comment' => array(
										'text_error_input_select'
								)
						),
				),
				'bank_state_select' => array(
						'type' => 'Select',
						'options' => array(
								'empty_option' => '',
								'value_options' => $bank_state,
						),
						'attributes' => array(
								'disabled' => true,
								'require' => false,
								'require_comment' => array(
										'text_error_input_select'
								)
						),
				),
				'bank_state' => array(
						'type' => 'Text',
						'attributes' => array(
								'not_show_label' => true,
								'is_hidden' => (isset($_POST['bank_state_select']) && $_POST['bank_state_select'] == "0" ? false : (isset($_POST['bank_state_select']) && $_POST['bank_state_select'] >= 0 ? true : ($bank_country > 1 ? true : false))),
								'require' => false,
								'require_comment' => array(
										'text_error_input_text_min'
								),
								'require_min' => 2,
						)
				),
				'bank_branch_select' => array(
						'type' => 'Select',
						'options' => array(
								'empty_option' => '',
								'value_options' => $bank_branch,
						),
						'attributes' => array(
								'disabled' => true,
								'require' => false,
								'require_comment' => array(
										'text_error_input_select'
								)
						),
				),
				'bank_branch' => array(
						'type' => 'Text',
						'attributes' => array(
								'not_show_label' => true,
								'is_hidden' => (isset($_POST['bank_branch_select']) && $_POST['bank_branch_select'] == "0" ? false : (isset($_POST['bank_branch_select']) && $_POST['bank_branch_select'] >= 0 ? true : ($bank_country > 1 ? true : false))),
								'require' => false,
								'require_comment' => array(
										'text_error_input_text_min'
								),
								'require_min' => 2,
						),
				),
				'bankseperator' => array(
						'type' => 'Seperator',
						'attributes' => array(
								'class' => 'seperator'
						)
				),
				'captcha' => array(
						'type' => 'Captcha',
						'attributes' => array(
								'size' => '7',
								'maxlength' => '6',
								'require' => true,
								'require_comment' => array(
										'text_error_input_text_min',
										'text_error_input_not_match'
								),
								'require_min' => 6,
						),
				),
				'captchaseperator' => array(
						'type' => 'Seperator',
						'attributes' => array(
								'class' => 'seperator'
						)
				),
				'action' => array(
						'type' => 'Submit',
						'attributes' => array(
								'value' => 'Register',
								'class' => 'button green'
						)
				)
		);
		return $field;
	}
}
