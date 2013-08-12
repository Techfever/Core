<?php
define('DSWIN', '\\');
define('DSOTHER', '/');
define('DS', (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? DSWIN : DSOTHER));
define('CORE_PATH', getcwd());
define('KERNEL_PATH', CORE_PATH . '/Kernel');
define('CONFIG_PATH', CORE_PATH . '/Config');
define('MODULE_PATH', CORE_PATH . '/Module');
define('CACHE_ENABLE', False);
define('DB_LOG_ENABLE', False);

// Composer autoloading
if (file_exists('vendor/autoload.php')) {
    $loader = include 'vendor/autoload.php';
}

$zf2Path = false;

if (is_dir('vendor/ZF2/library')) {
    $zf2Path = 'vendor/ZF2/library';
} elseif (getenv('ZF2_PATH')) {      // Support for ZF2_PATH environment variable or git submodule
    $zf2Path = getenv('ZF2_PATH');
} elseif (get_cfg_var('zf2_path')) { // Support for zf2_path directive value
    $zf2Path = get_cfg_var('zf2_path');
}

if ($zf2Path) {
    if (isset($loader)) {
        $loader->add('Zend', $zf2Path);
    } else {
        include $zf2Path . '/Zend/Loader/AutoloaderFactory.php';
        Zend\Loader\AutoloaderFactory::factory(array(
            'Zend\Loader\StandardAutoloader' => array(
                'autoregister_zf' => true
            )
        ));
    }
}

if (!class_exists('Zend\Loader\AutoloaderFactory')) {
    throw new RuntimeException('Unable to load ZF2. Run `php composer.phar install` or define a ZF2_PATH environment variable.');
}
