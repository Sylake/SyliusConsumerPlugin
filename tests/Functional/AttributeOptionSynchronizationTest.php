<?php

declare(strict_types=1);

namespace Tests\Sylake\SyliusConsumerPlugin\Functional;

use PHPUnit\Framework\Assert;
use Sylake\SyliusConsumerPlugin\Entity\AkeneoAttributeOption;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class AttributeOptionSynchronizationTest extends SynchronizationTestCase
{
    /**
     * @var RepositoryInterface
     */
    private $akeneoAttributeOptionRepository;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->akeneoAttributeOptionRepository = static::$kernel->getContainer()->get('sylake_sylius_consumer.repository.akeneo_attribute_option');
    }

    /**
     * @test
     */
    public function it_adds_and_updates_an_akeneo_attribute_option_from_akeneo_message()
    {
        $this->consume('{
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
        }');

        /** @var AkeneoAttributeOption|null $akeneoAttributeOption */
        $akeneoAttributeOption = $this->akeneoAttributeOptionRepository->findOneBy([
            'code' => 'high',
            'attribute' => 'product_positioning',
        ]);

        Assert::assertNotNull($akeneoAttributeOption);
        Assert::assertSame('high', $akeneoAttributeOption->getCode());
        Assert::assertSame('product_positioning', $akeneoAttributeOption->getAttribute());
        Assert::assertSame(['de_DE' => 'Hoch', 'en_GB' => 'High'], $akeneoAttributeOption->getLabels());

        $this->consume('{
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
        }');

        /** @var AkeneoAttributeOption|null $akeneoAttributeOption */
        $akeneoAttributeOption = $this->akeneoAttributeOptionRepository->findOneBy([
            'code' => 'high',
            'attribute' => 'product_positioning',
        ]);

        Assert::assertNotNull($akeneoAttributeOption);
        Assert::assertSame('high', $akeneoAttributeOption->getCode());
        Assert::assertSame('product_positioning', $akeneoAttributeOption->getAttribute());
        Assert::assertSame(['de_DE' => 'Hoch (updated)', 'en_GB' => 'High (updated)'], $akeneoAttributeOption->getLabels());
    }
}
