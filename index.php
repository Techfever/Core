<?php

require 'init_autoloader.php';

require_once __DIR__ . '/Kernel/Kernel.php';
$Kernel = new Kernel ();
$Kernel->initialize ();
?>