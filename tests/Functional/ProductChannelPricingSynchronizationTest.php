<?php

declare(strict_types=1);

namespace Tests\Sylake\SyliusConsumerPlugin\Functional;

use PHPUnit\Framework\Assert;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;

/**
 * @author Kamil Kokot <kamil@kokot.me>
 */
final class ProductChannelPricingSynchronizationTest extends ProductSynchronizationTestCase
{
    /**
     * @test
     */
    public function it_adds_and_updates_a_product_with_channels_and_pricing()
    {
        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "price": [{"locale": null, "scope": null, "data": [
                        {"amount": 10, "currency": "EUR"}, 
                        {"amount": 14, "currency": "USD"}
                    ]}]
                },
                "created": "2017-04-18T12:30:45+02:30",
                "associations": {}
            }
        }');

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        $this->assertArraysAreEqual(['EUR_1', 'EUR_2', 'USD_1'], $product->getChannels()->map(function (ChannelInterface $channel) {
            return $channel->getCode();
        })->toArray());

        /** @var ProductVariantInterface[] $productVariants */
        $productVariants = $product->getVariants()->toArray();
        $productVariant = current($productVariants);

        Assert::assertSame(1000, $productVariant->getChannelPricingForChannel($this->getChannelByCode('EUR_1'))->getPrice());
        Assert::assertSame(1000, $productVariant->getChannelPricingForChannel($this->getChannelByCode('EUR_2'))->getPrice());
        Assert::assertSame(1400, $productVariant->getChannelPricingForChannel($this->getChannelByCode('USD_1'))->getPrice());
        Assert::assertNull($productVariant->getChannelPricingForChannel($this->getChannelByCode('GBP_1')));

        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "price": [{"locale": null, "scope": null, "data": [
                        {"amount": 9, "currency": "GBP"}, 
                        {"amount": 14, "currency": "USD"}
                    ]}]
                },
                "created": "2017-04-18T12:30:45+02:30",
                "associations": {}
            }
        }');

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        $this->assertArraysAreEqual(['GBP_1', 'USD_1'], $product->getChannels()->map(function (ChannelInterface $channel) {
            return $channel->getCode();
        })->toArray());

        /** @var ProductVariantInterface[] $productVariants */
        $productVariants = $product->getVariants()->toArray();
        $productVariant = current($productVariants);

        Assert::assertNull($productVariant->getChannelPricingForChannel($this->getChannelByCode('EUR_1')));
        Assert::assertNull($productVariant->getChannelPricingForChannel($this->getChannelByCode('EUR_2')));
        Assert::assertSame(900, $productVariant->getChannelPricingForChannel($this->getChannelByCode('GBP_1'))->getPrice());
        Assert::assertSame(1400, $productVariant->getChannelPricingForChannel($this->getChannelByCode('USD_1'))->getPrice());
    }

    private function getChannelByCode(string $code): ?ChannelInterface
    {
        return static::$kernel->getContainer()->get('sylius.repository.channel')->findOneBy(['code' => $code]);
    }
}
