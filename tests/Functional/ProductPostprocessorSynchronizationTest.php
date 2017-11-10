<?php

declare(strict_types=1);

namespace Tests\Sylake\SyliusConsumerPlugin\Functional;

use PHPUnit\Framework\Assert;
use Sylake\SyliusConsumerPlugin\Event\ProductUpdated;
use Sylake\SyliusConsumerPlugin\Projector\Product\ProductPostprocessorInterface;
use Sylake\SyliusConsumerPlugin\Projector\ProductProjector;
use Sylius\Component\Core\Model\ProductInterface;

final class ProductPostprocessorSynchronizationTest extends ProductSynchronizationTestCase
{
    /**
     * @test
     */
    public function it_can_modify_projected_product_before_saving_it()
    {
        /** @var ProductProjector $productProjector */
        $productProjector = static::$kernel->getContainer()->get('sylake_sylius_consumer.projector.product');
        $productProjector->addPostprocessor(new class() implements ProductPostprocessorInterface {
            public function __invoke(ProductUpdated $event, ProductInterface $product): void
            {
                $product->setCode($product->getCode() . '_POSTPROCESSED');
            }
        });

        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}]
                },
                "created": "2017-04-18T12:30:45+02:30",
                "associations": {}
            }
        }');

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS_POSTPROCESSED']);

        Assert::assertNotNull($product);
    }
}
