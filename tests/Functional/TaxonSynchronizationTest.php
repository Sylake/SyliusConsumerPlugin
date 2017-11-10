<?php

declare(strict_types=1);

namespace Tests\Sylake\SyliusConsumerPlugin\Functional;

use PHPUnit\Framework\Assert;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;

/**
 * @author Kamil Kokot <kamil@kokot.me>
 */
final class TaxonSynchronizationTest extends SynchronizationTestCase
{
    /**
     * @var TaxonRepositoryInterface
     */
    private $taxonRepository;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->taxonRepository = static::$kernel->getContainer()->get('sylius.repository.taxon');
    }

    /**
     * @test
     */
    public function it_adds_and_updates_a_taxon_from_akeneo_message()
    {
        $this->consume('{
            "type": "akeneo_category_updated",
            "payload": {
                "code": "master",
                "parent": null,
                "labels": {
                    "en_US": "Master catalog",
                    "de_DE": "Hauptkatalog",
                    "fr_FR": "Catalogue principal"
                }
            }
        }');

        /** @var TaxonInterface|null $taxon */
        $taxon = $this->taxonRepository->findOneBy(['code' => 'master']);

        Assert::assertNotNull($taxon);
        Assert::assertNull($taxon->getParent());
        Assert::assertSame('Master catalog', $taxon->getTranslation('en_US')->getName());
        Assert::assertSame('master-catalog', $taxon->getTranslation('en_US')->getSlug());
        Assert::assertSame('Hauptkatalog', $taxon->getTranslation('de_DE')->getName());
        Assert::assertSame('hauptkatalog', $taxon->getTranslation('de_DE')->getSlug());
        Assert::assertSame('Catalogue principal', $taxon->getTranslation('fr_FR')->getName());
        Assert::assertSame('catalogue-principal', $taxon->getTranslation('fr_FR')->getSlug());

        $this->consume('{
            "type": "akeneo_category_updated",
            "payload": {
                "code": "audio_video",
                "parent": "master",
                "labels": {
                    "en_US": "Audio and Video",
                    "de_DE": "Audio und Video",
                    "fr_FR": "Audio et Video"
                }
            }
        }');

        /** @var TaxonInterface|null $taxon */
        $taxon = $this->taxonRepository->findOneBy(['code' => 'audio_video']);

        Assert::assertNotNull($taxon);
        Assert::assertSame('master', $taxon->getParent()->getCode());
        Assert::assertSame('Audio and Video', $taxon->getTranslation('en_US')->getName());
        Assert::assertSame('master-catalog/audio-and-video', $taxon->getTranslation('en_US')->getSlug());
        Assert::assertSame('Audio und Video', $taxon->getTranslation('de_DE')->getName());
        Assert::assertSame('hauptkatalog/audio-und-video', $taxon->getTranslation('de_DE')->getSlug());
        Assert::assertSame('Audio et Video', $taxon->getTranslation('fr_FR')->getName());
        Assert::assertSame('catalogue-principal/audio-et-video', $taxon->getTranslation('fr_FR')->getSlug());

        $this->consume('{
            "type": "akeneo_category_updated",
            "payload": {
                "code": "audio_video",
                "parent": null,
                "labels": {
                    "en_US": "Audio and Video (updated)",
                    "de_DE": "Audio und Video (updated)",
                    "fr_FR": "Audio et Video (updated)"
                }
            }
        }');

        /** @var TaxonInterface|null $taxon */
        $taxon = $this->taxonRepository->findOneBy(['code' => 'audio_video']);

        Assert::assertNotNull($taxon);
        Assert::assertNull($taxon->getParent());
        Assert::assertSame('Audio and Video (updated)', $taxon->getTranslation('en_US')->getName());
        Assert::assertSame('audio-and-video-updated', $taxon->getTranslation('en_US')->getSlug());
        Assert::assertSame('Audio und Video (updated)', $taxon->getTranslation('de_DE')->getName());
        Assert::assertSame('audio-und-video-updated', $taxon->getTranslation('de_DE')->getSlug());
        Assert::assertSame('Audio et Video (updated)', $taxon->getTranslation('fr_FR')->getName());
        Assert::assertSame('audio-et-video-updated', $taxon->getTranslation('fr_FR')->getSlug());
    }

    /**
     * @test
     */
    public function it_uses_taxon_code_as_its_name_if_it_does_not_have_name_in_given_locale()
    {
        $this->consume('{
            "type": "akeneo_category_updated",
            "payload": {
                "code": "master",
                "parent": null,
                "labels": {
                    "en_US": null,
                    "de_DE": null
                }
            }
        }');

        /** @var TaxonInterface|null $taxon */
        $taxon = $this->taxonRepository->findOneBy(['code' => 'master']);

        Assert::assertNotNull($taxon);
        Assert::assertSame('master', $taxon->getTranslation('en_US')->getName());
        Assert::assertSame('master', $taxon->getTranslation('en_US')->getSlug());
        Assert::assertSame('master', $taxon->getTranslation('de_DE')->getName());
        Assert::assertSame('master', $taxon->getTranslation('de_DE')->getSlug());
    }
}
