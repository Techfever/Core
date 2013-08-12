<?php
namespace Techfever\Functions\Crypt;

use Techfever\Functions\General as GeneralBase;

class Decode extends GeneralBase {
	private $data = null;

	public function __construct($data) {
		$data = base64_decode($data);
		$key = (strlen(SYSTEM_CRYPT_KEY) > 0 ? SYSTEM_CRYPT_KEY : 111216);
		$this->data = ($data - $key);
	}

	public function __toString() {
		return (string) $this->data;
	}
}
