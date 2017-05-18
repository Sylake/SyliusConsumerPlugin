<?php

namespace Sylake\SyliusConsumerPlugin\Projector;

use Sylake\SyliusConsumerPlugin\Event\TaxonCreated;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Taxonomy\Factory\TaxonFactoryInterface;
use Sylius\Component\Taxonomy\Generator\TaxonSlugGeneratorInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;

final class TaxonProjector
{
    /**
     * @var TaxonFactoryInterface
     */
    private $factory;

    /**
     * @var TaxonRepositoryInterface
     */
    private $repository;

    /**
     * @var TaxonSlugGeneratorInterface
     */
    private $slugGenerator;

    /**
     * @param TaxonFactoryInterface $factory
     * @param TaxonRepositoryInterface $repository
     * @param TaxonSlugGeneratorInterface $slugGenerator
     */
    public function __construct(
        TaxonFactoryInterface $factory,
        TaxonRepositoryInterface $repository,
        TaxonSlugGeneratorInterface $slugGenerator
    ) {
        $this->factory = $factory;
        $this->repository = $repository;
        $this->slugGenerator = $slugGenerator;
    }

    /**
     * @param TaxonCreated $event
     */
    public function handleTaxonCreated(TaxonCreated $event)
    {
        /** @var TaxonInterface|null $taxon */
        $taxon = $this->repository->findOneBy(['code' => $event->code()]);
        if (null === $taxon) {
            $taxon = $this->factory->createNew();
            $taxon->setCode($event->code());
        }

        $parentId = null;
        if (null !== $event->parent()) {
            /** @var TaxonInterface $parent */
            $parent = $this->repository->findOneBy(['code' => $event->parent()]);
            $parentId = $parent->getId();

            $taxon->setParent($parent);
        }

        foreach ($event->names() as $locale => $name) {
            $taxon->setFallbackLocale($locale);
            $taxon->setCurrentLocale($locale);

            $taxon->setName($name);
            $taxon->setSlug($this->slugGenerator->generate($name, $parentId));
        }

        $this->repository->add($taxon);
    }
}
