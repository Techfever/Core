<?php

namespace Techfever\Template\Plugin\Validators;

use Techfever\Exception;
use Zend\Stdlib\StringUtils;
use Zend\Stdlib\StringWrapper\StringWrapperInterface as StringWrapper;
use Zend\Validator\AbstractValidator;
use Techfever\Address\Country;
use Zend\ServiceManager\ServiceLocatorInterface;

class PostalCode extends AbstractValidator {
	/**
	 * @var ServiceLocator
	 */
	private $serviceLocator = null;

	const INVALID = 'postalCodeInvalid';
	const COUNTRY_INCORRECT = 'postalCodeCountryIncorrect';
	const LENGTH_MIN = 'postalCodeLengthMin';
	const LENGTH_MAX = 'postalCodeLengthMax';

	/**
	 * @var array
	 */
	protected $messageTemplates = array(
			self::INVALID => "text_error_invalid_value_type",
			self::COUNTRY_INCORRECT => "text_error_incorrect_for_country",
			self::LENGTH_MIN => "text_error_characters_min",
			self::LENGTH_MAX => "text_error_characters_max",
	);

	protected $stringWrapper;

	/**
	 * @var array
	 */
	protected $messageVariables = array(
			'country' => array(
					'options' => 'country'
			),
			'min' => array(
					'options' => 'min'
			),
			'max' => array(
					'options' => 'max'
			),
	);

	protected $options = array(
			'min' => 0,
			// Minimum length
			'max' => null,
			'country' => null,
			'allowed' => true
	);

