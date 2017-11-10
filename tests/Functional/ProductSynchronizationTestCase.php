<?php

declare(strict_types=1);

namespace Tests\Sylake\SyliusConsumerPlugin\Functional;

use PHPUnit\Framework\Assert;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;

/**
 * @author Kamil Kokot <kamil@kokot.me>
 */
abstract class ProductSynchronizationTestCase extends SynchronizationTestCase
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->productRepository = static::$kernel->getContainer()->get('sylius.repository.product');
    }

    /**
     * @param array $expectedArray
     * @param array $actualArray
     *
     * @throws \InvalidArgumentException
     */
    protected function assertArraysAreEqual(array $expectedArray, array $actualArray): void
    {
        Assert::assertSame(count($expectedArray), count($actualArray));

        foreach ($expectedArray as $expectedElement) {
            Assert::assertTrue(in_array($expectedElement, $actualArray, true));
        }
    }

    protected function consumeAssociationType(string $code, array $labels): void
    {
        $this->consume(sprintf('{
            "type": "akeneo_association_type_updated",
            "payload": {
                "code": %s,
                "labels": %s
            }
        }', json_encode($code), json_encode($labels)));
    }

    protected function consumeAttribute(string $code, string $type, array $labels): void
    {
        $this->consume(sprintf('{
            "type": "akeneo_attribute_updated",
            "payload": {
                "code": %s,
                "type": %s,
                "labels": %s
            }
        }', json_encode($code), json_encode($type), json_encode($labels)));
    }

    protected function consumeAttributeOption(string $attributeCode, string $code, array $labels): void
    {
        $this->consume(sprintf('{
            "type": "akeneo_attribute_option_updated",
            "payload": {
                "code": %s,
                "attribute": %s,
                "labels": %s
            }
        }', json_encode($code), json_encode($attributeCode), json_encode($labels)));
    }

    protected function consumeFamily(string $code, array $labels): void
    {
        $this->consume(sprintf('{
            "type": "akeneo_family_updated",
            "payload": {
                "code": %s,
                "labels": %s
            }
        }', json_encode($code), json_encode($labels)));
    }

    protected function consumeGroup(string $code, array $labels): void
    {
        $this->consume(sprintf('{
            "type": "akeneo_group_updated",
            "payload": {
                "code": %s,
                "labels": %s
            }
        }', json_encode($code), json_encode($labels)));
    }

    protected function consumeTaxon(string $code, ?string $parentCode, array $labels): void
    {
        $this->consume(sprintf('{
            "type": "akeneo_category_updated",
            "payload": {
                "code": %s,
                "parent": %s,
                "labels": %s
            }
        }', json_encode($code), json_encode($parentCode), json_encode($labels)));
    }
}
