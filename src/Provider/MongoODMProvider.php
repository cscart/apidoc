<?php
namespace CSCart\ApiDoc\Provider;

use CSCart\ApiDoc\Mongo\QueryCollector;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;

class MongoODMProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['mongo.queryCollector'] = function (Container $pimple) {
            return new QueryCollector();
        };

        $pimple['mongo.dm'] = function (Container $pimple) {
            $configuration = new Configuration();
            $configuration->setHydratorDir(ROOT_DIR . '/_doctrine/Hydrators');
            $configuration->setHydratorNamespace('Hydrators');
            $configuration->setProxyDir(ROOT_DIR . '/_doctrine/Proxies');
            $configuration->setProxyNamespace('Proxies');
            $configuration->setDefaultDB('cscart_apidoc');
            $configuration->setMetadataDriverImpl(AnnotationDriver::create(ROOT_DIR . '/src/Parser/Node'));

            $configuration->setAutoGenerateHydratorClasses(Configuration::AUTOGENERATE_FILE_NOT_EXISTS);
            $configuration->setAutoGenerateProxyClasses(Configuration::AUTOGENERATE_FILE_NOT_EXISTS);

            $configuration->setLoggerCallable([$pimple['mongo.queryCollector'], 'logQuery']);

            AnnotationDriver::registerAnnotationClasses();

            $dm = DocumentManager::create(null, $configuration);

            return $dm;
        };
    }

}