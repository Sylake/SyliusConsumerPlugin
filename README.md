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
