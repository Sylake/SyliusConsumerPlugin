<?php

namespace Tests\Sylake\SyliusConsumerPlugin\Functional;

use PHPUnit\Framework\Assert;
use Sylake\SyliusConsumerPlugin\Entity\AkeneoFamily;
use Sylius\Component\Resource\Repository\RepositoryInterface;

/**
 * @author Kamil Kokot <kamil@kokot.me>
 */
final class FamilySynchronizationTest extends SynchronizationTestCase
{
    /**
     * @var RepositoryInterface
     */
    private $akeneoFamilyRepository;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->akeneoFamilyRepository = static::$kernel->getContainer()->get('sylake_sylius_consumer.repository.akeneo_family');
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

        /** @var AkeneoFamily|null $akeneoFamily */
        $akeneoFamily = $this->akeneoFamilyRepository->findOneBy(['code' => 'FAMILY']);

        Assert::assertNotNull($akeneoFamily);
        Assert::assertSame('FAMILY', $akeneoFamily->getCode());
        Assert::assertSame(['de_DE' => 'Familie', 'en_GB' => 'Family'], $akeneoFamily->getLabels());

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

        /** @var AkeneoFamily|null $akeneoFamily */
        $akeneoFamily = $this->akeneoFamilyRepository->findOneBy(['code' => 'FAMILY']);

        Assert::assertNotNull($akeneoFamily);
        Assert::assertNotNull($akeneoFamily);
        Assert::assertSame('FAMILY', $akeneoFamily->getCode());
        Assert::assertSame(['de_DE' => 'Familie (updated)', 'en_GB' => 'Family (updated)'], $akeneoFamily->getLabels());
    }
}
