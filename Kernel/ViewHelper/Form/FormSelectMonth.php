<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Kernel\ViewHelper\Form;

use DateTime;
use IntlDateFormatter;
use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\AbstractHelper;
use Kernel\Form\Element\SelectMonth as SelectMonthElement;
use Kernel\ViewHelper\Form\FormSelectYear as FormSelectYearHelper;
use Kernel\Exception;
use Kernel\ServiceLocator;

class FormSelectMonth extends FormSelectYearHelper {

	/**
	 * Render a month element that is composed of two selects
	 *
	 * @param  \Zend\Form\ElementInterface $element
	 * @throws \Zend\Form\Exception\InvalidArgumentException
	 * @throws \Zend\Form\Exception\DomainException
	 * @return string
	 */
	public function render(ElementInterface $element) {
		if (!$element instanceof SelectMonthElement) {
			throw new Exception\InvalidArgumentException(sprintf('%s requires that the element is of type Zend\Form\Element\SelectMonth', __METHOD__));
		}

		$name = $element->getName();
		if ($name === null || $name === '') {
			throw new Exception\DomainException(sprintf('%s requires that the element has an assigned name; none discovered', __METHOD__));
		}

		$selectHelper = $this->getSelectElementHelper();

		$monthsOptions = $this->getMonthsOptions();
		$yearOptions = $this->getYearsOptions($element->getMinYear(), $element->getMaxYear());

		$monthElement = $element->getMonthElement()->setValueOptions($monthsOptions);
		$yearElement = $element->getYearElement()->setValueOptions($yearOptions);

		if ($element->shouldCreateEmptyOption()) {
			$monthElement->setEmptyOption('');
			$yearElement->setEmptyOption('');
		}

		return $selectHelper->render($monthElement) . '-' . $selectHelper->render($yearElement);
	}

	/**
	 * Create a key => value options for months
	 *
	 * @return array
	 */
	protected function getMonthsOptions() {
		$Translator = ServiceLocator::getServiceManager('Translator');
		$keyFormatter = new IntlDateFormatter($Translator->getLocale(), null, null, null, null, 'MM');
		$valueFormatter = new IntlDateFormatter($Translator->getLocale(), null, null, null, null, 'MMMM');
		$date = new DateTime('1970-01-01');

		$result = array();
		for ($month = 1; $month <= 12; $month++) {
			$key = $keyFormatter->format($date->getTimestamp());
			$value = $valueFormatter->format($date->getTimestamp());
			$result[$key] = $value;

			$date->modify('+1 month');
		}

		return $result;
	}
}
