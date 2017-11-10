<?php

declare(strict_types=1);

namespace Tests\Sylake\SyliusConsumerPlugin\Functional;

use PHPUnit\Framework\Assert;
use Sylake\SyliusConsumerPlugin\Entity\AkeneoAttributeOption;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class GroupSynchronizationTest extends SynchronizationTestCase
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
    public function it_adds_and_updates_an_akeneo_group_from_akeneo_message()
    {
        $this->consume('{
            "type": "akeneo_group_updated",
            "payload": {
                "code": "GROUP",
                "labels": {
                    "de_DE": "Gruppe",
                    "en_GB": "Group"
                }
            }
        }');

        /** @var AkeneoAttributeOption|null $akeneoAttributeOption */
        $akeneoAttributeOption = $this->akeneoAttributeOptionRepository->findOneBy([
            'code' => 'GROUP',
            'attribute' => 'AKENEO_GROUPS_NAMES',
        ]);

        Assert::assertNotNull($akeneoAttributeOption);
        Assert::assertSame('GROUP', $akeneoAttributeOption->getCode());
        Assert::assertSame('AKENEO_GROUPS_NAMES', $akeneoAttributeOption->getAttribute());
        Assert::assertSame(['de_DE' => 'Gruppe', 'en_GB' => 'Group'], $akeneoAttributeOption->getLabels());

        Assert::assertNotNull($this->attributeRepository->findOneBy(['code' => 'AKENEO_GROUPS_CODES']));
        Assert::assertNotNull($this->attributeRepository->findOneBy(['code' => 'AKENEO_GROUPS_NAMES']));

        $this->consume('{
            "type": "akeneo_group_updated",
            "payload": {
                "code": "GROUP",
                "labels": {
                    "de_DE": "Gruppe (updated)",
                    "en_GB": "Group (updated)"
                }
            }
        }');

        /** @var AkeneoAttributeOption|null $akeneoAttributeOption */
        $akeneoAttributeOption = $this->akeneoAttributeOptionRepository->findOneBy([
            'code' => 'GROUP',
            'attribute' => 'AKENEO_GROUPS_NAMES',
        ]);

        Assert::assertNotNull($akeneoAttributeOption);
        Assert::assertSame('GROUP', $akeneoAttributeOption->getCode());
        Assert::assertSame('AKENEO_GROUPS_NAMES', $akeneoAttributeOption->getAttribute());
        Assert::assertSame(['de_DE' => 'Gruppe (updated)', 'en_GB' => 'Group (updated)'], $akeneoAttributeOption->getLabels());

        Assert::assertNotNull($this->attributeRepository->findOneBy(['code' => 'AKENEO_GROUPS_CODES']));
        Assert::assertNotNull($this->attributeRepository->findOneBy(['code' => 'AKENEO_GROUPS_NAMES']));
    }
}
