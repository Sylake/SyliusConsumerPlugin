<?php

namespace Sylake\SyliusConsumerPlugin\Projector;

use Sylake\SyliusConsumerPlugin\Event\TaxonCreated;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Taxonomy\Factory\TaxonFactoryInterface;
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
     * @param TaxonFactoryInterface $factory
     * @param TaxonRepositoryInterface $repository
     */
    public function __construct(TaxonFactoryInterface $factory, TaxonRepositoryInterface $repository)
    {
        $this->factory = $factory;
        $this->repository = $repository;
    }

    /**
     * @param TaxonCreated $event
     */
    public function handleTaxonCreated(TaxonCreated $event)
    {
        /** @var TaxonInterface $taxon */
        if (null !== $event->parent()) {
            /** @var TaxonInterface $parent */
            $parent = $this->repository->findOneBy(['code' => $event->parent()]);
            $taxon = $this->factory->createForParent($parent);
        }

        if (null === $event->parent()) {
            $taxon = $this->factory->createNew();
        }

        $taxon->setCode($event->code());
        foreach ($event->names() as $locale => $name) {
            $taxon->setCurrentLocale($locale);
            $taxon->setName($name);
        }

        $this->repository->add($taxon);
    }
}
