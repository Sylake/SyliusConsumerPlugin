<?php

declare(strict_types=1);

namespace Tests\Sylake\SyliusConsumerPlugin\Functional;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\Assert;
use Sylius\Bundle\FixturesBundle\Fixture\FixtureInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author Kamil Kokot <kamil@kokot.me>
 */
abstract class ProductSynchronizationTestCase extends KernelTestCase
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ConsumerInterface
     */
    protected $consumer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
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
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->entityManager->clear();
        $this->entityManager = null;
    }

    /**
     * @param array $expectedArray
     * @param array $actualArray
     *
     * @throws \InvalidArgumentException
     */
    protected function assertArraysAreEqual(array $expectedArray, array $actualArray): void
    {
        Assert::assertSame(count($expectedArray), count($actualArray));

        foreach ($expectedArray as $expectedElement) {
            Assert::assertTrue(in_array($expectedElement, $actualArray, true));
        }
    }

    protected function consumeAssociationType(string $code, array $labels): void
    {
        $this->consumer->execute(new AMQPMessage(sprintf('{
            "type": "akeneo_association_type_updated",
            "payload": {
                "code": %s,
                "labels": %s
            }
        }', json_encode($code), json_encode($labels))));
    }

    protected function consumeAttribute(string $code, string $type, array $labels): void
    {
        $this->consumer->execute(new AMQPMessage(sprintf('{
            "type": "akeneo_attribute_updated",
            "payload": {
                "code": %s,
                "type": %s,
                "labels": %s
            }
        }', json_encode($code), json_encode($type), json_encode($labels))));
    }

    protected function consumeAttributeOption(string $attributeCode, string $code, array $labels): void
    {
        $this->consumer->execute(new AMQPMessage(sprintf('{
            "type": "akeneo_attribute_option_updated",
            "payload": {
                "code": %s,
                "attribute": %s,
                "labels": %s
            }
        }', json_encode($code), json_encode($attributeCode), json_encode($labels))));
    }

    protected function consumeTaxon(string $code, ?string $parentCode, array $labels): void
    {
        $this->consumer->execute(new AMQPMessage(sprintf('{
            "type": "akeneo_category_updated",
            "payload": {
                "code": %s,
                "parent": %s,
                "labels": %s
            }
        }', json_encode($code), json_encode($parentCode), json_encode($labels))));
    }
}
