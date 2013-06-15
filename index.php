<?php

require 'init_autoloader.php';
$Kernel = Kernel\Startup::prepare();
$Kernel->start();
$Kernel->render();
$Kernel->stop();
?>