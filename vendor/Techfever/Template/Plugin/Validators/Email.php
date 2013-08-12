<?php

namespace Techfever\Template\Plugin\Validators;

use Zend\Validator\AbstractValidator;

class Email extends AbstractValidator {
	const INVALID = 'emailInvalid';
	const INCORRECT = 'emailIncorrect';
	const DNS_INCORRECT = 'emailDNSIncorrect';
	const USERNAME_INCORRECT = 'emailUsernameIncorrect';
	const DOMAIN_INCORRECT = 'emailDomainIncorrect';
	const LENGTH_EXCEEDED = 'emailLengthExceeded';

	/**
	 * @var array
	 */
	protected $messageTemplates = array(
			self::INVALID => "text_error_invalid_value_type",
			self::INCORRECT => "text_error_invalid_email",
			self::DNS_INCORRECT => "text_error_invalid_email_dns",
			self::USERNAME_INCORRECT => "text_error_invalid_email_username",
			self::DOMAIN_INCORRECT => "text_error_invalid_email_domain",
			self::LENGTH_EXCEEDED => "text_error_invalid_email_length",
	);

	/**
	 * @var array
	 */
	protected $messageVariables = array();

	/**
	 * Internal options array
	 */
	protected $options = array();

	/**	 *
	 * @param array|\Traversable $options OPTIONAL
	 */
	public function __construct($options = null) {
		if (!is_array($options)) {
			$options = func_get_args();
		}

		parent::__construct($options);
	}

	/**
	 * Defined by Zend\Validator\ValidatorInterface
	 *
	 * Returns true if and only if $value is a valid email address
	 * according to RFC2822
	 *
	 * @link   http://www.ietf.org/rfc/rfc2822.txt RFC2822
	 * @link   http://www.columbia.edu/kermit/ascii.html US-ASCII characters
	 * @param  string $value
	 * @return bool
	 */
	public function isValid($value) {
		if (!is_string($value)) {
			$this->error(self::INVALID);
			return false;
		}
		$this->setValue($value);

		$atIndex = strrpos($value, "@");
		if (is_bool($atIndex) && !$atIndex) {
			$this->error(self::INCORRECT);
			return false;
		} else {
			$domain = substr($value, $atIndex + 1);
			$local = substr($value, 0, $atIndex);
			$localLen = strlen($local);
			$domainLen = strlen($domain);
			if ($localLen < 1 || $localLen > 64) {
				$this->error(self::LENGTH_EXCEEDED);
				return false;
			} else if ($domainLen < 1 || $domainLen > 255) {
				$this->error(self::DOMAIN_INCORRECT);
				return false;
			} else if ($local[0] == '.' || $local[$localLen - 1] == '.') {
				$this->error(self::USERNAME_INCORRECT);
				return false;
			} else if (preg_match('/\\.\\./', $local)) {
				$this->error(self::USERNAME_INCORRECT);
				return false;
			} else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
				$this->error(self::DOMAIN_INCORRECT);
				return false;
			} else if (preg_match('/\\.\\./', $domain)) {
				$this->error(self::DOMAIN_INCORRECT);
				return false;
			} else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\", "", $local))) {
				// character not valid in local part unless
				// local part is quoted
				if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\", "", $local))) {
					$this->error(self::USERNAME_INCORRECT);
					return false;
				}
			}
			/*
			if ($isValid && !(checkdnsrr($domain, "MX") || ↪checkdnsrr($domain,"A"))) {
			    $this->error(self::DNS_INCORRECT);
			    return false;
			}
			 */
		}
		return true;
	}
}
