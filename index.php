<?php

require 'init_autoloader.php';
$Kernel = Kernel\Startup::prepare();
$Kernel->initialize();
$Kernel->start();
$Kernel->uninitialize();
?>