<?php
namespace CSCart\ApiDoc\Provider;

use CSCart\ApiDoc\Debugger\MongoQueriesEventListener;
use Doctrine\MongoDB\Events;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\MongoDB\Connection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;

class MongoODMProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['mongo.dm'] = function (Container $pimple) {
            $connection = new Connection();
            $configuration = new Configuration();
            $configuration->setHydratorDir(ROOT_DIR . '/_doctrine/Hydrators');
            $configuration->setHydratorNamespace('Hydrators');
            $configuration->setProxyDir(ROOT_DIR . '/_doctrine/Proxies');
            $configuration->setProxyNamespace('Proxies');
            $configuration->setDefaultDB('cscart_apidoc');

            $configuration->setMetadataDriverImpl(AnnotationDriver::create(ROOT_DIR . '/src/Parser/Node'));
            AnnotationDriver::registerAnnotationClasses();

            $dm = DocumentManager::create($connection, $configuration);

            return $dm;
        };
    }

}