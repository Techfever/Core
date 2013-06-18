<?php
return array(
	'autoloader' => array(
		'namespaces' => array(
			"Kernel" => KERNEL_PATH,
			"Techfever" => CORE_PATH.'/vendor/Techfever',
			"Zend" => CORE_PATH.'/vendor/Zend',
		), "autoregister_zf" => false,
	)
);