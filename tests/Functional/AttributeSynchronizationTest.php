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
final class AttributeSynchronizationTest extends SynchronizationTestCase
{
    /**
     * @var RepositoryInterface
     */
    private $attributeRepository;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->attributeRepository = static::$kernel->getContainer()->get('sylius.repository.product_attribute');
    }

    /**
     * @test
     */
    public function it_adds_and_updates_an_attribute_from_akeneo_message()
    {
        $this->consume('{
            "type": "akeneo_attribute_updated",
            "payload": {
                "code": "main_color",
                "type": "pim_catalog_simpleselect",
                "labels": {
                    "de_DE": "Hauptfarbe",
                    "en_US": "Main color",
                    "fr_FR": "Couleur principale"
                }
            }
        }');

        /** @var ProductAttributeInterface|null $attribute */
        $attribute = $this->attributeRepository->findOneBy(['code' => 'main_color']);

        Assert::assertNotNull($attribute);
        Assert::assertSame('Hauptfarbe', $attribute->getTranslation('de_DE')->getName());
        Assert::assertSame('Main color', $attribute->getTranslation('en_US')->getName());
        Assert::assertSame('Couleur principale', $attribute->getTranslation('fr_FR')->getName());

        $this->consume('{
            "type": "akeneo_attribute_updated",
            "payload": {
                "code": "main_color",
                "type": "pim_catalog_simpleselect",
                "labels": {
                    "de_DE": "Hauptfarbe (updated)",
                    "en_US": "Main color (updated)",
                    "fr_FR": "Couleur principale (updated)"
                }
            }
        }');

        /** @var ProductAssociationTypeInterface|null $attribute */
        $attribute = $this->attributeRepository->findOneBy(['code' => 'main_color']);

        Assert::assertNotNull($attribute);
        Assert::assertSame('Hauptfarbe (updated)', $attribute->getTranslation('de_DE')->getName());
        Assert::assertSame('Main color (updated)', $attribute->getTranslation('en_US')->getName());
        Assert::assertSame('Couleur principale (updated)', $attribute->getTranslation('fr_FR')->getName());
    }
}