	/**
	 * Postal Code regexes by territory
	 *
	 * @var array
	 */
	protected static $postCodeRegex = array(
			'AFG' => '/^[1-3][0-9]{3}$/',
			'ALB' => '/^(1[0578]|2[05]|3[03-5]|4[03-7]|5[034]|6[034]|7[034]|8[03-7]|9[0347])[0-9]{2}$/',
			'DZA' => '/^[0-9]{5}$/',
			'AND' => '/^AD[1-7]00$/',
			'AGO' => null,
			'AIA' => '/^AI-2640$/',
			'ATG' => null,
			'ARG' => '/^[A-HJ-TP-Z][0-9]{4}[A-Z]{3}$/',
			'ARM' => '/^[0-9]{4}$/',
			'ABW' => null,
			'AUS' => '/^0200$|^08[0-9]{2}$|^0909$|^[1-8][0-9]{3}$|^9726$$/',
			'AUT' => '/^[1-9][0-9]{3}$/',
			'AZE' => '/^AZ [0-9]{4}$/',
			'BHS' => null,
			'BHR' => '/^[1-9][0-2]?[0-9]{2}$/',
			'BGD' => '/^[1-9][0-9]{3}$/',
			'BRB' => '/^BB[12][0-9]{5}$/',
			'BLR' => '/^[2][1-4][09]{4}$/',
			'BEL' => '/^[1-9][0-9]{3}$/',
			'BLZ' => null,
			'BEN' => null,
			'BMU' => '/^[A-Z]{2} ([0-9]{2}|[A-Z]{2})$/',
			'BTN' => '/^[0-9]{5}$/',
			'BOL' => null,
			'BIH' => '/^[0-9]{5}$/',
			'BWA' => null,
			'BRA' => '/^[0-9]{5}(-[0-9]{3})$/',
			'BRN' => '/^[BKTP][A-Z][0-9]{4}$/',
			'BGR' => '/^[1-9][0-9]{3}$/',
			'BFA' => null,
			'BDI' => null,
			'KHM' => '/^[0-9]{5}$/',
			'CMR' => null,
			'CAN' => '/^[A-CEGHJ-NPR-TVXY]\d[A-CEGHJ-NPR-TV-Z] \d[A-CEGHJ-NPR-TV-Z]\d$/',
			'CPV' => '/^[1-8][1-6][0-9]{2}$/',
			'CYM' => '/^KY[123]-[0-9]{4}$/',
			'CAF' => null,
			'TCD' => null,
			'CHL' => '/^[1-9][0-9]{6}$/',
			'CHN' => '/^[0-9]{4}(00|22)$/',
			'CXR' => '/^[0-9]{4}$/',
			'CCK' => '/^[0-9]{4}$/',
			'COL' => null,
			'COM' => null,
			'COG' => null,
			'COK' => '/^[0-9]{4}$/',
			'CRI' => '/^([0-9]*-)?[0-9]{5}$/',
			'CIV' => null,
			'HRV' => '/^HR-(10|2[0-3]|3[1-5]|4[02-47-9]|5[1-3])[0-9]{3}$/',
			'CYP' => '/^CY-[1-9][0-9]{3}$/',
			'CZE' => '/^[1-9][0-9]{2} [0-9]{3}$/',
			'DNK' => '/^DK-[1-5][0-9]{3}$/',
			'DJI' => null,
			'DMA' => null,
			'DOM' => '/^[0-9]{5}$/',
			'TMP' => null,
			'ECU' => '/^[A-Z][0-9]{4}[A-Z]$/',
			'EGY' => '/^[0-9]{5}$/',
			'SLV' => '/^CP [0-9][4}$/',
			'GNQ' => null,
			'ERI' => null,
			'EST' => '/^[0-9]{5}$/',
			'ETH' => '/^[0-9]{4}$/',
			'FLK' => '/^FIQQ 1ZZ$/',
			'FRO' => '/^FO( |-)?[1-9][0-9]{2}$/',
			'FJI' => null,
			'FIN' => '/^FI-([0-9]{4}[01]|99999)$/',
			'FRA' => '/^(2[A|B])|[0-9]{2})[0-9]{3}$/',
			'GUF' => '/^9(7|8)3[0-9]{2}$/',
			'PYF' => '/^9(7|8)7[0-9]{2}$/',
			'GAB' => '/^[0-9]{2} .* [0-9]{2}$/',
			'GMB' => null,
			'GEO' => '/^[0-9]{4}$/',
			'DEU' => '/^[0-9]{6}$/',
			'GHA' => null,
			'GIB' => '/^GX11 1AA$/',
			'GRC' => '/^[1-8][0-9]{2} [0-9]{2}$/',
			'GRL' => '/^39[0-8][0-59]$/',
			'GRD' => null,
			'GLP' => '/^9[78][01][0-9]{2}$/',
			'GTM' => '/^[0-9]{5}$/',
			'GIN' => '/^[0-4][0-9]{2} BP [0-9]{3}$/',
			'GNB' => '/^[1-9][0-9]{3}$/',
			'GUY' => null,
			'HTI' => '/^[0-9]{4}$/',
			'HND' => '/^[1-5][1-6][1-3]0[1-4]$/',
			'HKG' => null,
			'HUN' => '/^[0-9]{4}$/',
			'ISL' => '/^[1-9][0-9]{2}$/',
			'IND' => '/^(1[1-9]|2[0-8]|3[0-46-9]|4[0-9]|5[0-36-9]|6[0-47-9]|70-9]|8[0-5])[0-9]{4}$/',
			'IDN' => '/^[0-9]{5}$/',
			'IRQ' => '/^[3-6][0-2468]0[1-9]{2}$/',
			'IRL' => null,
			'ISR' => '/^[0-9]{5}$/',
			'ITA' => '/^[0-9]{5}$/',
			'JAM' => null,
			'JPN' => '/^[0-9]{3}-[0-9]{4}$/',
			'JOR' => '/^[0-9]{5}$/',
			'KAZ' => '/^[0-9]{5}$/',
			'KEN' => '/^[0-9]{5}$/',
			'KIR' => '/^[0-9]{3}-[0-9]{3}$/',
			'KOR' => '/^[0-9]{3}-[0-9]{3}$/',
			'KWT' => '/^[0-9]{5}$/',
			'KGZ' => '/^[0-9]{6}$/',
			'LAO' => '/^[0-9]{5}$/',
			'LVA' => '/^LV-[0-9]{4}$/',
			'LBN' => '/^[0-9]{4}( [0-9]{4})?$/',
			'LSO' => '/^[0-9]{3}$/',
			'LBR' => '/^[0-9]{3}$/',
			'LBY' => null,
			'LIE' => '/^[1-9][0-9]{3}$/',
			'LTU' => '/^LT-[0-9]{5}$/',
			'LUX' => '/^[0-9]{4}$/',
			'MAC' => null,
			'MKD' => '/^[0-9]{4}$/',
			'MDG' => '/^[0-9]{3}$/',
			'MWI' => null,
			'MYS' => '/^[0-9]{5}$/',
			'MDV' => '/^22000$|^23000$|^[0-2][0-9]{4}$/',
			'MLI' => null,
			'MLT' => '/^[A-Z] [0-9]{4}$/',
			'MTQ' => '/^9(7|8)3[0-9]{2}$/',
			'MRT' => null,
			'MUS' => '/^([0-9]{3}[A-Z]{2}[0-9]{3})?$/',
			'MYT' => '/^9(7|8)6[0-9]{2}$/',
			'MEX' => '/^(0[0-9]|1[013-6]|2[0-9]|3[0-35-9]|4[2-8]|5[0-9]|6[04-6-8]|7[0-25-9]|8[0-35-9]|9[013478])[0-9]{3}$/',
			'MDA' => '/^MD-[0-9]{4}$/',
			'MCO' => '/^(2[A|B])|[0-9]{2})[0-9]{3}$/',
			'MNG' => '/^[0-9]{5}$/',
			'MSR' => null,
			'MAR' => '/^[0-9]{5}$/',
			'MOZ' => '/^[0-9]{4}$/',
			'MMR' => '/^[01][0-9]{4}$/',
			'NAM' => null,
			'NRU' => null,
			'NPL' => '/^[0-9]{5}$/',
			'NLD' => '/^[1-9][0-9]{3} [A-Z]{2}$/',
			'NCL' => '/^9(7|8)8[0-9]{2}$/',
			'NZL' => '/^[0-9]{4}$/',
			'NIC' => '/^([0-9]{4}-)?[0-9]{3}-[0-9]{3}-[0-9]{2}$/',
			'NER' => '/^[0-9]{4}$/',
			'NGA' => '/^[0-9]{6}$/',
			'NIU' => null,
			'NFK' => '/^0200$|^08[0-9]{2}$|^0909$|^[1-8][0-9]{3}$|^9726$$/',
			'NOR' => '/^[0-9]{4}$/',
			'OMN' => '/^[0-9]{3}$/',
			'PAK' => '/^-?[0-9]{5}$/',
			'PAN' => null,
			'PNG' => '/^[0-9]{3}$/',
			'PRY' => '/^[1-9][0-9]{3}$/',
			'PER' => '/^([A-Z][0-9]{4})?$|^([1-9][0-9]?)?$/',
			'PHL' => '/^[0-9]{4}$/',
			'PCN' => '/^PCRN 1ZZ$/',
			'POL' => '/^[0-9]{2}-[0-9]{3}$/',
			'PRT' => '/^[0-9]{4}-[0-9]{3}$/',
			'QAT' => null,
			'REU' => '/^974[0-9]{2}$/',
			'ROM' => '/^[0-9]{6}$/',
			'RUS' => '/^[0-9]{3} [0-9]{3}$/',
			'RWA' => null,
			'KNA' => null,
			'LCA' => null,
			'VCT' => '/^vc[0-9]{4}$/',
			'WSM' => null,
			'SMR' => '/^4789[0-9]$/',
			'STP' => null,
			'SAU' => '/^[0-9]{5}(-[0-9]{4})?$/',
			'SEN' => '/^[0-9]{5}$/',
			'SYC' => null,
			'SLE' => null,
			'SGP' => '/^[0-9]{6}$/',
			'SVK' => '/^[0-9]{5}$/',
			'SVN' => '/^SI-[0-9]{4}$/',
			'SLB' => null,
			'SOM' => '/^(AD|B[KNRY]|G[GD]|HR|J[DH]|MD|NG|S[GDHL]|TG|WG) [0-9]{5}$/',
			'ZAF' => '/^[0-9]{4}$/',
			'SGS' => '/^SIQQ 1ZZ$/',
			'ESP' => '/^[0-9]{5}$/',
			'LKA' => '/^[0-9]{5}$/',
			'SHN' => '/^STHL 1ZZ$/',
			'SPM' => '/^9(7|8)5[0-9]{2}$/',
			'SUR' => null,
			'SWZ' => '/^[HMSL][1-4][1-2][0-9]$/',
			'SWE' => '/^SE-[0-9]{3} [0-9]{2}$/',
			'CHE' => '/^[1-9][0-9]{3}$/',
			'SYR' => '/^([0-9]{4})?$/',
			'TWN' => '/^[0-9]{3}([0-9]{2})?$/',
			'TJK' => '/^[0-9]{6}$/',
			'TZA' => null,
			'THA' => '/^[0-9]{5}$/',
			'TGO' => null,
			'TKL' => null,
			'TON' => null,
			'TTO' => null,
			'TUN' => '/^[0-9]{4}$/',
			'TUR' => '/^[0-9]{5}$/',
			'TKM' => '/^[0-9]{6}$/',
			'TCA' => '/^TKCA 1ZZ$/',
			'TUV' => null,
			'UGA' => null,
			'UKR' => '/^[0-9]{5}$/',
			'ARE' => null,
			'GBR' => '/^^GIR 0AA$|^[A-PR-UWZ]([0-9]{1,2}|[A-HK-Y][0-9]{1,2}|[0-9][A-HJKS-UW]||[A-HK-Y][0-9][ABEHMNPRV-Y]) [0-9][ABD-HJLNP-UW-Z]{2}$$/',
			'USA' => '/^[0-9]{5}(-[0-9]{4})?$/',
			'URY' => '/^[0-9]{5}$/',
			'UZB' => '/^[0-9]{6}$/',
			'VUT' => null,
			'VAT' => '/^00120$/',
			'VEN' => '/^[0-9]{4}$/',
			'VNM' => '/^[0-9]{6}$/',
			'VGB' => '/^VG1110$/',
			'WLF' => '/^9861[1-3]$/',
			'YEM' => null,
			'YUG' => '/^[0-9]{5}$/',
			'ZAR' => null,
			'ZMB' => '/^[0-9]{5}$/',
			'ZWE' => null,
			'ALA' => '/^AX-22( )?[0-9]{2}[01]$/',
			'ASC' => '/^ASCN 1ZZ$/',
			'BES' => null,
			'BES' => null,
			'BES' => null,
			'CUW' => null,
			'GGY' => '/^GY[1-9](0)? [0-9][ABD-HJLNP-UW-Z]{2}$$/',
			'IMN' => '/^IM[1-9]([679])?  [0-9][ABD-HJLNP-UW-Z]{2}$$/',
			'JEY' => '/^JE[1-4] [0-9][ABD-HJLNP-UW-Z]{2}$$/',
			'KSV' => '/^[1-7]00[0-9]{2}$/',
			'MNE' => '/^[0-9]{5}$/',
			'BLM' => '/^9[78][01][0-9]{2}$/',
			'MAF' => '/^9[78][01][0-9]{2}$/',
			'SXM' => null,
			'TAA' => '/^TDCU 1ZZ$/',
	);

