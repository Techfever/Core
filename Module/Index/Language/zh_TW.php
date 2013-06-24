<?php
use Kernel\Language;
use Kernel\ServiceLocator;

$Language = ServiceLocator::getServiceManager('Language');
$data = $Language->getLanguage('zh_TW');
return $data;
