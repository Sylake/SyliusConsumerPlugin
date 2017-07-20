<?php

declare(strict_types=1);

namespace Tests\Sylake\SyliusConsumerPlugin\Functional;

use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\Assert;
use Sylius\Component\Core\Model\ProductInterface;

/**
 * @author Kamil Kokot <kamil@kokot.me>
 */
final class ProductAttributeSynchronizationTest extends ProductSynchronizationTestCase
{
    /**
     * @test
     */
    public function it_adds_a_new_product_with_attributes()
    {
        $this->consumeAttribute('main_color', 'pim_catalog_simpleselect', ['en_US' => 'Main color']);
        $this->consumeAttribute('tshirt_style', 'pim_catalog_simpleselect', ['en_US' => 'T-Shirt style']);

        $this->consumeAttributeOption('main_color', 'black', ['en_US' => 'Black', 'de_DE' => 'Schwarz']);
        $this->consumeAttributeOption('tshirt_style', 'crewneck', ['en_US' => 'Crewneck', 'de_DE' => 'Rundhalsausschnitt']);
        $this->consumeAttributeOption('tshirt_style', 'short_sleeve', ['en_US' => 'Short sleeve', 'de_DE' => 'Kurzarm']);

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "main_color": [{"locale": null, "scope": null, "data": "black"}],
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "subtitle": [{"locale": "en_US", "scope": null, "data": "English subtitle"}],
                    "tshirt_style": [{"locale": null, "scope": null, "data": ["crewneck", "short_sleeve"]}]
                },
                "created": "2017-04-18T16:12:55+02:00",
                "associations": {}
            }
        }'));

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);

        Assert::assertSame('Black', $product->getAttributeByCodeAndLocale('main_color', 'en_US')->getValue());
        Assert::assertSame('Schwarz', $product->getAttributeByCodeAndLocale('main_color', 'de_DE')->getValue());

        Assert::assertSame('Crewneck, Short sleeve', $product->getAttributeByCodeAndLocale('tshirt_style', 'en_US')->getValue());
        Assert::assertSame('Rundhalsausschnitt, Kurzarm', $product->getAttributeByCodeAndLocale('tshirt_style', 'de_DE')->getValue());

        Assert::assertNull($product->getAttributeByCodeAndLocale('subtitle', 'en_US'));
        Assert::assertNull($product->getAttributeByCodeAndLocale('subtitle', 'de_DE'));
    }

    /**
     * @test
     */
    public function it_updates_an_existing_product_with_attributes()
    {
        $this->consumeAttribute('main_color', 'pim_catalog_simpleselect', ['en_US' => 'Main color']);
        $this->consumeAttribute('tshirt_style', 'pim_catalog_simpleselect', ['en_US' => 'T-Shirt style']);
        $this->consumeAttribute('subtitle', 'pim_catalog_text', ['en_US' => 'Subtitle']);

        $this->consumeAttributeOption('main_color', 'black', ['en_US' => 'Black', 'de_DE' => 'Schwarz']);

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "main_color": [{"locale": null, "scope": null, "data": "black"}],
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "subtitle": [{"locale": "de_DE", "scope": null, "data": "German subtitle"}],
                    "tshirt_style": [{"locale": null, "scope": null, "data": ["crewneck", "short_sleeve"]}]
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
                    "main_color": [{"locale": null, "scope": null, "data": "red"}],
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "subtitle": [{"locale": "en_US", "scope": null, "data": "English subtitle"}]
                },
                "created": "2017-04-18T16:12:55+02:00",
                "associations": {}
            }
        }'));

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);

        Assert::assertSame('red', $product->getAttributeByCodeAndLocale('main_color', 'en_US')->getValue());

        Assert::assertNull($product->getAttributeByCodeAndLocale('tshirt_style', 'en_US'));

        Assert::assertNull($product->getAttributeByCodeAndLocale('picture', 'en_US'));
        Assert::assertNull($product->getAttributeByCodeAndLocale('picture', 'de_DE'));

        Assert::assertSame('English subtitle', $product->getAttributeByCodeAndLocale('subtitle', 'en_US')->getValue());
        Assert::assertNull($product->getAttributeByCodeAndLocale('subtitle', 'de_DE'));
    }
}
