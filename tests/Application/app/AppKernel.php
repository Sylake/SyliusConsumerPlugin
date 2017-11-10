<?php

declare(strict_types=1);

use Sylius\Bundle\CoreBundle\Application\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * @author Kamil Kokot <kamil@kokot.me>
 */
class AppKernel extends Kernel
{
    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        return array_merge(parent::registerBundles(), [
            new \Sylius\Bundle\AdminBundle\SyliusAdminBundle(),
            new \Sylius\Bundle\ShopBundle\SyliusShopBundle(),
            new \FOS\OAuthServerBundle\FOSOAuthServerBundle(),
            new \Sylius\Bundle\AdminApiBundle\SyliusAdminApiBundle(),

            new \SimpleBus\SymfonyBridge\SimpleBusCommandBusBundle(),
            new \SimpleBus\SymfonyBridge\SimpleBusEventBusBundle(),
            new \OldSound\RabbitMqBundle\OldSoundRabbitMqBundle(),
            new \SyliusLabs\RabbitMqSimpleBusBundle\RabbitMqSimpleBusBundle(),
            new \Sylake\SyliusConsumerPlugin\SylakeSyliusConsumerPlugin(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config.yml');
    }
}
