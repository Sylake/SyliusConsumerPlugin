<?php

declare(strict_types=1);

namespace Tests\Sylake\SyliusConsumerPlugin\Functional;

use PHPUnit\Framework\Assert;
use Sylius\Component\Core\Model\ProductInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @author Kamil Kokot <kamil@kokot.me>
 */
final class ProductFamilySynchronizationTest extends ProductSynchronizationTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        (new Application(static::$kernel))->find('sylake:consumer:setup')->run(new ArrayInput([]), new NullOutput());
    }

    /**
     * @test
     */
    public function it_adds_updates_and_removes_a_family_code_attribute(): void
    {
        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "family": "FAMILY_CODE",
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
        Assert::assertSame('FAMILY_CODE', $product->getAttributeByCodeAndLocale('AKENEO_FAMILY_CODE', 'en_US')->getValue());
        Assert::assertSame('FAMILY_CODE', $product->getAttributeByCodeAndLocale('AKENEO_FAMILY_CODE', 'de_DE')->getValue());

        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "family": "FAMILY_CODE_UPDATED",
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
        Assert::assertSame('FAMILY_CODE_UPDATED', $product->getAttributeByCodeAndLocale('AKENEO_FAMILY_CODE', 'en_US')->getValue());
        Assert::assertSame('FAMILY_CODE_UPDATED', $product->getAttributeByCodeAndLocale('AKENEO_FAMILY_CODE', 'de_DE')->getValue());

        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "family": null,
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
        Assert::assertNull($product->getAttributeByCodeAndLocale('AKENEO_FAMILY_CODE', 'en_US'));
        Assert::assertNull($product->getAttributeByCodeAndLocale('AKENEO_FAMILY_CODE', 'de_DE'));
    }
}
