<?php

declare(strict_types=1);

namespace Tests\Sylake\SyliusConsumerPlugin\Functional;

use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\Assert;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Product\Model\ProductAssociationInterface;

/**
 * @author Kamil Kokot <kamil@kokot.me>
 */
final class ProductAssociationSynchronizationTest extends ProductSynchronizationTestCase
{
    /**
     * @test
     */
    public function it_adds_and_updates_a_product_with_associations()
    {
        $this->consumeAssociationType('SUBSTITUTION', ['en_US' => 'Substitution']);
        $this->consumeAssociationType('CROSS_SELL', ['en_US' => 'Cross sell']);

        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_WPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt white and purple with short sleeve"}]
                },
                "created": "2017-04-18T12:30:45+02:30",
                "associations": {}
            }
        }');

        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_PBXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt white and purple with short sleeve"}]
                },
                "created": "2017-04-18T12:30:45+02:30",
                "associations": {}
            }
        }');

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
                "associations": {
                    "SUBSTITUTION": {"groups": [], "products": ["AKNTS_WPXS", "AKNTS_PBXS", "AKNTS_PWXS"]},
                    "CROSS_SELL": {"groups": [], "products": ["AKNTS_WPXS", "AKNTS_PBXS"]}
                }
            }
        }');

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);
        Assert::assertNotNull($product);

        $substitutionAssociation = $this->getProductAssociation($product, 'SUBSTITUTION');
        Assert::assertNotNull($substitutionAssociation);
        $this->assertArraysAreEqual(['AKNTS_WPXS', 'AKNTS_PBXS'], $substitutionAssociation->getAssociatedProducts()->map(function (ProductInterface $product) {
            return $product->getCode();
        })->toArray());

        $crossSellAssociation = $this->getProductAssociation($product, 'CROSS_SELL');
        Assert::assertNotNull($crossSellAssociation);
        $this->assertArraysAreEqual(['AKNTS_WPXS', 'AKNTS_PBXS'], $crossSellAssociation->getAssociatedProducts()->map(function (ProductInterface $product) {
            return $product->getCode();
        })->toArray());

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
                "associations": {
                    "SUBSTITUTION": {"groups": [], "products": ["AKNTS_PBXS"]}
                }
            }
        }');

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);
        Assert::assertNotNull($product);

        $substitutionAssociation = $this->getProductAssociation($product, 'SUBSTITUTION');
        Assert::assertNotNull($substitutionAssociation);
        $this->assertArraysAreEqual(['AKNTS_PBXS'], $substitutionAssociation->getAssociatedProducts()->map(function (ProductInterface $product) {
            return $product->getCode();
        })->toArray());

        $crossSellAssociation = $this->getProductAssociation($product, 'CROSS_SELL');
        Assert::assertNull($crossSellAssociation);
    }

    private function getProductAssociation(ProductInterface $product, string $code): ?ProductAssociationInterface
    {
        foreach ($product->getAssociations() as $association) {
            if ($association->getType()->getCode() === $code) {
                return $association;
            }
        }

        return null;
    }
}
