<?php
use Kernel\Language;
use Kernel\ServiceLocator;

$Language = ServiceLocator::getServiceManager('Language');
$data = $Language->getLanguage('id_ID');
return $data;
