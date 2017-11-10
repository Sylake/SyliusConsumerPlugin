<?php

declare(strict_types=1);

namespace Tests\Sylake\SyliusConsumerPlugin\Functional;

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

        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "subtitle": [{"locale": null, "scope": null, "data": "Foo bar (locale independent)"}]
                },
                "created": "2017-04-18T12:30:45+02:30",
                "associations": {}
            }
        }');

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertSame('Foo bar (locale independent)', $product->getAttributeByCodeAndLocale('subtitle', 'en_US')->getValue());
        Assert::assertSame('Foo bar (locale independent)', $product->getAttributeByCodeAndLocale('subtitle', 'de_DE')->getValue());

        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "subtitle": [{"locale": "en_US", "scope": null, "data": "Foo bar (en_US)"}]
                },
                "created": "2017-04-18T12:30:45+02:30",
                "associations": {}
            }
        }');

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

        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "short_description": [{"locale": null, "scope": null, "data": "Foo bar (locale independent)"}]
                },
                "created": "2017-04-18T12:30:45+02:30",
                "associations": {}
            }
        }');

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertSame('Foo bar (locale independent)', $product->getAttributeByCodeAndLocale('short_description', 'en_US')->getValue());
        Assert::assertSame('Foo bar (locale independent)', $product->getAttributeByCodeAndLocale('short_description', 'de_DE')->getValue());

        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "short_description": [{"locale": "en_US", "scope": null, "data": "Foo bar (en_US)"}]
                },
                "created": "2017-04-18T12:30:45+02:30",
                "associations": {}
            }
        }');

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

        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "color": [{"locale": null, "scope": null, "data": "black"}],
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}]
                },
                "created": "2017-04-18T12:30:45+02:30",
                "associations": {}
            }
        }');

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertSame('Black', $product->getAttributeByCodeAndLocale('color', 'en_US')->getValue());
        Assert::assertSame('Schwarz', $product->getAttributeByCodeAndLocale('color', 'de_DE')->getValue());

        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "color": [{"locale": "en_US", "scope": null, "data": "red"}],
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}]
                },
                "created": "2017-04-18T12:30:45+02:30",
                "associations": {}
            }
        }');

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

        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "color": [{"locale": null, "scope": null, "data": ["black", "red"]}],
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}]
                },
                "created": "2017-04-18T12:30:45+02:30",
                "associations": {}
            }
        }');

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertSame('Black, Red', $product->getAttributeByCodeAndLocale('color', 'en_US')->getValue());
        Assert::assertSame('Schwarz, Rot', $product->getAttributeByCodeAndLocale('color', 'de_DE')->getValue());

        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "color": [{"locale": "en_US", "scope": null, "data": ["black"]}],
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}]
                },
                "created": "2017-04-18T12:30:45+02:30",
                "associations": {}
            }
        }');

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

        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "awesome_cert": [{"locale": null, "scope": null, "data": true}]
                },
                "created": "2017-04-18T12:30:45+02:30",
                "associations": {}
            }
        }');

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertTrue($product->getAttributeByCodeAndLocale('awesome_cert', 'en_US')->getValue());
        Assert::assertTrue($product->getAttributeByCodeAndLocale('awesome_cert', 'de_DE')->getValue());

        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "awesome_cert": [{"locale": "en_US", "scope": null, "data": false}]
                },
                "created": "2017-04-18T12:30:45+02:30",
                "associations": {}
            }
        }');

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertFalse($product->getAttributeByCodeAndLocale('awesome_cert', 'en_US')->getValue());
        Assert::assertNull($product->getAttributeByCodeAndLocale('awesome_cert', 'de_DE'));
    }

    /**
     * @test
     */
    public function it_adds_and_updates_an_integer_attribute(): void
    {
        $this->consumeAttribute('megapixels', 'pim_catalog_number', ['en_US' => 'Megapixels']);

        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "megapixels": [{"locale": null, "scope": null, "data": 12}]
                },
                "created": "2017-04-18T12:30:45+02:30",
                "associations": {}
            }
        }');

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertSame('12', $product->getAttributeByCodeAndLocale('megapixels', 'en_US')->getValue());
        Assert::assertSame('12', $product->getAttributeByCodeAndLocale('megapixels', 'de_DE')->getValue());

        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "megapixels": [{"locale": "en_US", "scope": null, "data": 9}]
                },
                "created": "2017-04-18T12:30:45+02:30",
                "associations": {}
            }
        }');

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertSame('9', $product->getAttributeByCodeAndLocale('megapixels', 'en_US')->getValue());
        Assert::assertNull($product->getAttributeByCodeAndLocale('megapixels', 'de_DE'));
    }

    /**
     * @test
     */
    public function it_adds_and_updates_a_float_attribute(): void
    {
        $this->consumeAttribute('megapixels', 'pim_catalog_number', ['en_US' => 'Megapixels']);

        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "megapixels": [{"locale": null, "scope": null, "data": 12.3}]
                },
                "created": "2017-04-18T12:30:45+02:30",
                "associations": {}
            }
        }');

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertSame('12.3', $product->getAttributeByCodeAndLocale('megapixels', 'en_US')->getValue());
        Assert::assertSame('12.3', $product->getAttributeByCodeAndLocale('megapixels', 'de_DE')->getValue());

        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "megapixels": [{"locale": "en_US", "scope": null, "data": 9.7}]
                },
                "created": "2017-04-18T12:30:45+02:30",
                "associations": {}
            }
        }');

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertSame('9.7', $product->getAttributeByCodeAndLocale('megapixels', 'en_US')->getValue());
        Assert::assertNull($product->getAttributeByCodeAndLocale('megapixels', 'de_DE'));
    }

    /**
     * @test
     */
    public function it_adds_and_updates_a_date_attribute(): void
    {
        $this->consumeAttribute('release_date', 'pim_catalog_date', ['en_US' => 'Release date']);

        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "release_date": [{"locale": null, "scope": null, "data": "2017-06-01T00:00:00+02:30"}]
                },
                "created": "2017-04-18T12:30:45+02:30",
                "associations": {}
            }
        }');

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertEquals(\DateTime::createFromFormat('!Y-m-d', '2017-06-01'), $product->getAttributeByCodeAndLocale('release_date', 'en_US')->getValue());
        Assert::assertEquals(\DateTime::createFromFormat('!Y-m-d', '2017-06-01'), $product->getAttributeByCodeAndLocale('release_date', 'de_DE')->getValue());

        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "release_date": [{"locale": "en_US", "scope": null, "data": "2017-06-06T00:00:00+02:30"}]
                },
                "created": "2017-04-18T12:30:45+02:30",
                "associations": {}
            }
        }');

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertEquals(\DateTime::createFromFormat('!Y-m-d', '2017-06-06'), $product->getAttributeByCodeAndLocale('release_date', 'en_US')->getValue());
        Assert::assertNull($product->getAttributeByCodeAndLocale('release_date', 'de_DE'));
    }

    /**
     * @test
     */
    public function it_ignores_unexisting_attributes()
    {
        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "unexisting": [{"locale": null, "scope": null, "data": "foo"}]
                },
                "created": "2017-04-18T12:30:45+02:30",
                "associations": {}
            }
        }');

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertNull($product->getAttributeByCodeAndLocale('unexisting', 'en_US'));
        Assert::assertNull($product->getAttributeByCodeAndLocale('unexisting', 'de_DE'));
    }

    /**
     * @test
     */
    public function it_ignores_null_attributes()
    {
        $this->consumeAttribute('subtitle', 'pim_catalog_text', ['en_US' => 'Subtitle']);

        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "subtitle": [{"locale": null, "scope": null, "data": null}]
                },
                "created": "2017-04-18T12:30:45+02:30",
                "associations": {}
            }
        }');

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertNull($product->getAttributeByCodeAndLocale('subtitle', 'en_US'));
        Assert::assertNull($product->getAttributeByCodeAndLocale('subtitle', 'de_DE'));
    }

    /**
     * @test
     */
    public function it_ignores_empty_attributes()
    {
        $this->consumeAttribute('subtitle', 'pim_catalog_text', ['en_US' => 'Subtitle']);

        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "subtitle": [{"locale": null, "scope": null, "data": ""}]
                },
                "created": "2017-04-18T12:30:45+02:30",
                "associations": {}
            }
        }');

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertNull($product->getAttributeByCodeAndLocale('subtitle', 'en_US'));
        Assert::assertNull($product->getAttributeByCodeAndLocale('subtitle', 'de_DE'));
    }

    /**
     * @test
     */
    public function it_removes_attributes_no_longer_existing_in_a_product()
    {
        $this->consumeAttribute('subtitle', 'pim_catalog_text', ['en_US' => 'Subtitle']);

        $this->consume('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "subtitle": [{"locale": "en_US", "scope": null, "data": "English subtitle"}]
                },
                "created": "2017-04-18T12:30:45+02:30",
                "associations": {}
            }
        }');

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertSame('English subtitle', $product->getAttributeByCodeAndLocale('subtitle', 'en_US')->getValue());
        Assert::assertNull($product->getAttributeByCodeAndLocale('subtitle', 'de_DE'));

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
                "associations": {}
            }
        }');

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertNull($product->getAttributeByCodeAndLocale('subtitle', 'en_US'));
        Assert::assertNull($product->getAttributeByCodeAndLocale('subtitle', 'de_DE'));
    }
}
