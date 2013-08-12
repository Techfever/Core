<?php

namespace Techfever\Template\Plugin\Helpers;

use DateTime;
use Locale;
use IntlDateFormatter;
use Zend\Form\ElementInterface;
use Techfever\Template\Plugin\Forms\SelectMonth as SelectMonthElement;
use Techfever\Template\Plugin\Helpers\FormSelectYear as FormSelectYearHelper;
use Techfever\Exception;

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
		$keyFormatter = new IntlDateFormatter(Locale::getDefault(), null, null, null, null, 'MM');
		$valueFormatter = new IntlDateFormatter(Locale::getDefault(), null, null, null, null, 'MMMM');
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
