<?php
namespace Kernel\ServiceFactory;

use Zend\Db\Adapter\Adapter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DbServiceFactory implements FactoryInterface
{
    /**
     * Create db adapter service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Adapter
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        return new Adapter($config['db']);
    }
}
