<?php

declare(strict_types=1);

namespace Tests\Sylake\SyliusConsumerPlugin\Functional;

use PHPUnit\Framework\Assert;
use Sylake\SyliusConsumerPlugin\Entity\AkeneoAttributeOption;
use Sylius\Component\Resource\Repository\RepositoryInterface;

/**
 * @author Kamil Kokot <kamil@kokot.me>
 */
final class FamilySynchronizationTest extends SynchronizationTestCase
{
    /** @var RepositoryInterface */
    private $akeneoAttributeOptionRepository;

    /** @var RepositoryInterface */
    private $attributeRepository;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->akeneoAttributeOptionRepository = static::$kernel->getContainer()->get('sylake_sylius_consumer.repository.akeneo_attribute_option');
        $this->attributeRepository = static::$kernel->getContainer()->get('sylius.repository.product_attribute');
    }

    /**
     * @test
     */
    public function it_adds_and_updates_an_akeneo_family_from_akeneo_message()
    {
        $this->consume('{
            "type": "akeneo_family_updated",
            "payload": {
                "code": "FAMILY",
                "labels": {
                    "de_DE": "Familie",
                    "en_GB": "Family"
                }
            }
        }');

        /** @var AkeneoAttributeOption|null $akeneoAttributeOption */
        $akeneoAttributeOption = $this->akeneoAttributeOptionRepository->findOneBy([
            'code' => 'FAMILY',
            'attribute' => 'AKENEO_FAMILY_NAME',
        ]);

        Assert::assertNotNull($akeneoAttributeOption);
        Assert::assertSame('FAMILY', $akeneoAttributeOption->getCode());
        Assert::assertSame('AKENEO_FAMILY_NAME', $akeneoAttributeOption->getAttribute());
        Assert::assertSame(['de_DE' => 'Familie', 'en_GB' => 'Family'], $akeneoAttributeOption->getLabels());

        Assert::assertNotNull($this->attributeRepository->findOneBy(['code' => 'AKENEO_FAMILY_CODE']));
        Assert::assertNotNull($this->attributeRepository->findOneBy(['code' => 'AKENEO_FAMILY_NAME']));

        $this->consume('{
            "type": "akeneo_family_updated",
            "payload": {
                "code": "FAMILY",
                "labels": {
                    "de_DE": "Familie (updated)",
                    "en_GB": "Family (updated)"
                }
            }
        }');

        /** @var AkeneoAttributeOption|null $akeneoAttributeOption */
        $akeneoAttributeOption = $this->akeneoAttributeOptionRepository->findOneBy([
            'code' => 'FAMILY',
            'attribute' => 'AKENEO_FAMILY_NAME',
        ]);

        Assert::assertNotNull($akeneoAttributeOption);
        Assert::assertSame('FAMILY', $akeneoAttributeOption->getCode());
        Assert::assertSame('AKENEO_FAMILY_NAME', $akeneoAttributeOption->getAttribute());
        Assert::assertSame(['de_DE' => 'Familie (updated)', 'en_GB' => 'Family (updated)'], $akeneoAttributeOption->getLabels());

        Assert::assertNotNull($this->attributeRepository->findOneBy(['code' => 'AKENEO_FAMILY_CODE']));
        Assert::assertNotNull($this->attributeRepository->findOneBy(['code' => 'AKENEO_FAMILY_NAME']));
    }
}
