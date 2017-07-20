<?php

namespace Tests\Sylake\SyliusConsumerPlugin\Functional;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\Assert;
use Sylius\Bundle\FixturesBundle\Fixture\FixtureInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductImageInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Locale\Model\Locale;
use Sylius\Component\Product\Model\ProductAssociationInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
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

        /** @var FixtureInterface $localeFixture */
        $localeFixture = static::$kernel->getContainer()->get('sylius.fixture.locale');
        $localeFixture->load(['locales' => ['de_DE']]);

        /** @var FixtureInterface $currencyFixture */
        $currencyFixture = static::$kernel->getContainer()->get('sylius.fixture.currency');
        $currencyFixture->load(['currencies' => ['EUR', 'USD', 'GBP']]);

        /** @var FixtureInterface $channelFixture */
        $channelFixture = static::$kernel->getContainer()->get('sylius.fixture.channel');
        $channelFixture->load(['custom' => [
            ['code' => 'EUR_1', 'default_tax_zone' => null, 'currencies' => ['EUR']],
            ['code' => 'EUR_2', 'default_tax_zone' => null, 'currencies' => ['EUR']],
            ['code' => 'USD_1', 'default_tax_zone' => null, 'currencies' => ['USD']],
            ['code' => 'GBP_1', 'default_tax_zone' => null, 'currencies' => ['GBP']],
        ]]);
    }

    /**
     * @test
     */
    public function it_adds_a_new_product_with_basic_product_information()
    {
        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "description": [{"locale": "en_US", "scope": "mobile", "data": "T-Shirt description"}]
                },
                "created": "2017-04-18T16:12:55+02:00",
                "updated": "2017-04-18T16:12:55+02:00",
                "associations": {}
            }
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
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "description": [{"locale": "en_US", "scope": "mobile", "data": "T-Shirt description"}]
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
                "enabled": false,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve (updated)"}],
                    "description": [{"locale": "en_US", "scope": "mobile", "data": "T-Shirt description (updated)"}]
                },
                "created": "2017-04-18T16:12:58+02:00",
                "associations": {}
            }
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

    /**
     * @test
     */
    public function it_adds_a_new_product_with_taxons()
    {
        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_category_updated",
            "payload": {
                "code": "master",
                "parent": null,
                "labels": {"en_US": "Master catalog"}
            }
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_category_updated",
            "payload": {
                "code": "master__goodies",
                "parent": "master",
                "labels": {"en_US": "Goodies"}
            }
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_category_updated",
            "payload": {
                "code": "master__goodies__tshirts",
                "parent": "master__goodies",
                "labels": {"en_US": "T-Shirts"}
            }
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": ["master__goodies", "master__goodies__tshirts"],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}]
                },
                "created": "2017-04-18T16:12:55+02:00",
                "associations": {}
            }
        }'));

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertSame('master__goodies__tshirts', $product->getMainTaxon()->getCode());
        $this->assertArraysAreEqual(['master__goodies', 'master__goodies__tshirts'], $product->getTaxons()->map(function (TaxonInterface $taxon) {
            return $taxon->getCode();
        })->toArray());
    }

    /**
     * @test
     */
    public function it_updates_an_existing_product_with_taxons()
    {
        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_category_updated",
            "payload": {
                "code": "master",
                "parent": null,
                "labels": {"en_US": "Master catalog"}
            }
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_category_updated",
            "payload": {
                "code": "master__goodies",
                "parent": "master",
                "labels": {"en_US": "Goodies"}
            }
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_category_updated",
            "payload": {
                "code": "master__goodies__tshirts",
                "parent": "master__goodies",
                "labels": {"en_US": "T-Shirts"}
            }
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": ["master__goodies", "master__goodies__tshirts"],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}]
                },
                "created": "2017-04-18T16:12:55+02:00",
                "associations": {}
            }
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": ["master__goodies"],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}]
                },
                "created": "2017-04-18T16:12:55+02:00",
                "associations": {}
            }
        }'));

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertSame('master__goodies', $product->getMainTaxon()->getCode());
        $this->assertArraysAreEqual(['master__goodies'], $product->getTaxons()->map(function (TaxonInterface $taxon) {
            return $taxon->getCode();
        })->toArray());
    }

    /**
     * @test
     */
    public function it_adds_a_new_product_with_attributes()
    {
        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_attribute_updated",
            "payload": {
                "code": "main_color",
                "type": "pim_catalog_simpleselect",
                "labels": {"en_US": "Main color"}
            }
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_attribute_updated",
            "payload": {
                "code": "tshirt_style",
                "type": "pim_catalog_simpleselect",
                "labels": {"en_US": "T-Shirt style"}
            }
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_attribute_option_updated",
            "payload": {
                "code": "black",
                "attribute": "main_color",
                "labels": {"de_DE": "Schwarz", "en_US": "Black"}
            }
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_attribute_option_updated",
            "payload": {
                "code": "crewneck",
                "attribute": "tshirt_style",
                "labels": {"de_DE": "Rundhalsausschnitt", "en_US": "Crewneck"}
            }
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_attribute_option_updated",
            "payload": {
                "code": "short_sleeve",
                "attribute": "tshirt_style",
                "labels": {"de_DE": "Kurzarm", "en_US": "Short sleeve"}
            }
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "additional_info": [{"locale": null, "scope": null, "data": null}],
                    "main_color": [{"locale": null, "scope": null, "data": "black"}],
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
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
        Assert::assertNull($product->getAttributeByCodeAndLocale('additional_info', 'en_US'));
        Assert::assertNull($product->getAttributeByCodeAndLocale('additional_info', 'de_DE'));
    }

    /**
     * @test
     */
    public function it_updates_an_existing_product_with_attributes()
    {
        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_attribute_updated",
            "payload": {
                "code": "main_color",
                "type": "pim_catalog_simpleselect",
                "labels": {"en_US": "Main color"}
            }
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_attribute_updated",
            "payload": {
                "code": "tshirt_style",
                "type": "pim_catalog_simpleselect",
                "labels": {"en_US": "T-Shirt style"}
            }
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_attribute_updated",
            "payload": {
                "code": "additional_info",
                "type": "pim_catalog_text",
                "labels": {"en_US": "Additional information"}
            }
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_attribute_updated",
            "payload": {
                "code": "subtitle",
                "type": "pim_catalog_text",
                "labels": {"en_US": "Subtitle"}
            }
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "additional_info": [{"locale": null, "scope": null, "data": null}],
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
                    "additional_info": [{"locale": null, "scope": null, "data": null}],
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

    /**
     * @test
     */
    public function it_adds_a_new_product_with_associations()
    {
        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_association_type_updated",
            "payload": {
                "code": "SUBSTITUTION",
                "labels": {"en_US": "Substitution"}
            }
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_association_type_updated",
            "payload": {
                "code": "CROSS_SELL",
                "labels": {"en_US": "Cross sell"}
            }
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_WPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt white and purple with short sleeve"}]
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
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}]
                },
                "created": "2017-04-18T16:12:55+02:00",
                "associations": {
                    "SUBSTITUTION": {"groups": [], "products": ["AKNTS_WPXS", "AKNTS_PBXS", "AKNTS_PWXS"]},
                    "CROSS_SELL": {"groups": [], "products": ["AKNTS_WPXS"]}
                }
            }
        }'));

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);
        Assert::assertNotNull($product);

        $substitutionAssociation = $this->getProductAssociation($product, 'SUBSTITUTION');
        Assert::assertNotNull($substitutionAssociation);
        $this->assertArraysAreEqual(['AKNTS_WPXS'], $substitutionAssociation->getAssociatedProducts()->map(function (ProductInterface $product) {
            return $product->getCode();
        })->toArray());

        $crossSellAssociation = $this->getProductAssociation($product, 'CROSS_SELL');
        Assert::assertNotNull($crossSellAssociation);
        $this->assertArraysAreEqual(['AKNTS_WPXS'], $crossSellAssociation->getAssociatedProducts()->map(function (ProductInterface $product) {
            return $product->getCode();
        })->toArray());
    }

    /**
     * @test
     */
    public function it_updates_an_existing_product_with_associations()
    {
        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_association_type_updated",
            "payload": {
                "code": "SUBSTITUTION",
                "labels": {"en_US": "Substitution"}
            }
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_association_type_updated",
            "payload": {
                "code": "CROSS_SELL",
                "labels": {"en_US": "Cross sell"}
            }
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_WPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt white and purple with short sleeve"}]
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
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}]
                },
                "created": "2017-04-18T16:12:55+02:00",
                "associations": {
                    "SUBSTITUTION": {"groups": [], "products": ["AKNTS_WPXS", "AKNTS_PBXS", "AKNTS_PWXS"]},
                    "CROSS_SELL": {"groups": [], "products": ["AKNTS_WPXS"]}
                }
            }
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}]
                },
                "created": "2017-04-18T16:12:55+02:00",
                "associations": {
                    "SUBSTITUTION": {"groups": [], "products": []}
                }
            }
        }'));

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);
        Assert::assertNotNull($product);

        $substitutionAssociation = $this->getProductAssociation($product, 'SUBSTITUTION');
        Assert::assertNotNull($substitutionAssociation);
        $this->assertArraysAreEqual([], $substitutionAssociation->getAssociatedProducts()->map(function (ProductInterface $product) {
            return $product->getCode();
        })->toArray());

        $crossSellAssociation = $this->getProductAssociation($product, 'CROSS_SELL');
        Assert::assertNull($crossSellAssociation);
    }

    /**
     * @test
     */
    public function it_adds_a_new_product_with_channels_and_pricing()
    {
        $this->consumer->execute(new AMQPMessage('{
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
                "created": "2017-04-18T16:12:55+02:00",
                "associations": {}
            }
        }'));

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
    }

    /**
     * @test
     */
    public function it_updates_an_existing_product_with_channels_and_pricing()
    {
        $this->consumer->execute(new AMQPMessage('{
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
                    "price": [{"locale": null, "scope": null, "data": [
                        {"amount": 9, "currency": "GBP"}, 
                        {"amount": 14, "currency": "USD"}
                    ]}]
                },
                "created": "2017-04-18T16:12:55+02:00",
                "associations": {}
            }
        }'));

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

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $this->entityManager->clear();
        $this->entityManager = null;
    }

    /**
     * @param array $expectedArray
     * @param array $actualArray
     */
    private function assertArraysAreEqual(array $expectedArray, array $actualArray)
    {
        Assert::assertSame(count($expectedArray), count($actualArray));

        foreach ($expectedArray as $expectedElement) {
            Assert::assertTrue(in_array($expectedElement, $actualArray, true));
        }
    }

    /**
     * @param ProductInterface $product
     * @param string $code
     *
     * @return ProductAssociationInterface|null
     */
    private function getProductAssociation(ProductInterface $product, $code)
    {
        foreach ($product->getAssociations() as $association) {
            if ($association->getType()->getCode() === $code) {
                return $association;
            }
        }

        return null;
    }

    /**
     * @param string $code
     *
     * @return ChannelInterface|null
     */
    private function getChannelByCode($code)
    {
        return static::$kernel->getContainer()->get('sylius.repository.channel')->findOneBy(['code' => $code]);
    }
}
