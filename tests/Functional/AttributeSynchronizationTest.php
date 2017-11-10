<?php

declare(strict_types=1);

namespace Tests\Sylake\SyliusConsumerPlugin\Functional;

use PHPUnit\Framework\Assert;
use Sylius\Component\Product\Model\ProductAssociationTypeInterface;
use Sylius\Component\Product\Model\ProductAttributeInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

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

    /**
     * @test
     */
    public function it_uses_attribute_code_as_its_name_if_it_does_not_have_name_in_given_locale()
    {
        $this->consume('{
            "type": "akeneo_attribute_updated",
            "payload": {
                "code": "main_color",
                "type": "pim_catalog_simpleselect",
                "labels": {
                    "de_DE": null,
                    "en_US": null
                }
            }
        }');

        /** @var ProductAttributeInterface|null $attribute */
        $attribute = $this->attributeRepository->findOneBy(['code' => 'main_color']);

        Assert::assertNotNull($attribute);
        Assert::assertSame('main_color', $attribute->getTranslation('de_DE')->getName());
        Assert::assertSame('main_color', $attribute->getTranslation('en_US')->getName());
    }
}
