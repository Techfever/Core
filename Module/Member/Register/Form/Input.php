<?php
namespace Module\Member\Register\Form;

use Zend\Form\Form;
use Zend\Captcha;
use Kernel\Exception;

class Input extends Form {
	public function __construct() {
		parent::__construct ();
		$this->setAttribute ( 'method', 'post' );
		$this->add ( array (
					'name' => 'profile_designation',
					'type' => 'Select',
		            'options' => array(
		                     'label' => 'text_designation',
		                     'empty_option' => 'text_form_select',
		                     'value_options' => array(
		                             '0' => 'French',
		                             '1' => 'English',
		                             '2' => 'Japanese',
		                             '3' => 'Chinese',
		                     ),
		             )
			) ); 
		$this->add ( array (
					'name' => 'profile_fullname',
					'type' => 'Text',
					'options' => array (
							'label' => 'text_fullname'
					)
			) ); 
		$this->add ( array (
					'name' => 'profile_firstname',
					'type' => 'Text',
					'options' => array (
							'label' => 'text_firstname'
					)
			) ); 
		$this->add ( array (
					'name' => 'profile_lastname',
					'type' => 'Text',
					'options' => array (
							'label' => 'text_lastname'
					)
			) ); 
		$this->add ( array (
					'name' => 'profile_nric_passport',
					'type' => 'Text',
					'options' => array (
							'label' => 'text_nric_passport'
					)
			) ); 
		$this->add ( array (
					'name' => 'profile_gender',
					'type' => 'Radio',
		            'options' => array(
		                     'label' => 'text_gender',
		                     'empty_option' => 'text_form_select',
		                     'value_options' => array(
		                             '1' => 'Male',
		                             '2' => 'Female'
		                     ),
		             )
			) ); 
		$this->add ( array (
					'name' => 'profile_dob',
					'type' => 'Date',
					'options' => array (
							'label' => 'text_dob'
					),
				     'attributes' => array(
				             'min' => '2012-01-01',
				             'max' => '2020-01-01',
				             'step' => '1', // days; default step interval is 1 day
				     )
			) ); 
		$this->add ( array (
					'name' => 'profile_nationality',
					'type' => 'Select',
					'options' => array (
							'label' => 'text_nationality',
		                    'empty_option' => 'text_form_select',
		                    'value_options' => array(
		                            '1' => 'Malaysia',
		                            '2' => 'China'
		                    ),
					)
			) ); 
		$this->add ( array (
					'name' => 'profile_email_address',
					'type' => 'Text',
					'options' => array (
							'label' => 'text_email_address'
					)
			) ); 
		$this->add ( array (
					'name' => 'profile_mobile_no',
					'type' => 'Text',
					'options' => array (
							'label' => 'text_mobile_no'
					)
			) ); 
		$this->add ( array (
					'name' => 'profile_telephone_no',
					'type' => 'Text',
					'options' => array (
							'label' => 'text_telephone_no'
					)
			) ); 
		$this->add ( array (
					'name' => 'profile_office_no',
					'type' => 'Text',
					'options' => array (
							'label' => 'text_office_no'
					)
			) ); 
		$this->add ( array (
					'name' => 'profile_fax_no',
					'type' => 'Text',
					'options' => array (
							'label' => 'text_fax_no'
					)
			) ); 
		$this->add ( array (
					'name' => 'profileseperator',
					'type' => 'Seperator',
					'attributes' => array (
							'class' => 'line2'
					)
			) ); 
		$this->add ( array (
					'name' => 'register_captcha',
					'type' => 'Captcha',
				     'options' => array(
				             'label' => 'text_captcha',
				             'captcha' => new Captcha\Image(array(
				                'font' => KERNEL_PATH . '/Font/arial.ttf',
				                'width' => 180,
				                'height' => 70,
				                'dotNoiseLevel' => 40,
				                'lineNoiseLevel' => 3,
				             	'ImgDir' => CORE_PATH . '/Data/Captcha',
				             	'ImgUrl' => $_SERVER['HTTP_REFERER'] . 'Image/Captcha'
							)),
				     ),
			) ); 
		$this->add ( array (
					'name' => 'buttonseperator',
					'type' => 'Seperator',
					'attributes' => array (
							'class' => 'line2'
					)
			) ); 
		$this->add ( array (
				'name' => 'subaction',
				'type' => 'Hidden' ,
				'attributes' => array (
						'value' => 'submit'
				)
		) );
		$this->add ( array (
				'name' => 'action',
				'type' => 'Submit',
				'attributes' => array (
						'value' => 'Register'
				)
		) );
	}
}