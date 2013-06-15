<?php

require 'init_autoloader.php';
use Techfever\Kernel\Startup;
global $Kernel;
$Kernel = Techfever\Kernel\Startup::initialize();
//$Kernel->initialize();
//print_r($Kernel->Superglobal()->getVariable('Server'));
//print_r($Kernel->getService('Superglobal', 'object')->getVariable('Global'));
//print_r($Kernel->getConfig('Database'));
?>