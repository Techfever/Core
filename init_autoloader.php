<?php
/**
 * Autoloader
 */

define('CORE_PATH', getcwd());
define('KERNEL_PATH', CORE_PATH . '/kernel');

// use library generated autoloader

// Composer autoloading
if (file_exists('vendor/autoload.php')) {
	$loader = include 'vendor/autoload.php';
}

$vendorPath = false;
if (is_dir('vendor/Techfever')) {
	$vendorPath = 'vendor/Techfever';
}
$kernelPath = false;
if (is_dir('kernel')) {
	$kernelPath = 'kernel';
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
		Techfever\Loader\AutoloaderFactory::factory(array(
			'Zend\Loader\StandardAutoloader' => array(
				'autoregister_zf' => true
			)
		));
	}
}

if (!class_exists('Techfever\Loader\AutoloaderFactory')) {
	throw new RuntimeException('Unable to load TF2. Run `php composer.phar install` or define a TF2_PATH environment variable.');
}
?>
