<?php

namespace Techfever\Template\Plugin\Validators;

class Radio extends Select {
	const INVALID = 'radioInvalid';

	/**
	 * @var array
	 */
	protected $messageTemplates = array(
			self::INVALID => "text_error_invalid_value_type",
	);
}
