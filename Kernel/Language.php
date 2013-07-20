<?php
namespace Kernel;

use Zend\Validator;
use Zend\Authentication;
use Zend\Captcha;
use Zend\I18n;

class Language {
	/**
	 * @var Locale
	 **/
	private $_locale = null;

	/**
	 * @var Languages
	 **/
	private $_language = null;

	/**
	 * @var Active
	 **/
	private $_active = array();

	/**
	 * @var Has Active
	 **/
	private $_hasactive = false;

	/**
	 * @var Defination
	 **/
	private $_defination = array();

	/**
	 * @var Has Defination
	 **/
	private $_hasdefination = false;

	/**
	 * Constructor
	 */
	public function __construct($locale) {
		$this->_locale = $locale;
		$this->getActive();
	}

	/**
	 * getLanguage
	 */
	public function getLanguage($language) {
		$this->_language = $language;
		return $this->getDefination($this->_language);
	}

	public function hasActive() {
		return $this->_hasactive;
	}

	public function getActive() {
		if (!is_array($this->_active) || count($this->_active) < 1) {
			$DBActive = new Database('select');
			$DBActive->columns(array(
							'id' => 'system_language_id',
							'name' => 'system_language_name',
							'iso' => 'system_language_iso'
					));
			$DBActive->from(array(
							'sl' => 'system_language'
					));
			$DBActive->where(array(
							'sl.system_language_status = 1',
					));
			$DBActive->order(array(
							'system_language_iso ASC'
					));
			$DBActive->setCacheName('system_language');
			$DBActive->execute();
			if ($DBActive->hasResult()) {
				$this->_active = $DBActive->toArray();
				$this->_hasactive = true;
			}
		}
		return $this->_active;
	}

	public function hasDefination() {
		return $this->_hasdefination;
	}

	public function getDefination($locale = null) {
		if ($this->_hasactive && is_array($this->_active) && !empty($locale)) {
			foreach ($this->_active as $active) {
				$id = $active['id'];
				$iso = $active['iso'];
				if ($iso == $locale) {
					$DBDefination = new Database('select');
					$DBDefination->columns(array(
									'id' => 'system_language_defination_id',
									'key' => 'system_language_defination_key',
									'value' => 'system_language_defination_value'
							));
					$DBDefination->from(array(
									'sld' => 'system_language_defination'
							));
					$DBDefination->where(array(
									'sld.system_language_id = ' . $id,
							));
					$DBDefination->order(array(
									'system_language_defination_key ASC'
							));
					$DBDefination->setCacheName('system_language_defination_' . strtolower($locale));
					$DBDefination->execute();
					if ($DBDefination->hasResult()) {
						$data = array();
						while ($DBDefination->valid()) {
							$data[$DBDefination->get('key')] = html_entity_decode(preg_replace("/U\+([0-9A-F]{4})/", "&#x\\1;", $DBDefination->get('value')), ENT_NOQUOTES, 'UTF-8');
							$DBDefination->next();
						}
						$this->_defination[$locale] = $data;
						$this->_hasdefination = true;
					}
				}
			}
		}
		if (!empty($locale)) {
			return $this->_defination[$locale];
		}
		return false;
	}

	public function get($key) {
		return $this->_defination[$this->_locale][$key];
	}

