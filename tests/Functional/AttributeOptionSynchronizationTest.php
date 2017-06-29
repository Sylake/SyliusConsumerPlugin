<?php

namespace Tests\Sylake\SyliusConsumerPlugin\Functional;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\Assert;
use Sylake\SyliusConsumerPlugin\Entity\AkeneoAttributeOption;
use Sylius\Component\Product\Model\ProductAssociationTypeInterface;
use Sylius\Component\Product\Model\ProductAttributeInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author Kamil Kokot <kamil@kokot.me>
 */
final class AttributeOptionSynchronizationTest extends KernelTestCase
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var RepositoryInterface
     */
    private $akeneoAttributeOptionRepository;

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
        $this->akeneoAttributeOptionRepository = static::$kernel->getContainer()->get('sylake_sylius_consumer.repository.akeneo_attribute_option');
        $this->consumer = static::$kernel->getContainer()->get('rabbitmq_simplebus.consumer');

        (new ORMPurger($this->entityManager))->purge();
    }

    /**
     * @test
     */
    public function it_adds_new_akeneo_attribute_option_from_akeneo_message()
    {
        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_attribute_option_updated",
            "payload": {
                "code": "high",
                "attribute": "product_positioning",
                "sort_order": 0,
                "labels": {
                    "de_DE": "Hoch",
                    "en_GB": "High"
                }
            }
        }'));

        /** @var AkeneoAttributeOption|null $akeneoAttributeOption */
        $akeneoAttributeOption = $this->akeneoAttributeOptionRepository->findOneBy([
            'code' => 'high',
            'attribute' => 'product_positioning',
        ]);

        Assert::assertNotNull($akeneoAttributeOption);
        Assert::assertSame('high', $akeneoAttributeOption->getCode());
        Assert::assertSame('product_positioning', $akeneoAttributeOption->getAttribute());
        Assert::assertSame([
            'de_DE' => 'Hoch',
            'en_GB' => 'High',
        ], $akeneoAttributeOption->getLabels());
    }

    /**
     * @test
     */
    public function it_updates_existing_akeneo_attribute_option_from_akeneo_message()
    {
        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_attribute_option_updated",
            "payload": {
                "code": "high",
                "attribute": "product_positioning",
                "sort_order": 0,
                "labels": {
                    "de_DE": "Hoch",
                    "en_GB": "High"
                }
            }
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_attribute_option_updated",
            "payload": {
                "code": "high",
                "attribute": "product_positioning",
                "sort_order": 0,
                "labels": {
                    "de_DE": "Hoch (updated)",
                    "en_GB": "High (updated)"
                }
            }
        }'));

        /** @var AkeneoAttributeOption|null $akeneoAttributeOption */
        $akeneoAttributeOption = $this->akeneoAttributeOptionRepository->findOneBy([
            'code' => 'high',
            'attribute' => 'product_positioning',
        ]);

        Assert::assertNotNull($akeneoAttributeOption);
        Assert::assertSame('high', $akeneoAttributeOption->getCode());
        Assert::assertSame('product_positioning', $akeneoAttributeOption->getAttribute());
        Assert::assertSame([
            'de_DE' => 'Hoch (updated)',
            'en_GB' => 'High (updated)',
        ], $akeneoAttributeOption->getLabels());
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
