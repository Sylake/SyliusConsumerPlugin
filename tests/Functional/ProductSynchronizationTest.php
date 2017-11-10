<?php

declare(strict_types=1);

namespace Tests\Sylake\SyliusConsumerPlugin\Functional;

use PHPUnit\Framework\Assert;
use Sylius\Component\Core\Model\ProductInterface;

final class ProductSynchronizationTest extends ProductSynchronizationTestCase
{
    /**
     * @test
     */
    public function it_adds_and_updates_a_product_with_basic_product_information()
    {
        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "description": [{"locale": "en_US", "scope": "mobile", "data": "T-Shirt description"}]
                },
                "created": "2017-04-18T12:30:45+02:30",
                "associations": {}
            }
        }');

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertSame('Akeneo T-Shirt black and purple with short sleeve', $product->getTranslation('en_US')->getName());
        Assert::assertSame('aknts-bpxs-akeneo-t-shirt-black-and-purple-with-short-sleeve', $product->getTranslation('en_US')->getSlug());
        Assert::assertSame('T-Shirt description', $product->getTranslation('en_US')->getDescription());
        Assert::assertEquals(\DateTime::createFromFormat(\DateTime::ATOM, '2017-04-18T12:30:45+02:30'), $product->getCreatedAt());
        Assert::assertTrue($product->isEnabled());

        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": false,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve (updated)"}],
                    "description": [{"locale": "en_US", "scope": "mobile", "data": "T-Shirt description (updated)"}]
                },
                "created": "2017-04-18T12:45:45+02:30",
                "associations": {}
            }
        }');

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertSame('Akeneo T-Shirt black and purple with short sleeve (updated)', $product->getTranslation('en_US')->getName());
        Assert::assertSame('aknts-bpxs-akeneo-t-shirt-black-and-purple-with-short-sleeve-updated', $product->getTranslation('en_US')->getSlug());
        Assert::assertSame('T-Shirt description (updated)', $product->getTranslation('en_US')->getDescription());
        Assert::assertEquals(\DateTime::createFromFormat(\DateTime::ATOM, '2017-04-18T12:45:45+02:30'), $product->getCreatedAt());
        Assert::assertFalse($product->isEnabled());
    }
}
