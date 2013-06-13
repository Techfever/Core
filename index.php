<?php

require 'init_autoloader.php';
use Kernel\Startup;
global $Kernel;
$Kernel = Kernel\Startup::initialize();
die();
$Kernel->initialize();
//print_r($Kernel->Superglobal()->getVariable('Server'));
print_r($Kernel->getService('Superglobal', 'object')->getVariable('Global'));
print_r($Kernel->getConfig('Database'));
?>