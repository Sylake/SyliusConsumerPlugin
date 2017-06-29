<?php

namespace Tests\Sylake\SyliusConsumerPlugin\Functional;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\Assert;
use Sylius\Component\Product\Model\ProductAssociationTypeInterface;
use Sylius\Component\Product\Repository\ProductAssociationTypeRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author Kamil Kokot <kamil@kokot.me>
 */
final class AssociationTypeSynchronizationTest extends KernelTestCase
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ProductAssociationTypeRepositoryInterface
     */
    private $associationTypeRepository;

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
        $this->associationTypeRepository = static::$kernel->getContainer()->get('sylius.repository.product_association_type');
        $this->consumer = static::$kernel->getContainer()->get('rabbitmq_simplebus.consumer');

        (new ORMPurger($this->entityManager))->purge();
    }

    /**
     * @test
     */
    public function it_adds_new_association_type_from_akeneo_message()
    {
        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_association_type_updated",
            "payload": {
                "code": "SUBSTITUTION",
                "labels": {
                    "de_DE": "Ersatz",
                    "en_US": "Substitution",
                    "fr_FR": "Remplacement"
                }
            },
            "recordedOn": "2017-05-22 12:51:29"
        }'));

        /** @var ProductAssociationTypeInterface|null $associationType */
        $associationType = $this->associationTypeRepository->findOneBy(['code' => 'SUBSTITUTION']);

        Assert::assertNotNull($associationType);
        Assert::assertSame('Ersatz', $associationType->getTranslation('de_DE')->getName());
        Assert::assertSame('Substitution', $associationType->getTranslation('en_US')->getName());
        Assert::assertSame('Remplacement', $associationType->getTranslation('fr_FR')->getName());
    }

    /**
     * @test
     */
    public function it_updates_existing_association_type_from_akeneo_message()
    {
        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_association_type_updated",
            "payload": {
                "code": "SUBSTITUTION",
                "labels": {
                    "de_DE": "Ersatz",
                    "en_US": "Substitution",
                    "fr_FR": "Remplacement"
                }
            },
            "recordedOn": "2017-05-22 12:51:29"
        }'));

        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_association_type_updated",
            "payload": {
                "code": "SUBSTITUTION",
                "labels": {
                    "de_DE": "Ersatz (updated)",
                    "en_US": "Substitution (updated)",
                    "fr_FR": "Remplacement (updated)"
                }
            },
            "recordedOn": "2017-05-22 12:51:29"
        }'));

        /** @var ProductAssociationTypeInterface|null $associationType */
        $associationType = $this->associationTypeRepository->findOneBy(['code' => 'SUBSTITUTION']);

        Assert::assertNotNull($associationType);
        Assert::assertSame('Ersatz (updated)', $associationType->getTranslation('de_DE')->getName());
        Assert::assertSame('Substitution (updated)', $associationType->getTranslation('en_US')->getName());
        Assert::assertSame('Remplacement (updated)', $associationType->getTranslation('fr_FR')->getName());
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
