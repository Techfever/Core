<?php
return array(
	'autoloader' => array(
		'namespaces' => array(
			"Kernel" => KERNEL_PATH,
			"Techfever" => CORE_PATH.'/vendor/Techfever',
		), "autoregister_zf" => true,
	)
);