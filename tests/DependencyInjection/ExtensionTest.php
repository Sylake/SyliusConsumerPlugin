<?php

declare(strict_types=1);

namespace Tests\Sylake\SyliusConsumerPlugin\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Sylake\SyliusConsumerPlugin\DependencyInjection\SylakeSyliusConsumerExtension;

final class ExtensionTest extends AbstractExtensionTestCase
{
    /**
     * @test
     */
    public function it_adds_product_denormalization_parameters_by_default()
    {
        $this->load();

        $this->assertContainerBuilderHasParameter('sylake_sylius_consumer.denormalizer.product.name_attribute', 'name');
        $this->assertContainerBuilderHasParameter('sylake_sylius_consumer.denormalizer.product.description_attribute', 'description');
        $this->assertContainerBuilderHasParameter('sylake_sylius_consumer.denormalizer.product.price_attribute', 'price');
        $this->assertContainerBuilderHasParameter('sylake_sylius_consumer.denormalizer.product.image_attribute', 'images');
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainerExtensions()
    {
        return [new SylakeSyliusConsumerExtension()];
    }
}
