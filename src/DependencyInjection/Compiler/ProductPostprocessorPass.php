<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class ProductPostprocessorPass implements CompilerPassInterface
{
    /** {@inheritdoc} */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('sylake_sylius_consumer.projector.product')) {
            return;
        }

        $compositeItemProjectorDefinition = $container->findDefinition('sylake_sylius_consumer.projector.product');

        foreach ($container->findTaggedServiceIds('sylake_sylius_consumer.projector.product.postprocessor') as $id => $tags) {
            $compositeItemProjectorDefinition->addMethodCall('addPostprocessor', [new Reference($id)]);
        }
    }
}
