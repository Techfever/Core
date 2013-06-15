<?php
/**
 * Autoloader
 */

define('CORE_PATH', getcwd());
define('KERNEL_PATH', CORE_PATH . '/techfever/Kernel');

// use library generated autoloader

// Composer autoloading
if (file_exists('vendor/autoload.php')) {
	$loader = include 'vendor/autoload.php';
}

$vendorPath = false;
if (is_dir('vendor')) {
	$vendorPath = 'vendor';
}
$kernelPath = false;
if (is_dir('kernel')) {
	$kernelPath = 'techfever';
}

if (isset($loader)) {
	if ($vendorPath) {
		$loader->add('Techfever', $vendorPath);
	}
	if ($kernelPath) {
		$loader->add('Kernel', $kernelPath);
	}
} else {
	if ($vendorPath) {
		include $vendorPath . '/Techfever/Loader/AutoloaderFactory.php';
		Techfever\Loader\AutoloaderFactory::factory(
				array(
					'Techfever\Loader\StandardAutoloader' => array(
						'autoregister_tf' => true, 'namespaces' => array(
							'Techfever' => $vendorPath . '/Techfever', 'Kernel' => $kernelPath . '/techfever/Kernel',
						)
					)
				));
	}
}
if (!class_exists('Techfever\Loader\AutoloaderFactory')) {
	throw new RuntimeException('Unable to load TF1. Run `php composer.phar install` or define a TF1_PATH environment variable.');
}
?>
