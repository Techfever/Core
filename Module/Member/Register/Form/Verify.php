<?php
namespace Module\Member\Register\Form;

// Add these import statements
use Zend\Stdlib\DateTime;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Kernel\Exception;

class Verify implements InputFilterAwareInterface {
	public $id;
	public $title;
	public $description;
	public $created_by;
	public $created_date;
	public $modified_by;
	public $modified_date;
	protected $inputFilter; // <-- Add this variable
	public function exchangeArray($data) {
		if (isset($data['submit'])) {
			$this->id = (isset($data['id'])) ? $data['id'] : null;
			$this->title = (isset($data['title'])) ? $data['title'] : null;
			$this->description = (isset($data['description'])) ? $data['description'] : null;
		} else {
			$created_date = new DateTime($data['content_created_date']);
			$modified_date = new DateTime($data['content_modified_date']);
			$this->id = (isset($data['content_id'])) ? $data['content_id'] : null;
			$this->title = (isset($data['content_title'])) ? $data['content_title'] : null;
			$this->description = (isset($data['content_description'])) ? $data['content_description'] : null;
			$this->created_by = (isset($data['content_created_by'])) ? $data['content_created_by'] : null;
			$this->created_date = (isset($data['content_created_date'])) ? $created_date->format('H:i:s d-m-Y') : null;
			$this->modified_by = (isset($data['content_modified_by'])) ? $data['content_modified_by'] : null;
			$this->modified_date = (isset($data['content_modified_date'])) ? $modified_date->format('H:i:s d-m-Y') : null;
		}
	}
	public function getArrayCopy() {
		return get_object_vars($this);
	}

	// Add content to these methods:
	public function setInputFilter(InputFilterInterface $inputFilter) {
		throw new Exception\RuntimeException('Not used');
	}
	public function getInputFilter() {
		if (!$this->inputFilter) {
			$inputFilter = new InputFilter();
			$factory = new InputFactory();

			$inputFilter->add($factory->createInput(array(
								'name' => 'id', 'required' => true, 'filters' => array(
									array(
										'name' => 'Int'
									)
								)
							)));

			$inputFilter
					->add(
							$factory
									->createInput(
											array(
													'name' => 'title',
													'required' => true,
													'filters' => array(
														array(
															'name' => 'StripTags'
														), array(
															'name' => 'StringTrim'
														)
													),
													'validators' => array(
														array(
															'name' => 'StringLength', 'options' => array(
																'encoding' => 'UTF-8', 'min' => 1, 'max' => 100
															)
														)
													)
											)));

			$inputFilter
					->add(
							$factory
									->createInput(
											array(
													'name' => 'description',
													'required' => true,
													'filters' => array(
														array(
															'name' => 'StripTags'
														), array(
															'name' => 'StringTrim'
														)
													),
													'validators' => array(
														array(
															'name' => 'StringLength', 'options' => array(
																'encoding' => 'UTF-8', 'min' => 1, 'max' => 100
															)
														)
													)
											)));

			$this->inputFilter = $inputFilter;
		}

		return $this->inputFilter;
	}
}
