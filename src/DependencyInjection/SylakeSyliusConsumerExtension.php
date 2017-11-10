<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class SylakeSyliusConsumerExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $config = $this->processConfiguration($this->getConfiguration([], $container), $config);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('services.xml');

        $container->setParameter(
            'sylake_sylius_consumer.denormalizer.product.name_attribute',
            $config['denormalizer']['product']['name_attribute']
        );
        $container->setParameter(
            'sylake_sylius_consumer.denormalizer.product.description_attribute',
            $config['denormalizer']['product']['description_attribute']
        );
        $container->setParameter(
            'sylake_sylius_consumer.denormalizer.product.price_attribute',
            $config['denormalizer']['product']['price_attribute']
        );
        $container->setParameter(
            'sylake_sylius_consumer.denormalizer.product.image_attribute',
            $config['denormalizer']['product']['image_attribute']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        if (!$container->hasExtension('old_sound_rabbit_mq')) {
            throw new \RuntimeException('Make sure OldSoundRabbitMqBundle is enabled.');
        }

        $container->prependExtensionConfig('old_sound_rabbit_mq', [
            'consumers' => [
                'sylake' => [
                    'exchange_options' => [
                        'name' => 'sylake',
                        'type' => 'fanout',
                    ],
                    'queue_options' => [
                        'name' => 'sylake',
                    ],
                    'callback' => 'rabbitmq_simplebus.consumer',
                ],
            ],
        ]);
    }
}
