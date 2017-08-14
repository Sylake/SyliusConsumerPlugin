<?php

declare(strict_types=1);

namespace Tests\Sylake\SyliusConsumerPlugin\DependencyInjection\Compiler;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Sylake\SyliusConsumerPlugin\DependencyInjection\Compiler\ProductPostprocessorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class ProductPostprocessorPassTest extends AbstractCompilerPassTestCase
{
    /** {@inheritdoc} */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ProductPostprocessorPass());
    }

    /**
     * @test
     */
    public function it_collects_tagged_item_projectors()
    {
        $this->setDefinition('sylake_sylius_consumer.projector.product', new Definition());
        $this->setDefinition(
            'acme.product_postprocessor',
            (new Definition())->addTag('sylake_sylius_consumer.projector.product.postprocessor')
        );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sylake_sylius_consumer.projector.product',
            'addPostprocessor',
            [new Reference('acme.product_postprocessor')]
        );
    }
}
