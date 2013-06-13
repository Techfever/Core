<?php

require 'init_autoloader.php';

global $Kernel;
require_once __DIR__ . '/Kernel/Kernel.php';
$Kernel = new Kernel\Initialize;
$Kernel->initialize();
//print_r($Kernel->Superglobal()->getVariable('Server'));
print_r($Kernel->getService('Superglobal', 'object')->getVariable('Global'));
print_r($Kernel->getConfig('Database'));
?>