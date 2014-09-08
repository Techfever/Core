<?php

namespace Techfever\Php;

use Techfever\Exception;

class Php {
	protected $config = null;
	public function __construct($config) {
		if (! empty ( $config ) && ! is_array ( $config )) {
			throw new Exception\InvalidArgumentException ( 'Config must contain an array' );
		}
		$this->config = $config;
		foreach ( $this->config as $key => $value ) {
			ini_set ( $key, $value );
		}
	}
}