	/**
	 * Sets validator options
	 *
	 * @param  int|array|\Traversable $options
	 */
	public function __construct($options = null) {
		if (!is_array($options)) {
			$options = func_get_args();

			$temp['min'] = array_shift($options);
			if (!empty($options)) {
				$temp['max'] = array_shift($options);
			}

			if (!empty($options)) {
				$temp['country'] = array_shift($options);
			}

			if (!empty($options)) {
				$temp['allowed'] = array_shift($options);
			}

			$options = $temp;
		} else {
			$options = array_merge($this->options, $options);
		}
		if (!isset($options['servicelocator'])) {
			throw new Exception\RuntimeException('ServiceLocator has not been set or configured.');
		}
		$this->setServiceLocator($options['servicelocator']);
		unset($options['servicelocator']);
		$this->options = $options;

		if (array_key_exists('country', $options)) {
			$this->setCountry($options['country']);
		}

		if (array_key_exists('allowed', $options)) {
			$this->allowed($options['allowed']);
		}

		parent::__construct($options);
	}

	/**
	 * Set serviceManager instance
	 *
	 * @param  ServiceLocatorInterface $serviceLocator
	 * @return void
	 */
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
		$this->serviceLocator = $serviceLocator;
	}

	/**
	 * Retrieve serviceManager instance
	 *
	 * @return ServiceLocatorInterface
	 */
	public function getServiceLocator() {
		return $this->serviceLocator;
	}

	/**
	 * Get Country
	 *
	 * @return string
	 */
	public function getCountry() {
		return $this->options['country'];
	}

	/**
	 * Set Country
	 *
	 * @param  string $country
	 * @return self
	 */
	public function setCountry($country) {
		$this->options['country'] = $country;

		return $this;
	}

	/**
	 * Returns the min option
	 *
	 * @return int
	 */
	public function getMin() {
		return $this->options['min'];
	}

	/**
	 * Sets the min option
	 *
	 * @param  int $min
	 * @throws Exception\InvalidArgumentException
	 * @return text Provides a fluent interface
	 */
	public function setMin($min) {
		if (null !== $this->getMax() && $min > $this->getMax()) {
			throw new Exception\InvalidArgumentException("The minimum must be less than or equal to the maximum length, but $min >" . " " . $this->getMax());
		}

		$this->options['min'] = max(0, (int) $min);
		return $this;
	}

	/**
	 * Returns the max option
	 *
	 * @return int|null
	 */
	public function getMax() {
		return $this->options['max'];
	}

	/**
	 * Sets the max option
	 *
	 * @param  int|null $max
	 * @throws Exception\InvalidArgumentException
	 * @return text Provides a fluent interface
	 */
	public function setMax($max) {
		if (null === $max) {
			$this->options['max'] = null;
		} elseif ($max < $this->getMin()) {
			throw new Exception\InvalidArgumentException("The maximum must be greater than or equal to the minimum length, but " . "$max < " . $this->getMin());
		} else {
			$this->options['max'] = (int) $max;
		}

		return $this;
	}

	/**
	 * Get the string wrapper to detect the string length
	 *
	 * @return StringWrapper
	 */
	public function getStringWrapper() {
		if (!$this->stringWrapper) {
			$this->stringWrapper = StringUtils::getWrapper();
		}
		return $this->stringWrapper;
	}

	/**
	 * Set the string wrapper to detect the string length
	 *
	 * @param StringWrapper $stringWrapper
	 * @return text
	 */
	public function setStringWrapper(StringWrapper $stringWrapper) {
		$this->stringWrapper = $stringWrapper;
	}
	/**
	 * Allow Possible
	 *
	 * @param  bool|null $possible
	 * @return self|bool
	 */
	public function allowed($allowed = null) {
		if (null !== $allowed) {
			$this->options['allowed'] = (bool) $allowed;

			return $this;
		}

		return $this->options['allowed'];
	}

	/**
	 * @param  string $value
	 * @return bool
	 */
	public function isValid($value) {
		if (!is_scalar($value)) {
			$this->error(self::INVALID);

			return false;
		}
		$this->setValue($value);

		if (strlen($this->getValue()) > 0) {
			$length = $this->getStringWrapper()->strlen($value);
			if ($length < $this->getMin()) {
				$this->error(self::LENGTH_MIN);
				return false;
			}

			if (null !== $this->getMax() && $this->getMax() < $length) {
				$this->error(self::LENGTH_MAX);
				return false;
			}
			if (!$this->allowed()) {
				$Country = new Country(array(
						'country' => $this->options['country'],
						'servicelocator' => $this->getServiceLocator(),
				));
				$regex = $Country->postcodeRegex();
				if (strlen($regex) > 0 && !preg_match($regex, $value)) {
					$country_name = $Country->getCountryName($this->options['country']);
					$this->setCountry($country_name);
					$this->error(self::COUNTRY_INCORRECT);
					return false;
				}
			}
		}
		return true;
	}
}