	public function validatorMessages($validator, $field = null) {

		$messages = array();
		$validator = strtolower($validator);
		if (!empty($validator)) {
			switch ($validator) {
				case 'uri':
					$messages = array(
							Validator\Uri::INVALID => "text_error_required",
							Validator\Uri::NOT_URI => "text_error_not_valid_uri",
					);
					break;
				case 'stringlength':
					$messages = array(
							Validator\StringLength::INVALID => "text_error_required",
							Validator\StringLength::TOO_SHORT => "text_error_character_min",
							Validator\StringLength::TOO_LONG => "text_error_character_max",
					);
					break;
				case 'step':
					$messages = array(
							Validator\Step::INVALID => "text_error_required",
							Validator\Step::NOT_STEP => "text_error_not_valid_step",
					);
					break;
				case 'regex':
					$messages = array(
							Validator\Regex::INVALID => "text_error_required",
							Validator\Regex::NOT_MATCH => "text_error_not_match_pattern",
							Validator\Regex::ERROROUS => "text_error_not_match_pattern",
					);
					break;
				case 'numeric':
					$messages = array(
							Validator\Numeric::NOT_NUMERIC => "text_error_numeric_only",
							Validator\Numeric::STRING_EMPTY => "text_error_required",
							Validator\Numeric::TOO_SHORT => "text_error_numeric_min",
							Validator\Numeric::TOO_LONG => "text_error_numeric_max",
					);
					break;
				case 'notempty':
					$messages = array(
							Validator\NotEmpty::IS_EMPTY => "text_error_required",
							Validator\NotEmpty::INVALID => "text_error_invalid",
					);
					break;
				case 'lessthan':
					$messages = array(
							Validator\LessThan::NOT_LESS => "text_error_not_less",
							Validator\LessThan::NOT_LESS_INCLUSIVE => "text_error_not_less_equal",
					);
					break;
				case 'isinstanceof':
					$messages = array(
							Validator\IsInstanceOf::NOT_INSTANCE_OF => "text_error_not_instance_of",
					);
					break;
				case 'isbn':
					$messages = array(
							Validator\Isbn::INVALID => "text_error_required",
							Validator\Isbn::NO_ISBN => "text_error_not_valid_isbn",
					);
					break;
				case 'ip':
					$messages = array(
							Validator\Ip::INVALID => "text_error_required",
							Validator\Ip::NOT_IP_ADDRESS => "text_error_not_valid_ip_address",
					);
					break;
				case 'inarray':
					$messages = array(
							Validator\InArray::NOT_IN_ARRAY => "text_error_not_match_haystack",
					);
					break;
				case 'identical':
					$messages = array(
							Validator\Identical::NOT_SAME => "text_error_not_match",
							Validator\Identical::MISSING_TOKEN => "text_error_not_match_token",
					);
					break;
				case 'hostname':
					$messages = array(
							Validator\Hostname::CANNOT_DECODE_PUNYCODE => "text_error_valid_dns_not_punycode",
							Validator\Hostname::INVALID => "text_error_required",
							Validator\Hostname::INVALID_DASH => "text_error_valid_dns_invalid_position",
							Validator\Hostname::INVALID_HOSTNAME => "text_error_not_valid_dns",
							Validator\Hostname::INVALID_HOSTNAME_SCHEMA => "text_error_valid_dns_not_tld",
							Validator\Hostname::INVALID_LOCAL_NAME => "text_error_not_valid_local_network_name",
							Validator\Hostname::INVALID_URI => "text_error_not_valid_uri_hostname",
							Validator\Hostname::IP_ADDRESS_NOT_ALLOWED => "text_error_valid_ip_not_allow",
							Validator\Hostname::LOCAL_NAME_NOT_ALLOWED => "text_error_valid_local_network_name",
							Validator\Hostname::UNDECIPHERABLE_TLD => "text_error_valid_dns_not_extract_tld",
							Validator\Hostname::UNKNOWN_TLD => "text_error_valid_dns_not_match_tld",
					);
					break;
				case 'hex':
					$messages = array(
							Validator\Hex::INVALID => "text_error_required",
							Validator\Hex::NOT_HEX => "text_error_not_valid_hexadecimal",
					);
					break;
				case 'greaterthan':
					$messages = array(
							Validator\GreaterThan::NOT_GREATER => "text_error_not_greater",
							Validator\GreaterThan::NOT_GREATER_INCLUSIVE => "text_error_not_greater_equal",
					);
					break;
				case 'explode':
					$messages = array(
							Validator\Explode::INVALID => "text_error_invalid",
					);
					break;
				case 'emailaddress':
					$messages = array(
							Validator\EmailAddress::INVALID => "text_error_required",
							Validator\EmailAddress::INVALID_FORMAT => "text_error_not_valid_email_address",
							Validator\EmailAddress::INVALID_HOSTNAME => "text_error_not_valid_email_address_hostname",
							Validator\EmailAddress::INVALID_MX_RECORD => "text_error_not_valid_email_address_mx",
							Validator\EmailAddress::INVALID_SEGMENT => "text_error_not_valid_email_segment",
							Validator\EmailAddress::DOT_ATOM => "text_error_not_valid_email_dot",
							Validator\EmailAddress::QUOTED_STRING => "text_error_not_valid_email_quoted",
							Validator\EmailAddress::INVALID_LOCAL_PART => "text_error_not_valid_email_address_local_part",
							Validator\EmailAddress::LENGTH_EXCEEDED => "text_error_not_valid_length",
					);
					break;
				case 'digits':
					$messages = array(
							Validator\Digits::NOT_DIGITS => "text_error_required",
							Validator\Digits::STRING_EMPTY => "text_error_required",
							Validator\Digits::INVALID => "text_error_invalid",
					);
					break;
				case 'datestep':
					$messages = array(
							Validator\DateStep::NOT_STEP => "text_error_not_valid_step",
					);
					break;
				case 'date':
					$messages = array(
							Validator\Date::INVALID => "text_error_required",
							Validator\Date::INVALID_DATE => "text_error_not_valid_date",
							Validator\Date::FALSEFORMAT => "text_error_not_valid_date_format",
					);
					break;
				case 'csrf':
					$messages = array(
							Validator\Csrf::NOT_SAME => "text_error_not_valid_site",
					);
					break;
				case 'creditcard':
					$messages = array(
							Validator\CreditCard::CHECKSUM => "text_error_not_valid",
							Validator\CreditCard::CONTENT => "text_error_numeric_only",
							Validator\CreditCard::INVALID => "text_error_required",
							Validator\CreditCard::LENGTH => "text_error_not_valid_amount_numeric",
							Validator\CreditCard::PREFIX => "text_error_not_valid_institute",
							Validator\CreditCard::SERVICE => "text_error_not_valid_credit_card_number",
							Validator\CreditCard::SERVICEFAILURE => "text_error_error_validating",
					);
					break;
				case 'callback':
					$messages = array(
							Validator\Callback::INVALID_VALUE => "text_error_not_valid",
							Validator\Callback::INVALID_CALLBACK => "text_error_error_validating",
					);
					break;
				case 'between':
					$messages = array(
							Validator\Between::NOT_BETWEEN => "text_error_not_between",
							Validator\Between::NOT_BETWEEN_STRICT => "text_error_not_between_strictly",
					);
					break;
				case 'barcode':
					$messages = array(
							Validator\Barcode::FAILED => "text_error_not_valid_checksum",
							Validator\Barcode::INVALID_CHARS => "text_error_not_valid_characters",
							Validator\Barcode::INVALID_LENGTH => "text_error_character_length",
							Validator\Barcode::INVALID => "text_error_required",
					);
					break;
				case 'alphabetic':
					$messages = array(
							Validator\Alphabetic::NOT_ALPHABETIC => "text_error_alphabetic_character_only",
							Validator\Alphabetic::STRING_EMPTY => "text_error_required",
							Validator\Alphabetic::TOO_SHORT => "text_error_alphabetic_character_min",
							Validator\Alphabetic::TOO_LONG => "text_error_alphabetic_character_max",
					);
				case 'sitemappriority':
					$messages = array(
							Validator\Sitemap\Priority::NOT_VALID => "text_error_not_valid_sitemap_priority",
							Validator\Sitemap\Priority::INVALID => "text_error_required",
					);
				case 'sitemaploc':
					$messages = array(
							Validator\Sitemap\Loc::NOT_VALID => "text_error_not_valid_sitemap_location",
							Validator\Sitemap\Loc::INVALID => "text_error_required",
					);
				case 'sitemaplastmod':
					$messages = array(
							Validator\Sitemap\Lastmod::NOT_VALID => "text_error_not_valid_sitemap_lastmod",
							Validator\Sitemap\Lastmod::INVALID => "text_error_required",
					);
				case 'sitemapchangefreq':
					$messages = array(
							Validator\Sitemap\Changefreq::NOT_VALID => "text_error_not_valid_sitemap_changefreq",
							Validator\Sitemap\Changefreq::INVALID => "text_error_required",
					);
					break;
				case 'filecount':
					$messages = array(
							Validator\File\Count::TOO_MANY => "text_error_max_file_count",
							Validator\File\Count::TOO_FEW => "text_error_min_file_count",
					);
					break;
				case 'filecrc32':
					$messages = array(
							Validator\File\Crc32::DOES_NOT_MATCH => "text_error_not_match_crc32",
							Validator\File\Crc32::NOT_DETECTED => "text_error_not_valid_crc32",
							Validator\File\Crc32::NOT_FOUND => "text_error_not_exist_readable",
					);
					break;
				case 'fileexcludeextension':
					$messages = array(
							Validator\File\ExcludeExtension::FALSE_EXTENSION => "text_error_not_valid_extension",
							Validator\File\ExcludeExtension::NOT_FOUND => "text_error_not_exist_readable",
					);
					break;
				case 'fileexists':
					$messages = array(
							Validator\File\Exists::DOES_NOT_EXIST => "text_error_not_exist",
					);
					break;
				case 'fileextension':
					$messages = array(
							Validator\File\Extension::FALSE_EXTENSION => "text_error_not_valid_extension",
							Validator\File\Extension::NOT_FOUND => "text_error_not_exist_readable",
					);
					break;
				case 'filefilessize':
					$messages = array(
							Validator\File\FilesSize::TOO_BIG => "text_error_max_file_sum_size",
							Validator\File\FilesSize::TOO_SMALL => "text_error_min_file_sum_size",
							Validator\File\FilesSize::NOT_READABLE => "text_error_file_only",
					);
					break;
				case 'filehash':
					$messages = array(
							Validator\File\Hash::DOES_NOT_MATCH => "text_error_not_match_hashes",
							Validator\File\Hash::NOT_DETECTED => "text_error_not_valid_hashes",
							Validator\File\Hash::NOT_FOUND => "text_error_not_exist_readable",
					);
					break;
				case 'fileimagesize':
					$messages = array(
							Validator\File\ImageSize::WIDTH_TOO_BIG => "text_error_max_image_width",
							Validator\File\ImageSize::WIDTH_TOO_SMALL => "text_error_min_image_width",
							Validator\File\ImageSize::HEIGHT_TOO_BIG => "text_error_max_image_height",
							Validator\File\ImageSize::HEIGHT_TOO_SMALL => "text_error_min_image_height",
							Validator\File\ImageSize::NOT_DETECTED => "text_error_file_size_detect",
							Validator\File\ImageSize::NOT_READABLE => "text_error_not_exist_readable",
					);
					break;
				case 'fileiscompressed':
					$messages = array(
							Validator\File\IsCompressed::FALSE_TYPE => "text_error_not_compressed",
							Validator\File\IsCompressed::NOT_DETECTED => "text_error_mimetype_not_detected",
							Validator\File\IsCompressed::NOT_READABLE => "text_error_not_exist_readable",
					);
					break;
				case 'fileisimage':
					$messages = array(
							Validator\File\IsImage::FALSE_TYPE => "text_error_not_image",
							Validator\File\IsImage::NOT_DETECTED => "text_error_mimetype_not_detected",
							Validator\File\IsImage::NOT_READABLE => "text_error_not_exist_readable",
					);
					break;
				case 'filemd5':
					$messages = array(
							Validator\File\Md5::DOES_NOT_MATCH => "text_error_not_match_md5",
							Validator\File\Md5::NOT_DETECTED => "text_error_not_valid_md5",
							Validator\File\Md5::NOT_FOUND => "text_error_not_exist_readable",
					);
					break;
				case 'filemimetype':
					$messages = array(
							Validator\File\MimeType::FALSE_TYPE => "text_error_not_valid_minetype",
							Validator\File\MimeType::NOT_DETECTED => "text_error_mimetype_not_detected",
							Validator\File\MimeType::NOT_READABLE => "text_error_not_exist_readable",
					);
					break;
				case 'filenotexists':
					$messages = array(
							Validator\File\NotExists::DOES_EXIST => "text_error_exist",
					);
					break;
				case 'filesha1':
					$messages = array(
							Validator\File\Sha1::DOES_NOT_MATCH => "text_error_not_match_sha1",
							Validator\File\Sha1::NOT_DETECTED => "text_error_not_valid_sha1",
							Validator\File\Sha1::NOT_FOUND => "text_error_not_exist_readable",
					);
					break;
				case 'filesize':
					$messages = array(
							Validator\File\Size::TOO_BIG => "text_error_max_file_size",
							Validator\File\Size::TOO_SMALL => "text_error_min_file_size",
							Validator\File\Size::NOT_FOUND => "text_error_not_exist_readable",
					);
					break;
				case 'fileupload':
					$messages = array(
							Validator\File\Upload::INI_SIZE => "text_error_exceeds_ini_size",
							Validator\File\Upload::FORM_SIZE => "text_error_exceeds_form_size",
							Validator\File\Upload::PARTIAL => "text_error_only_partially_uploaded",
							Validator\File\Upload::NO_FILE => "text_error_not_uploaded",
							Validator\File\Upload::NO_TMP_DIR => "text_error_file_temporary_directory",
							Validator\File\Upload::CANT_WRITE => "text_error_cant_be_written",
							Validator\File\Upload::EXTENSION => "text_error_file_extension_error",
							Validator\File\Upload::ATTACK => "text_error_file_illegally",
							Validator\File\Upload::FILE_NOT_FOUND => "text_error_not_found",
							Validator\File\Upload::UNKNOWN => "text_error_file_error",
					);
					break;
				case 'fileuploadfile':
					$messages = array(
							Validator\File\UploadFile::INI_SIZE => "text_error_exceeds_ini_size",
							Validator\File\UploadFile::FORM_SIZE => "text_error_exceeds_form_size",
							Validator\File\UploadFile::PARTIAL => "text_error_only_partially_uploaded",
							Validator\File\UploadFile::NO_FILE => "text_error_not_uploaded",
							Validator\File\UploadFile::NO_TMP_DIR => "text_error_file_temporary_directory",
							Validator\File\UploadFile::CANT_WRITE => "text_error_cant_be_written",
							Validator\File\UploadFile::EXTENSION => "text_error_file_extension_error",
							Validator\File\UploadFile::ATTACK => "text_error_file_illegally",
							Validator\File\UploadFile::FILE_NOT_FOUND => "text_error_not_found",
							Validator\File\UploadFile::UNKNOWN => "text_error_file_error",
					);
					break;
				case 'filewordcount':
					$messages = array(
							Validator\File\WordCount::TOO_MUCH => "text_error_character_max",
							Validator\File\WordCount::TOO_LESS => "text_error_character_min",
							Validator\File\WordCount::NOT_FOUND => "text_error_not_exist_readable",
					);
					break;
				case 'abstractdb':
					$messages = array(
							Validator\Db\AbstractDb::ERROR_NO_RECORD_FOUND => "text_error_not_match",
							Validator\Db\AbstractDb::ERROR_RECORD_FOUND => "text_error_match",
					);
					break;
				case 'authentication':
					$messages = array(
							Authentication\Validator\Authentication::IDENTITY_NOT_FOUND => "text_error_not_valid_identity",
							Authentication\Validator\Authentication::IDENTITY_AMBIGUOUS => "text_error_not_valid_identity",
							Authentication\Validator\Authentication::CREDENTIAL_INVALID => "text_error_not_valid_password",
							Authentication\Validator\Authentication::UNCATEGORIZED => "text_error_authentication_failed",
							Authentication\Validator\Authentication::GENERAL => "text_error_authentication_failed",
					);
					break;
				case 'captcha':
					$messages = array(
							Captcha\ReCaptcha::MISSING_VALUE => "text_error_required",
							Captcha\ReCaptcha::MISSING_ID => "text_error_is_missing",
							Captcha\ReCaptcha::BAD_CAPTCHA => "text_error_not_match",
					);
					break;
				case 'alnum':
					$messages = array(
							I18n\Validator\Alnum::INVALID => "text_error_required",
							I18n\Validator\Alnum::NOT_ALNUM => "text_error_alphabetic_numeric_only",
							I18n\Validator\Alnum::STRING_EMPTY => "text_error_required",
					);
					break;
				case 'alpha':
					$messages = array(
							I18n\Validator\Alpha::INVALID => "text_error_required",
							I18n\Validator\Alpha::NOT_ALPHA => "text_error_not_appear_alphabetic_characters",
							I18n\Validator\Alpha::STRING_EMPTY => "text_error_required",
					);
					break;
				case 'float':
					$messages = array(
							I18n\Validator\Float::INVALID => "text_error_required",
							I18n\Validator\Float::NOT_FLOAT => "text_error_not_appear_float",
					);
					break;
				case 'int':
					$messages = array(
							I18n\Validator\Int::INVALID => "text_error_required",
							I18n\Validator\Int::NOT_INT => "text_error_not_appear_integer",
					);
					break;
				case 'postcode':
					$messages = array(
							I18n\Validator\PostCode::INVALID => "text_error_required",
							I18n\Validator\PostCode::NO_MATCH => "text_error_not_valid_postal_code",
							I18n\Validator\PostCode::SERVICE => "text_error_not_valid_postal_code",
							I18n\Validator\PostCode::SERVICEFAILURE => "text_error_error_validating",
					);
					break;
			}
			if (is_array($messages) && $messages > 0) {
				if (!empty($field)) {
					$field = $this->get('text_' . $field);
				}
				foreach ($messages as $messages_key => $messages_value) {
					$messages_data = $this->get($messages_value);
					$messages_raw = (!empty($field) ? $field . ' ' : null) . $messages_data;
					$messages[$messages_key] = ucfirst($messages_raw);
				}
			}
		}
		return $messages;
	}
}
