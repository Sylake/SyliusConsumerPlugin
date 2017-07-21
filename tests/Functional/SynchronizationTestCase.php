<?php

declare(strict_types=1);

namespace Tests\Sylake\SyliusConsumerPlugin\Functional;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Sylius\Bundle\FixturesBundle\Fixture\FixtureInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author Kamil Kokot <kamil@kokot.me>
 */
abstract class SynchronizationTestCase extends KernelTestCase
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var ConsumerInterface
     */
    private $consumer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::$kernel->getContainer()->get('doctrine')->getManager();
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

    protected function consume(string $message): void
    {
        $this->consumer->execute(new AMQPMessage($message));

        $this->entityManager->clear();
    }
}
