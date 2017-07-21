<?php

declare(strict_types=1);

namespace Tests\Sylake\SyliusConsumerPlugin\Functional;

use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\Assert;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\TaxonInterface;

/**
 * @author Kamil Kokot <kamil@kokot.me>
 */
final class ProductTaxonSynchronizationTest extends ProductSynchronizationTestCase
{
    /**
     * @test
     */
    public function it_adds_and_updates_a_product_with_taxons()
    {
        $this->consumeTaxon('master', null, ['en_US' => 'Master catalog']);
        $this->consumeTaxon('master__goodies', 'master', ['en_US' => 'Goodies']);
        $this->consumeTaxon('master__goodies__tshirts', 'master__goodies', ['en_US' => 'T-Shirts']);

        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": ["master__goodies", "master__goodies__tshirts"],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}]
                },
                "created": "2017-04-18T12:30:45+02:30",
                "associations": {}
            }
        }');

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertSame('master__goodies__tshirts', $product->getMainTaxon()->getCode());
        $this->assertArraysAreEqual(['master__goodies', 'master__goodies__tshirts'], $product->getTaxons()->map(function (TaxonInterface $taxon) {
            return $taxon->getCode();
        })->toArray());

        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": ["master__goodies"],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}]
                },
                "created": "2017-04-18T12:30:45+02:30",
                "associations": {}
            }
        }');

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertSame('master__goodies', $product->getMainTaxon()->getCode());
        $this->assertArraysAreEqual(['master__goodies'], $product->getTaxons()->map(function (TaxonInterface $taxon) {
            return $taxon->getCode();
        })->toArray());
    }
}
