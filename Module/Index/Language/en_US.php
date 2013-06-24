<?php
use Kernel\Language;
use Kernel\ServiceLocator;

$Language = ServiceLocator::getServiceManager('Language');
$data = $Language->getLanguage('en_US');
return $data;
