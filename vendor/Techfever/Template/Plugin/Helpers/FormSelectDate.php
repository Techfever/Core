<?php

namespace Techfever\Template\Plugin\Helpers;

use DateTime;
use IntlDateFormatter;
use Zend\Form\ElementInterface;
use Techfever\Template\Plugin\Forms\SelectDate as SelectDateElement;
use Techfever\Template\Plugin\Helpers\FormSelectMonth as FormSelectMonthHelper;
use Techfever\Exception;

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

		$dayElement = $element->getDayElement()->setName($name . '[day]');
		$monthElement = $element->getMonthElement()->setName($name . '[month]');
		$yearElement = $element->getYearElement()->setName($name . '[year]');

		$dayElement = $element->getDayElement()->setAttribute('class', 'selectdate');
		$monthElement = $element->getMonthElement()->setAttribute('class', 'selectmonth');
		$yearElement = $element->getYearElement()->setAttribute('class', 'selectyear');

		$dayElement = $element->getDayElement()->setEmptyOption('');
		$monthElement = $element->getMonthElement()->setEmptyOption('');
		$yearElement = $element->getYearElement()->setEmptyOption('');

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
