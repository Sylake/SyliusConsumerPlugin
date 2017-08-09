<?php

namespace Tests\Sylake\SyliusConsumerPlugin\Functional;

use PHPUnit\Framework\Assert;
use Sylake\SyliusConsumerPlugin\Entity\AkeneoGroup;
use Sylius\Component\Resource\Repository\RepositoryInterface;

/**
 * @author Kamil Kokot <kamil@kokot.me>
 */
final class GroupSynchronizationTest extends SynchronizationTestCase
{
    /**
     * @var RepositoryInterface
     */
    private $akeneoGroupRepository;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->akeneoGroupRepository = static::$kernel->getContainer()->get('sylake_sylius_consumer.repository.akeneo_group');
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

        /** @var AkeneoGroup|null $akeneoGroup */
        $akeneoGroup = $this->akeneoGroupRepository->findOneBy(['code' => 'GROUP']);

        Assert::assertNotNull($akeneoGroup);
        Assert::assertSame('GROUP', $akeneoGroup->getCode());
        Assert::assertSame(['de_DE' => 'Gruppe', 'en_GB' => 'Group'], $akeneoGroup->getLabels());

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

        /** @var AkeneoGroup|null $akeneoGroup */
        $akeneoGroup = $this->akeneoGroupRepository->findOneBy(['code' => 'GROUP']);

        Assert::assertNotNull($akeneoGroup);
        Assert::assertNotNull($akeneoGroup);
        Assert::assertSame('GROUP', $akeneoGroup->getCode());
        Assert::assertSame(['de_DE' => 'Gruppe (updated)', 'en_GB' => 'Group (updated)'], $akeneoGroup->getLabels());
    }
}
