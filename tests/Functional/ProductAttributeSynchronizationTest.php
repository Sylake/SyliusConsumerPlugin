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
    public function it_adds_and_updates_a_text_attribute(): void
    {
        $this->consumeAttribute('subtitle', 'pim_catalog_text', ['en_US' => 'Subtitle']);

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "subtitle": [{"locale": null, "scope": null, "data": "Foo bar (locale independent)"}]
                },
                "created": "2017-04-18T16:12:55+02:00",
                "associations": {}
            }
        }'));

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertSame('Foo bar (locale independent)', $product->getAttributeByCodeAndLocale('subtitle', 'en_US')->getValue());
        Assert::assertSame('Foo bar (locale independent)', $product->getAttributeByCodeAndLocale('subtitle', 'de_DE')->getValue());

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "subtitle": [{"locale": "en_US", "scope": null, "data": "Foo bar (en_US)"}]
                },
                "created": "2017-04-18T16:12:55+02:00",
                "associations": {}
            }
        }'));

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertSame('Foo bar (en_US)', $product->getAttributeByCodeAndLocale('subtitle', 'en_US')->getValue());
        Assert::assertNull($product->getAttributeByCodeAndLocale('subtitle', 'de_DE'));
    }

    /**
     * @test
     */
    public function it_adds_and_updates_a_textarea_attribute(): void
    {
        $this->consumeAttribute('short_description', 'pim_catalog_textarea', ['en_US' => 'Short description']);

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "short_description": [{"locale": null, "scope": null, "data": "Foo bar (locale independent)"}]
                },
                "created": "2017-04-18T16:12:55+02:00",
                "associations": {}
            }
        }'));

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertSame('Foo bar (locale independent)', $product->getAttributeByCodeAndLocale('short_description', 'en_US')->getValue());
        Assert::assertSame('Foo bar (locale independent)', $product->getAttributeByCodeAndLocale('short_description', 'de_DE')->getValue());

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "short_description": [{"locale": "en_US", "scope": null, "data": "Foo bar (en_US)"}]
                },
                "created": "2017-04-18T16:12:55+02:00",
                "associations": {}
            }
        }'));

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertSame('Foo bar (en_US)', $product->getAttributeByCodeAndLocale('short_description', 'en_US')->getValue());
        Assert::assertNull($product->getAttributeByCodeAndLocale('short_description', 'de_DE'));
    }

    /**
     * @test
     */
    public function it_adds_and_updates_a_simple_select_attribute(): void
    {
        $this->consumeAttribute('color', 'pim_catalog_simpleselect', ['en_US' => 'Main color']);

        $this->consumeAttributeOption('color', 'black', ['en_US' => 'Black', 'de_DE' => 'Schwarz']);
        $this->consumeAttributeOption('color', 'red', ['en_US' => 'Red', 'de_DE' => 'Rot']);

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "color": [{"locale": null, "scope": null, "data": "black"}],
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}]
                },
                "created": "2017-04-18T16:12:55+02:00",
                "associations": {}
            }
        }'));

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertSame('Black', $product->getAttributeByCodeAndLocale('color', 'en_US')->getValue());
        Assert::assertSame('Schwarz', $product->getAttributeByCodeAndLocale('color', 'de_DE')->getValue());

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "color": [{"locale": "en_US", "scope": null, "data": "red"}],
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}]
                },
                "created": "2017-04-18T16:12:55+02:00",
                "associations": {}
            }
        }'));

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertSame('Red', $product->getAttributeByCodeAndLocale('color', 'en_US')->getValue());
        Assert::assertNull($product->getAttributeByCodeAndLocale('color', 'de_DE'));
    }

    /**
     * @test
     */
    public function it_adds_and_updates_a_multi_select_attribute(): void
    {
        $this->consumeAttribute('color', 'pim_catalog_multiselect', ['en_US' => 'Main color']);

        $this->consumeAttributeOption('color', 'black', ['en_US' => 'Black', 'de_DE' => 'Schwarz']);
        $this->consumeAttributeOption('color', 'red', ['en_US' => 'Red', 'de_DE' => 'Rot']);

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "color": [{"locale": null, "scope": null, "data": ["black", "red"]}],
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}]
                },
                "created": "2017-04-18T16:12:55+02:00",
                "associations": {}
            }
        }'));

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertSame('Black, Red', $product->getAttributeByCodeAndLocale('color', 'en_US')->getValue());
        Assert::assertSame('Schwarz, Rot', $product->getAttributeByCodeAndLocale('color', 'de_DE')->getValue());

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "color": [{"locale": "en_US", "scope": null, "data": ["black"]}],
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}]
                },
                "created": "2017-04-18T16:12:55+02:00",
                "associations": {}
            }
        }'));

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertSame('Black', $product->getAttributeByCodeAndLocale('color', 'en_US')->getValue());
        Assert::assertNull($product->getAttributeByCodeAndLocale('color', 'de_DE'));
    }

    /**
     * @test
     */
    public function it_adds_and_updates_a_bool_attribute(): void
    {
        $this->consumeAttribute('awesome_cert', 'pim_catalog_boolean', ['en_US' => 'Certificate of awesomeness']);

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "awesome_cert": [{"locale": null, "scope": null, "data": true}]
                },
                "created": "2017-04-18T16:12:55+02:00",
                "associations": {}
            }
        }'));

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertTrue($product->getAttributeByCodeAndLocale('subtitle', 'en_US')->getValue());
        Assert::assertTrue($product->getAttributeByCodeAndLocale('subtitle', 'de_DE')->getValue());

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "awesome_cert": [{"locale": "en_US", "scope": null, "data": false}]
                },
                "created": "2017-04-18T16:12:55+02:00",
                "associations": {}
            }
        }'));

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertFalse($product->getAttributeByCodeAndLocale('subtitle', 'en_US')->getValue());
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
