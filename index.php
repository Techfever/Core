<?php

require 'init_autoloader.php';

require_once __DIR__ . '/Kernel/Kernel.php';
$Kernel = new Kernel();
$Kernel->initialize();
//print_r($Kernel->Superglobal()->getVariable('Server'));
print_r($Kernel->getService('Superglobal', 'object')->getVariable('Server'));
print_r($Kernel->getConfig('Database'));
?>