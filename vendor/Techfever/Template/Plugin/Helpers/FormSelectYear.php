<?php

namespace Techfever\Template\Plugin\Helpers;

use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\AbstractHelper;
use Techfever\Template\Plugin\Forms\SelectYear as SelectYearElement;
use Techfever\Exception;

class FormSelectYear extends AbstractHelper {
	/**
	 * FormSelect helper
	 *
	 * @var FormSelect
	 */
	protected $selectHelper;
	
	/**
	 *
	 * @throws Exception\ExtensionNotLoadedException if ext/intl is not present
	 */
	public function __construct() {
		if (! extension_loaded ( 'intl' )) {
			throw new Exception\ExtensionNotLoadedException ( sprintf ( '%s component requires the intl PHP extension', __NAMESPACE__ ) );
		}
	}
	
	/**
	 * Invoke helper as function
	 *
	 * Proxies to {@link render()}.
	 *
	 * @param ElementInterface $element        	
	 * @return FormSelectDate
	 */
	public function __invoke(ElementInterface $element = null) {
		if (! $element) {
			return $this;
		}
		
		return $this->render ( $element );
	}
	
	/**
	 * Render a year element that is composed of two selects
	 *
	 * @param \Zend\Form\ElementInterface $element        	
	 * @throws \Zend\Form\Exception\InvalidArgumentException
	 * @throws \Zend\Form\Exception\DomainException
	 * @return string
	 */
	public function render(ElementInterface $element) {
		if (! $element instanceof SelectYearElement) {
			throw new Exception\InvalidArgumentException ( sprintf ( '%s requires that the element is of type Zend\Form\Element\SelectYear', __METHOD__ ) );
		}
		
		$name = $element->getName ();
		if ($name === null || $name === '') {
			throw new Exception\DomainException ( sprintf ( '%s requires that the element has an assigned name; none discovered', __METHOD__ ) );
		}
		
		$selectHelper = $this->getSelectElementHelper ();
		
		$yearOptions = $this->getYearsOptions ( $element->getMinYear (), $element->getMaxYear () );
		
		$yearElement = $element->getYearElement ()->setValueOptions ( $yearOptions );
		
		if ($element->shouldCreateEmptyOption ()) {
			$yearElement->setEmptyOption ( '' );
		}
		
		return $selectHelper->render ( $yearElement );
	}
	
	/**
	 * Create a key => value options for years
	 * read date for users, so we only use four digits years
	 *
	 * @param int $minYear        	
	 * @param int $maxYear        	
	 * @return array
	 */
	protected function getYearsOptions($minYear, $maxYear) {
		$result = array ();
		for($i = $maxYear; $i >= $minYear; -- $i) {
			$result [$i] = $i;
		}
		
		return $result;
	}
	
	/**
	 * Retrieve the FormSelect helper
	 *
	 * @return FormSelect
	 */
	protected function getSelectElementHelper() {
		if ($this->selectHelper) {
			return $this->selectHelper;
		}
		
		if (method_exists ( $this->view, 'plugin' )) {
			$this->selectHelper = $this->view->plugin ( 'formselect' );
		}
		
		return $this->selectHelper;
	}
}
