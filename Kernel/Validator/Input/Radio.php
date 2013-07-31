<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Kernel\Validator\Input;

class Radio extends Select {
	const INVALID = 'radioInvalid';

	/**
	 * @var array
	 */
	protected $messageTemplates = array(
			self::INVALID => "text_error_invalid_value_type",
	);
}
