<?php

namespace Tests\Sylake\SyliusConsumerPlugin\Functional;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\Assert;
use Sylius\Component\Product\Model\ProductAssociationTypeInterface;
use Sylius\Component\Product\Model\ProductAttributeInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author Kamil Kokot <kamil@kokot.me>
 */
final class AttributeSynchronizationTest extends KernelTestCase
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var RepositoryInterface
     */
    private $attributeRepository;

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
        $this->attributeRepository = static::$kernel->getContainer()->get('sylius.repository.product_attribute');
        $this->consumer = static::$kernel->getContainer()->get('rabbitmq_simplebus.consumer');

        (new ORMPurger($this->entityManager))->purge();
    }

    /**
     * @test
     */
    public function it_adds_new_attribute_from_akeneo_message()
    {
        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_attribute_updated",
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
                "labels": {
                    "de_DE": "Hauptfarbe",
                    "en_US": "Main color",
                    "fr_FR": "Couleur principale"
                }
            },
            "recordedOn": "2017-05-22 14:16:59"
        }'));

        /** @var ProductAttributeInterface|null $attribute */
        $attribute = $this->attributeRepository->findOneBy(['code' => 'main_color']);

        Assert::assertNotNull($attribute);
        Assert::assertSame('Hauptfarbe', $attribute->getTranslation('de_DE')->getName());
        Assert::assertSame('Main color', $attribute->getTranslation('en_US')->getName());
        Assert::assertSame('Couleur principale', $attribute->getTranslation('fr_FR')->getName());
    }

    /**
     * @test
     */
    public function it_updates_existing_attribute_from_akeneo_message()
    {
        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_attribute_updated",
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
                "labels": {
                    "de_DE": "Hauptfarbe",
                    "en_US": "Main color",
                    "fr_FR": "Couleur principale"
                }
            },
            "recordedOn": "2017-05-22 14:16:59"
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_attribute_updated",
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
                "labels": {
                    "de_DE": "Hauptfarbe (updated)",
                    "en_US": "Main color (updated)",
                    "fr_FR": "Couleur principale (updated)"
                }
            },
            "recordedOn": "2017-05-22 14:16:59"
        }'));

        /** @var ProductAssociationTypeInterface|null $attribute */
        $attribute = $this->attributeRepository->findOneBy(['code' => 'main_color']);

        Assert::assertNotNull($attribute);
        Assert::assertSame('Hauptfarbe (updated)', $attribute->getTranslation('de_DE')->getName());
        Assert::assertSame('Main color (updated)', $attribute->getTranslation('en_US')->getName());
        Assert::assertSame('Couleur principale (updated)', $attribute->getTranslation('fr_FR')->getName());
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $this->entityManager->clear();
        $this->entityManager = null;
    }
}
