<?php

declare(strict_types=1);

namespace Tests\Sylake\SyliusConsumerPlugin\Functional;

use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\Assert;
use Sylius\Component\Core\Model\ProductInterface;

/**
 * @author Kamil Kokot <kamil@kokot.me>
 */
final class ProductImageSynchronizationTest extends ProductSynchronizationTestCase
{
    /**
     * @test
     */
    public function it_adds_a_new_product_with_images()
    {
        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "images": [{"locale": null, "scope": null, "data": "8\/7\/5\/3\/8753d08e04e7ecdda77ef77573cd42bbfb029dcb_image.jpg"}]
                },
                "created": "2017-04-18T16:12:55+02:00",
                "associations": {}
            }
        }'));

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);

        $akeneoProductImages = $product->getImagesByType('akeneo')->toArray();
        $akeneoProductImage = current($akeneoProductImages);

        Assert::assertNotFalse($akeneoProductImage);
        Assert::assertSame('8/7/5/3/8753d08e04e7ecdda77ef77573cd42bbfb029dcb_image.jpg', $akeneoProductImage->getPath());
        Assert::assertSame('akeneo', $akeneoProductImage->getType());
    }

    /**
     * @test
     */
    public function it_updates_an_existing_product_with_images()
    {
        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "images": [{"locale": null, "scope": null, "data": "8\/7\/5\/3\/8753d08e04e7ecdda77ef77573cd42bbfb029dcb_image.jpg"}]
                },
                "created": "2017-04-18T16:12:55+02:00",
                "associations": {}
            }
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "images": [{"locale": null, "scope": null, "data": null}]
                },
                "created": "2017-04-18T16:12:58+02:00",
                "associations": {}
            }
        }'));

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertSame([], $product->getImagesByType('akeneo')->toArray());
    }
}
