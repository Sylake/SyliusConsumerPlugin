<?php

declare(strict_types=1);

namespace Tests\Sylake\SyliusConsumerPlugin\Functional;

use PHPUnit\Framework\Assert;
use Sylius\Component\Core\Model\ProductInterface;

/**
 * @author Kamil Kokot <kamil@kokot.me>
 */
final class ProductGroupSynchronizationTest extends ProductSynchronizationTestCase
{
    /**
     * @test
     */
    public function it_adds_updates_and_removes_a_family_code_attribute(): void
    {
        $this->consumeGroup('FIRST_GROUP_CODE', ['en_US' => 'Group 1.', 'de_DE' => 'Gruppe 1.']);
        $this->consumeGroup('SECOND_GROUP_CODE', ['en_US' => 'Group 2.', 'de_DE' => 'Gruppe 2.']);

        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "groups": {"1": "FIRST_GROUP_CODE", "2": "SECOND_GROUP_CODE"},
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
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertSame('FIRST_GROUP_CODE, SECOND_GROUP_CODE', $product->getAttributeByCodeAndLocale('AKENEO_GROUPS_CODES', 'en_US')->getValue());
        Assert::assertSame('FIRST_GROUP_CODE, SECOND_GROUP_CODE', $product->getAttributeByCodeAndLocale('AKENEO_GROUPS_CODES', 'de_DE')->getValue());
        Assert::assertSame('Group 1., Group 2.', $product->getAttributeByCodeAndLocale('AKENEO_GROUPS_NAMES', 'en_US')->getValue());
        Assert::assertSame('Gruppe 1., Gruppe 2.', $product->getAttributeByCodeAndLocale('AKENEO_GROUPS_NAMES', 'de_DE')->getValue());

        $this->consumeGroup('FIRST_GROUP_CODE', ['en_US' => 'Group 1. (updated)', 'de_DE' => 'Gruppe 1. (updated)']);

        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "groups": {"1": "FIRST_GROUP_CODE"},
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
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertSame('FIRST_GROUP_CODE', $product->getAttributeByCodeAndLocale('AKENEO_GROUPS_CODES', 'en_US')->getValue());
        Assert::assertSame('FIRST_GROUP_CODE', $product->getAttributeByCodeAndLocale('AKENEO_GROUPS_CODES', 'de_DE')->getValue());
        Assert::assertSame('Group 1. (updated)', $product->getAttributeByCodeAndLocale('AKENEO_GROUPS_NAMES', 'en_US')->getValue());
        Assert::assertSame('Gruppe 1. (updated)', $product->getAttributeByCodeAndLocale('AKENEO_GROUPS_NAMES', 'de_DE')->getValue());

        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "groups": {},
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
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertNull($product->getAttributeByCodeAndLocale('AKENEO_GROUPS_CODES', 'en_US'));
        Assert::assertNull($product->getAttributeByCodeAndLocale('AKENEO_GROUPS_CODES', 'de_DE'));
        Assert::assertNull($product->getAttributeByCodeAndLocale('AKENEO_GROUPS_NAMES', 'en_US'));
        Assert::assertNull($product->getAttributeByCodeAndLocale('AKENEO_GROUPS_NAMES', 'de_DE'));
    }
}
