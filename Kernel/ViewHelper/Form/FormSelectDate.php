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
use Kernel\Form\Element\SelectDate as SelectDateElement;
use Kernel\ViewHelper\Form\FormSelectMonth as FormSelectMonthHelper;
use Kernel\Exception;

class FormSelectDate extends FormSelectMonthHelper {
	/**
	 * Render a date element that is composed of three selects
	 *
	 * @param  ElementInterface $element
	 * @throws \Zend\Form\Exception\InvalidArgumentException
	 * @throws \Zend\Form\Exception\DomainException
	 * @return string
	 */
	public function render(ElementInterface $element) {
		if (!$element instanceof SelectDateElement) {
			throw new Exception\InvalidArgumentException(sprintf('%s requires that the element is of type Zend\Form\Element\SelectDate', __METHOD__));
		}

		$name = $element->getName();
		if ($name === null || $name === '') {
			throw new Exception\DomainException(sprintf('%s requires that the element has an assigned name; none discovered', __METHOD__));
		}

		$selectHelper = $this->getSelectElementHelper();

		$daysOptions = $this->getDaysOptions();
		$monthsOptions = $this->getMonthsOptions();
		$yearOptions = $this->getYearsOptions($element->getMinYear(), $element->getMaxYear());

		$dayElement = $element->getDayElement()->setValueOptions($daysOptions);
		$monthElement = $element->getMonthElement()->setValueOptions($monthsOptions);
		$yearElement = $element->getYearElement()->setValueOptions($yearOptions);

		if ($element->shouldCreateEmptyOption()) {
			$dayElement->setEmptyOption('');
			$yearElement->setEmptyOption('');
			$monthElement->setEmptyOption('');
		}

		return $selectHelper->render($dayElement) . '-' . $selectHelper->render($monthElement) . '-' . $selectHelper->render($yearElement);
	}

	/**
	 * Create a key => value options for days
	 *
	 * @return array
	 */
	protected function getDaysOptions() {
		$keyFormatter = new IntlDateFormatter(null, null, null, null, null, 'dd');
		$valueFormatter = new IntlDateFormatter(null, null, null, null, null, 'dd');
		$date = new DateTime('1970-01-01');

		$result = array();
		for ($day = 1; $day <= 31; $day++) {
			$key = $keyFormatter->format($date->getTimestamp());
			$value = $valueFormatter->format($date->getTimestamp());
			$result[$key] = $value;

			$date->modify('+1 day');
		}

		return $result;
	}
}
