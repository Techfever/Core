<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Kernel\Validator\Input;

use Kernel\Exception;
use Zend\Stdlib\StringUtils;
use Zend\Stdlib\StringWrapper\StringWrapperInterface as StringWrapper;
use Zend\Validator\AbstractValidator;

class Select extends AbstractValidator {
	const INVALID = 'selectInvalid';

	/**
	 * @var array
	 */
	protected $messageTemplates = array(
			self::INVALID => "text_error_invalid_value_type",
	);

	/**
	 * @var array
	 */
	protected $messageVariables = array();

	protected $options = array(
			'type' => 'Integer',
			// Minimum length,
			'encoding' => 'UTF-8', // Encoding to use
	);

	protected $stringWrapper;

	/**
	 * Sets validator options
	 *
	 * @param  integer|array|\Traversable $options
	 */
	public function __construct($options = array()) {
		if (!is_array($options)) {
			$options = func_get_args();
			if (!empty($options)) {
				$temp['type'] = array_shift($options);
			}

			if (!empty($options)) {
				$temp['encoding'] = array_shift($options);
			}
			$options = $temp;
		}

		parent::__construct($options);
	}

	/**
	 * Returns the type option
	 *
	 * @return string|null
	 */
	public function getType() {
		return $this->options['type'];
	}

	/**
	 * Sets the type option
	 *
	 * @param  string|null $type
	 * @return Type Provides a fluent interface
	 */
	public function setType($type) {
		if (null === $type) {
			$this->options['type'] = null;
		} else {
			$this->options['type'] = (string) $type;
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
			$this->stringWrapper = StringUtils::getWrapper($this->getEncoding());
		}
		return $this->stringWrapper;
	}

	/**
	 * Set the string wrapper to detect the string length
	 *
	 * @param StringWrapper
	 * @return StringLength
	 */
	public function setStringWrapper(StringWrapper $stringWrapper) {
		$stringWrapper->setEncoding($this->getEncoding());
		$this->stringWrapper = $stringWrapper;
	}

	/**
	 * Returns the actual encoding
	 *
	 * @return string
	 */
	public function getEncoding() {
		return $this->options['encoding'];
	}

	/**
	 * Sets a new encoding to use
	 *
	 * @param string $encoding
	 * @return text
	 * @throws Exception\InvalidArgumentException
	 */
	public function setEncoding($encoding) {
		$this->stringWrapper = StringUtils::getWrapper($encoding);
		$this->options['encoding'] = $encoding;
		return $this;
	}

	/**
	 * Returns true if and only if the string length of $value is at least the min option and
	 * no greater than the max option (when the max option is not null).
	 *
	 * @param  string $value
	 * @return bool
	 */
	public function isValid($value) {
		if (!is_numeric($value) && !is_string($value)) {
			$this->error(self::INVALID);
			return false;
		}
		$this->setValue($value);

		if ($this->getType() == 'Integer' && !is_numeric($value)) {
			$this->error(self::INVALID);
			return false;
		} elseif ($this->getType() == 'String' && is_string($value)) {
			$length = $this->getStringWrapper()->strlen($value);
			if ($length < 1) {
				$this->error(self::INVALID);
				return false;
			}
		}
		return true;
	}
}
