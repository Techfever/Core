<?php
use Kernel\Template;

$Template = new Template();
$Template->prepare();
$config = $Template->getConfig();
return $config;
