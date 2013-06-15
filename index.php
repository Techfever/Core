<?php

require 'init_autoloader.php';
$Kernel = Kernel\Startup::prepare();
$Kernel->initialize();
$Kernel->render();
$Kernel->uninitialize();
?>