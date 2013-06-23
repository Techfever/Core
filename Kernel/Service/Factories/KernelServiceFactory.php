<?php
namespace Kernel\Service\Factories;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Kernel\Service\Manager;

/**
 * Phpsetting.
 */
class KernelServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new Manager();
    }
}
