<?php
namespace Techfever\Functions\Crypt;

use Techfever\Functions\General as GeneralBase;

class Encode extends GeneralBase {
	private $data = null;

	public function __construct($data) {
		$key = (strlen(SYSTEM_CRYPT_KEY) > 0 ? SYSTEM_CRYPT_KEY : 111216);
		$data = ($key + $data);
		$this->data = base64_encode($data);
	}

	public function __toString() {
		return (string) $this->data;
	}
}
