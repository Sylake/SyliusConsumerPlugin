# This Sylius consumer plugin is deprecated & supports Akeneo 1.7 only

SyliusConsumerPlugin
====================

Installation
------------

1. Require this package:

```bash
$ composer require sylake/sylius-consumer-plugin
```

2. Add bundles to `AppKernel.php` of existing Sylius application:

```php
public function registerBundles()
{
    $bundles = [
        new \SimpleBus\SymfonyBridge\SimpleBusCommandBusBundle(),
        new \SimpleBus\SymfonyBridge\SimpleBusEventBusBundle(),
        new \OldSound\RabbitMqBundle\OldSoundRabbitMqBundle(),
        new \SyliusLabs\RabbitMqSimpleBusBundle\RabbitMqSimpleBusBundle(),
        new \Sylake\SyliusConsumerPlugin\SylakeSyliusConsumerPlugin(),
    ];

    return array_merge(parent::registerBundles(), $bundles);
}
```

3. Configure RabbitMQ default connection:

```yaml
# app/config/config.yml

old_sound_rabbit_mq:
    connections:
        default:
            host: 'localhost'
            port: 5672
            user: 'guest'
            password: 'guest'
```

Usage
-----

1. Run the following command to listen for messages and consume them:

```bash
$ bin/console rabbitmq:consumer sylake
```

Extending product projector
---------------------------

Adding a product postprocessor allows to change product before saving it.

1. Create a class which implements `Sylake\SyliusConsumerPlugin\Projector\Product\ProductPostprocessorInterface`.

2. Define it as a service with tag `sylake_sylius_consumer.projector.product.postprocessor`.

3. :tada:
