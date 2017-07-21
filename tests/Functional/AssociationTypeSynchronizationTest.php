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
final class AssociationTypeSynchronizationTest extends SynchronizationTestCase
{
    /**
     * @var ProductAssociationTypeRepositoryInterface
     */
    private $associationTypeRepository;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->associationTypeRepository = static::$kernel->getContainer()->get('sylius.repository.product_association_type');
    }

    /**
     * @test
     */
    public function it_adds_and_updates_an_association_type_from_akeneo_message()
    {
        $this->consume('{
            "type": "akeneo_association_type_updated",
            "payload": {
                "code": "SUBSTITUTION",
                "labels": {
                    "de_DE": "Ersatz",
                    "en_US": "Substitution",
                    "fr_FR": "Remplacement"
                }
            }
        }');

        /** @var ProductAssociationTypeInterface|null $associationType */
        $associationType = $this->associationTypeRepository->findOneBy(['code' => 'SUBSTITUTION']);

        Assert::assertNotNull($associationType);
        Assert::assertSame('Ersatz', $associationType->getTranslation('de_DE')->getName());
        Assert::assertSame('Substitution', $associationType->getTranslation('en_US')->getName());
        Assert::assertSame('Remplacement', $associationType->getTranslation('fr_FR')->getName());

        $this->consume('{
            "type": "akeneo_association_type_updated",
            "payload": {
                "code": "SUBSTITUTION",
                "labels": {
                    "de_DE": "Ersatz (updated)",
                    "en_US": "Substitution (updated)",
                    "fr_FR": "Remplacement (updated)"
                }
            }
        }');

        /** @var ProductAssociationTypeInterface|null $associationType */
        $associationType = $this->associationTypeRepository->findOneBy(['code' => 'SUBSTITUTION']);

        Assert::assertNotNull($associationType);
        Assert::assertSame('Ersatz (updated)', $associationType->getTranslation('de_DE')->getName());
        Assert::assertSame('Substitution (updated)', $associationType->getTranslation('en_US')->getName());
        Assert::assertSame('Remplacement (updated)', $associationType->getTranslation('fr_FR')->getName());
    }
}
