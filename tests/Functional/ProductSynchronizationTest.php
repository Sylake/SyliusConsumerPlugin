<?php

namespace Tests\Sylake\SyliusConsumerPlugin\Functional;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\Assert;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author Kamil Kokot <kamil@kokot.me>
 */
final class ProductSynchronizationTest extends KernelTestCase
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ConsumerInterface
     */
    private $consumer;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        self::bootKernel();

        $this->entityManager = static::$kernel->getContainer()->get('doctrine')->getManager();
        $this->productRepository = static::$kernel->getContainer()->get('sylius.repository.product');
        $this->consumer = static::$kernel->getContainer()->get('rabbitmq_simplebus.consumer');

        (new ORMPurger($this->entityManager))->purge();
    }

    /**
     * @test
     */
    public function it_adds_a_new_product_with_basic_product_information()
    {
        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_product_created",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "family": "tshirts",
                "groups": [],
                "variant_group": "akeneo_tshirt",
                "categories": ["goodies", "tshirts"],
                "enabled": true,
                "values": {
                    "sku": [{"locale": null, "scope": null, "data": "AKNTS_BPXS"}],
                    "clothing_size": [{"locale": null, "scope": null, "data": "xs"}],
                    "main_color": [{"locale": null, "scope": null, "data": "black"}],
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "secondary_color": [{"locale": null, "scope": null, "data": "purple"}],
                    "tshirt_materials": [{"locale": null, "scope": null, "data": "cotton"}],
                    "tshirt_style": [{"locale": null, "scope": null, "data": ["crewneck", "short_sleeve"]}],
                    "price": [{"locale": null, "scope": null, "data": [{"amount": 10, "currency": "EUR"}, {"amount": 14, "currency": "USD"}]}],
                    "description": [{"locale": "de_DE", "scope": "mobile", "data": "T-Shirt description"}],
                    "picture": [{"locale": null, "scope": null, "data": null}]
                },
                "created": "2017-04-18T16:12:55+02:00",
                "updated": "2017-04-18T16:12:55+02:00",
                "associations": {"SUBSTITUTION": {"groups": [], "products": ["AKNTS_WPXS", "AKNTS_PBXS", "AKNTS_PWXS"]}}
            },
            "recordedOn": "2017-05-22 10:13:34"
        }'));

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertSame('Akeneo T-Shirt black and purple with short sleeve', $product->getTranslation('en_US')->getName());
        Assert::assertSame('aknts-bpxs-akeneo-t-shirt-black-and-purple-with-short-sleeve', $product->getTranslation('en_US')->getSlug());
        Assert::assertSame('T-Shirt description', $product->getTranslation('en_US')->getDescription());
        Assert::assertEquals(\DateTime::createFromFormat(\DateTime::W3C, '2017-04-18T16:12:55+02:00'), $product->getCreatedAt());
        Assert::assertTrue($product->isEnabled());
    }

    /**
     * @test
     */
    public function it_updates_an_existing_product_with_basic_product_information()
    {
        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_product_created",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "family": "tshirts",
                "groups": [],
                "variant_group": "akeneo_tshirt",
                "categories": ["goodies", "tshirts"],
                "enabled": true,
                "values": {
                    "sku": [{"locale": null, "scope": null, "data": "AKNTS_BPXS"}],
                    "clothing_size": [{"locale": null, "scope": null, "data": "xs"}],
                    "main_color": [{"locale": null, "scope": null, "data": "black"}],
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "secondary_color": [{"locale": null, "scope": null, "data": "purple"}],
                    "tshirt_materials": [{"locale": null, "scope": null, "data": "cotton"}],
                    "tshirt_style": [{"locale": null, "scope": null, "data": ["crewneck", "short_sleeve"]}],
                    "price": [{"locale": null, "scope": null, "data": [{"amount": 10, "currency": "EUR"}, {"amount": 14, "currency": "USD"}]}],
                    "description": [{"locale": "de_DE", "scope": "mobile", "data": "T-Shirt description"}],
                    "picture": [{"locale": null, "scope": null, "data": null}]
                },
                "created": "2017-04-18T16:12:55+02:00",
                "updated": "2017-04-18T16:12:55+02:00",
                "associations": {"SUBSTITUTION": {"groups": [], "products": ["AKNTS_WPXS", "AKNTS_PBXS", "AKNTS_PWXS"]}}
            },
            "recordedOn": "2017-05-22 10:13:34"
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_product_created",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "family": "tshirts",
                "groups": [],
                "variant_group": "akeneo_tshirt",
                "categories": ["goodies", "tshirts"],
                "enabled": false,
                "values": {
                    "sku": [{"locale": null, "scope": null, "data": "AKNTS_BPXS"}],
                    "clothing_size": [{"locale": null, "scope": null, "data": "xs"}],
                    "main_color": [{"locale": null, "scope": null, "data": "black"}],
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve (updated)"}],
                    "secondary_color": [{"locale": null, "scope": null, "data": "purple"}],
                    "tshirt_materials": [{"locale": null, "scope": null, "data": "cotton"}],
                    "tshirt_style": [{"locale": null, "scope": null, "data": ["crewneck", "short_sleeve"]}],
                    "price": [{"locale": null, "scope": null, "data": [{"amount": 10, "currency": "EUR"}, {"amount": 14, "currency": "USD"}]}],
                    "description": [{"locale": "de_DE", "scope": "mobile", "data": "T-Shirt description (updated)"}],
                    "picture": [{"locale": null, "scope": null, "data": null}]
                },
                "created": "2017-04-18T16:12:58+02:00",
                "updated": "2017-04-18T16:12:55+02:00",
                "associations": {"SUBSTITUTION": {"groups": [], "products": ["AKNTS_WPXS", "AKNTS_PBXS", "AKNTS_PWXS"]}}
            },
            "recordedOn": "2017-05-22 10:13:34"
        }'));

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertSame('Akeneo T-Shirt black and purple with short sleeve (updated)', $product->getTranslation('en_US')->getName());
        Assert::assertSame('aknts-bpxs-akeneo-t-shirt-black-and-purple-with-short-sleeve-updated', $product->getTranslation('en_US')->getSlug());
        Assert::assertSame('T-Shirt description (updated)', $product->getTranslation('en_US')->getDescription());
        Assert::assertEquals(\DateTime::createFromFormat(\DateTime::W3C, '2017-04-18T16:12:58+02:00'), $product->getCreatedAt());
        Assert::assertFalse($product->isEnabled());
    }

    /**
     * @test
     */
    public function it_adds_a_new_product_with_taxons()
    {
        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_category_created",
            "payload": {
                "code": "master",
                "parent": null,
                "labels": {"en_US": "Master catalog"}
            },
            "recordedOn": "2017-05-22 14:24:40"
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_category_created",
            "payload": {
                "code": "tshirts",
                "parent": "master",
                "labels": {"en_US": "T-Shirts"}
            },
            "recordedOn": "2017-05-22 14:24:40"
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_category_created",
            "payload": {
                "code": "goodies",
                "parent": "master",
                "labels": {"en_US": "Goodies"}
            },
            "recordedOn": "2017-05-22 14:24:40"
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_product_created",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "family": "tshirts",
                "groups": [],
                "variant_group": "akeneo_tshirt",
                "categories": ["goodies", "tshirts"],
                "enabled": true,
                "values": {
                    "sku": [{"locale": null, "scope": null, "data": "AKNTS_BPXS"}],
                    "clothing_size": [{"locale": null, "scope": null, "data": "xs"}],
                    "main_color": [{"locale": null, "scope": null, "data": "black"}],
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "secondary_color": [{"locale": null, "scope": null, "data": "purple"}],
                    "tshirt_materials": [{"locale": null, "scope": null, "data": "cotton"}],
                    "tshirt_style": [{"locale": null, "scope": null, "data": ["crewneck", "short_sleeve"]}],
                    "price": [{"locale": null, "scope": null, "data": [{"amount": 10, "currency": "EUR"}, {"amount": 14, "currency": "USD"}]}],
                    "description": [{"locale": "de_DE", "scope": "mobile", "data": "T-Shirt description"}],
                    "picture": [{"locale": null, "scope": null, "data": null}]
                },
                "created": "2017-04-18T16:12:55+02:00",
                "updated": "2017-04-18T16:12:55+02:00",
                "associations": {"SUBSTITUTION": {"groups": [], "products": ["AKNTS_WPXS", "AKNTS_PBXS", "AKNTS_PWXS"]}}
            },
            "recordedOn": "2017-05-22 10:13:34"
        }'));

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertSame('tshirts', $product->getMainTaxon()->getCode());
        $this->assertArraysAreEqual(['goodies', 'tshirts'], $product->getTaxons()->map(function (TaxonInterface $taxon) {
            return $taxon->getCode();
        })->toArray());
    }

    /**
     * @test
     */
    public function it_updates_an_existing_product_with_taxons()
    {
        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_category_created",
            "payload": {
                "code": "master",
                "parent": null,
                "labels": {"en_US": "Master catalog"}
            },
            "recordedOn": "2017-05-22 14:24:40"
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_category_created",
            "payload": {
                "code": "tshirts",
                "parent": "master",
                "labels": {"en_US": "T-Shirts"}
            },
            "recordedOn": "2017-05-22 14:24:40"
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_category_created",
            "payload": {
                "code": "goodies",
                "parent": "master",
                "labels": {"en_US": "Goodies"}
            },
            "recordedOn": "2017-05-22 14:24:40"
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_product_created",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "family": "tshirts",
                "groups": [],
                "variant_group": "akeneo_tshirt",
                "categories": ["goodies", "tshirts"],
                "enabled": true,
                "values": {
                    "sku": [{"locale": null, "scope": null, "data": "AKNTS_BPXS"}],
                    "clothing_size": [{"locale": null, "scope": null, "data": "xs"}],
                    "main_color": [{"locale": null, "scope": null, "data": "black"}],
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "secondary_color": [{"locale": null, "scope": null, "data": "purple"}],
                    "tshirt_materials": [{"locale": null, "scope": null, "data": "cotton"}],
                    "tshirt_style": [{"locale": null, "scope": null, "data": ["crewneck", "short_sleeve"]}],
                    "price": [{"locale": null, "scope": null, "data": [{"amount": 10, "currency": "EUR"}, {"amount": 14, "currency": "USD"}]}],
                    "description": [{"locale": "de_DE", "scope": "mobile", "data": "T-Shirt description"}],
                    "picture": [{"locale": null, "scope": null, "data": null}]
                },
                "created": "2017-04-18T16:12:55+02:00",
                "updated": "2017-04-18T16:12:55+02:00",
                "associations": {"SUBSTITUTION": {"groups": [], "products": ["AKNTS_WPXS", "AKNTS_PBXS", "AKNTS_PWXS"]}}
            },
            "recordedOn": "2017-05-22 10:13:34"
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_product_created",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "family": null,
                "groups": [],
                "variant_group": "akeneo_tshirt",
                "categories": ["goodies"],
                "enabled": true,
                "values": {
                    "sku": [{"locale": null, "scope": null, "data": "AKNTS_BPXS"}],
                    "clothing_size": [{"locale": null, "scope": null, "data": "xs"}],
                    "main_color": [{"locale": null, "scope": null, "data": "black"}],
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "secondary_color": [{"locale": null, "scope": null, "data": "purple"}],
                    "tshirt_materials": [{"locale": null, "scope": null, "data": "cotton"}],
                    "tshirt_style": [{"locale": null, "scope": null, "data": ["crewneck", "short_sleeve"]}],
                    "price": [{"locale": null, "scope": null, "data": [{"amount": 10, "currency": "EUR"}, {"amount": 14, "currency": "USD"}]}],
                    "description": [{"locale": "de_DE", "scope": "mobile", "data": "T-Shirt description"}],
                    "picture": [{"locale": null, "scope": null, "data": null}]
                },
                "created": "2017-04-18T16:12:55+02:00",
                "updated": "2017-04-18T16:12:55+02:00",
                "associations": {"SUBSTITUTION": {"groups": [], "products": ["AKNTS_WPXS", "AKNTS_PBXS", "AKNTS_PWXS"]}}
            },
            "recordedOn": "2017-05-22 10:13:34"
        }'));

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertNull($product->getMainTaxon());
        $this->assertArraysAreEqual(['goodies'], $product->getTaxons()->map(function (TaxonInterface $taxon) {
            return $taxon->getCode();
        })->toArray());
    }

    /**
     * @test
     */
    public function it_adds_a_new_product_with_attributes()
    {
        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_attribute_created",
            "payload": {
                "code": "main_color",
                "type": "pim_catalog_simpleselect",
                "group": "color",
                "unique": false,
                "useable_as_grid_filter": true,
                "allowed_extensions": [],
                "metric_family": null,
                "default_metric_unit": null,
                "reference_data_name": null,
                "available_locales": [],
                "max_characters": null,
                "validation_rule": null,
                "validation_regexp": null,
                "wysiwyg_enabled": null,
                "number_min": null,
                "number_max": null,
                "decimals_allowed": null,
                "negative_allowed": null,
                "date_min": null,
                "date_max": null,
                "max_file_size": null,
                "minimum_input_length": null,
                "sort_order": 1,
                "localizable": false,
                "scopable": false,
                "labels": {"en_US": "Main color"}
            },
            "recordedOn": "2017-05-22 14:16:59"
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_attribute_created",
            "payload": {
                "code": "tshirt_style",
                "type": "pim_catalog_simpleselect",
                "group": "color",
                "unique": false,
                "useable_as_grid_filter": true,
                "allowed_extensions": [],
                "metric_family": null,
                "default_metric_unit": null,
                "reference_data_name": null,
                "available_locales": [],
                "max_characters": null,
                "validation_rule": null,
                "validation_regexp": null,
                "wysiwyg_enabled": null,
                "number_min": null,
                "number_max": null,
                "decimals_allowed": null,
                "negative_allowed": null,
                "date_min": null,
                "date_max": null,
                "max_file_size": null,
                "minimum_input_length": null,
                "sort_order": 1,
                "localizable": false,
                "scopable": false,
                "labels": {"en_US": "T-Shirt style"}
            },
            "recordedOn": "2017-05-22 14:16:59"
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_product_created",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "family": "tshirts",
                "groups": [],
                "variant_group": "akeneo_tshirt",
                "categories": ["goodies", "tshirts"],
                "enabled": true,
                "values": {
                    "sku": [{"locale": null, "scope": null, "data": "AKNTS_BPXS"}],
                    "clothing_size": [{"locale": null, "scope": null, "data": "xs"}],
                    "main_color": [{"locale": null, "scope": null, "data": "black"}],
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "secondary_color": [{"locale": null, "scope": null, "data": "purple"}],
                    "tshirt_materials": [{"locale": null, "scope": null, "data": "cotton"}],
                    "tshirt_style": [{"locale": null, "scope": null, "data": ["crewneck", "short_sleeve"]}],
                    "price": [{"locale": null, "scope": null, "data": [{"amount": 10, "currency": "EUR"}, {"amount": 14, "currency": "USD"}]}],
                    "description": [{"locale": "de_DE", "scope": "mobile", "data": "T-Shirt description"}],
                    "picture": [{"locale": null, "scope": null, "data": null}]
                },
                "created": "2017-04-18T16:12:55+02:00",
                "updated": "2017-04-18T16:12:55+02:00",
                "associations": {"SUBSTITUTION": {"groups": [], "products": ["AKNTS_WPXS", "AKNTS_PBXS", "AKNTS_PWXS"]}}
            },
            "recordedOn": "2017-05-22 10:13:34"
        }'));

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertSame('black', $product->getAttributeByCodeAndLocale('main_color')->getValue());
        Assert::assertSame('crewneck, short_sleeve', $product->getAttributeByCodeAndLocale('tshirt_style')->getValue());
    }

    /**
     * @test
     */
    public function it_updates_an_existing_product_with_attributes()
    {
        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_attribute_created",
            "payload": {
                "code": "main_color",
                "type": "pim_catalog_simpleselect",
                "group": "color",
                "unique": false,
                "useable_as_grid_filter": true,
                "allowed_extensions": [],
                "metric_family": null,
                "default_metric_unit": null,
                "reference_data_name": null,
                "available_locales": [],
                "max_characters": null,
                "validation_rule": null,
                "validation_regexp": null,
                "wysiwyg_enabled": null,
                "number_min": null,
                "number_max": null,
                "decimals_allowed": null,
                "negative_allowed": null,
                "date_min": null,
                "date_max": null,
                "max_file_size": null,
                "minimum_input_length": null,
                "sort_order": 1,
                "localizable": false,
                "scopable": false,
                "labels": {"en_US": "Main color"}
            },
            "recordedOn": "2017-05-22 14:16:59"
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_attribute_created",
            "payload": {
                "code": "tshirt_style",
                "type": "pim_catalog_simpleselect",
                "group": "color",
                "unique": false,
                "useable_as_grid_filter": true,
                "allowed_extensions": [],
                "metric_family": null,
                "default_metric_unit": null,
                "reference_data_name": null,
                "available_locales": [],
                "max_characters": null,
                "validation_rule": null,
                "validation_regexp": null,
                "wysiwyg_enabled": null,
                "number_min": null,
                "number_max": null,
                "decimals_allowed": null,
                "negative_allowed": null,
                "date_min": null,
                "date_max": null,
                "max_file_size": null,
                "minimum_input_length": null,
                "sort_order": 1,
                "localizable": false,
                "scopable": false,
                "labels": {"en_US": "T-Shirt style"}
            },
            "recordedOn": "2017-05-22 14:16:59"
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_product_created",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "family": "tshirts",
                "groups": [],
                "variant_group": "akeneo_tshirt",
                "categories": ["goodies", "tshirts"],
                "enabled": true,
                "values": {
                    "sku": [{"locale": null, "scope": null, "data": "AKNTS_BPXS"}],
                    "clothing_size": [{"locale": null, "scope": null, "data": "xs"}],
                    "main_color": [{"locale": null, "scope": null, "data": "black"}],
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "secondary_color": [{"locale": null, "scope": null, "data": "purple"}],
                    "tshirt_materials": [{"locale": null, "scope": null, "data": "cotton"}],
                    "tshirt_style": [{"locale": null, "scope": null, "data": ["crewneck", "short_sleeve"]}],
                    "price": [{"locale": null, "scope": null, "data": [{"amount": 10, "currency": "EUR"}, {"amount": 14, "currency": "USD"}]}],
                    "description": [{"locale": "de_DE", "scope": "mobile", "data": "T-Shirt description"}],
                    "picture": [{"locale": null, "scope": null, "data": null}]
                },
                "created": "2017-04-18T16:12:55+02:00",
                "updated": "2017-04-18T16:12:55+02:00",
                "associations": {"SUBSTITUTION": {"groups": [], "products": ["AKNTS_WPXS", "AKNTS_PBXS", "AKNTS_PWXS"]}}
            },
            "recordedOn": "2017-05-22 10:13:34"
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_product_created",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "family": "tshirts",
                "groups": [],
                "variant_group": "akeneo_tshirt",
                "categories": ["goodies", "tshirts"],
                "enabled": true,
                "values": {
                    "sku": [{"locale": null, "scope": null, "data": "AKNTS_BPXS"}],
                    "clothing_size": [{"locale": null, "scope": null, "data": "xs"}],
                    "main_color": [{"locale": null, "scope": null, "data": "red"}],
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "secondary_color": [{"locale": null, "scope": null, "data": "purple"}],
                    "tshirt_materials": [{"locale": null, "scope": null, "data": "cotton"}],
                    "price": [{"locale": null, "scope": null, "data": [{"amount": 10, "currency": "EUR"}, {"amount": 14, "currency": "USD"}]}],
                    "description": [{"locale": "de_DE", "scope": "mobile", "data": "T-Shirt description"}],
                    "picture": [{"locale": null, "scope": null, "data": null}]
                },
                "created": "2017-04-18T16:12:55+02:00",
                "updated": "2017-04-18T16:12:55+02:00",
                "associations": {"SUBSTITUTION": {"groups": [], "products": ["AKNTS_WPXS", "AKNTS_PBXS", "AKNTS_PWXS"]}}
            },
            "recordedOn": "2017-05-22 10:13:34"
        }'));

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertSame('red', $product->getAttributeByCodeAndLocale('main_color')->getValue());
        Assert::assertNull($product->getAttributeByCodeAndLocale('tshirt_style'));
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $this->entityManager->clear();
        $this->entityManager = null;
    }

    private function assertArraysAreEqual(array $expectedArray, array $actualArray)
    {
        Assert::assertSame(count($expectedArray), count($actualArray));

        foreach ($expectedArray as $expectedElement) {
            Assert::assertTrue(in_array($expectedElement, $actualArray, true));
        }
    }
}
