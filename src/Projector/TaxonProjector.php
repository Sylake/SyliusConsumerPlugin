<?php

namespace Sylake\SyliusConsumerPlugin\Projector;

use Psr\Log\LoggerInterface;
use Sylake\SyliusConsumerPlugin\Event\TaxonCreated;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Taxonomy\Generator\TaxonSlugGeneratorInterface;

final class TaxonProjector
{
    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var RepositoryInterface
     */
    private $repository;

    /**
     * @var TaxonSlugGeneratorInterface
     */
    private $slugGenerator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param FactoryInterface $factory
     * @param RepositoryInterface $repository
     * @param TaxonSlugGeneratorInterface $slugGenerator
     * @param LoggerInterface $logger
     */
    public function __construct(
        FactoryInterface $factory,
        RepositoryInterface $repository,
        TaxonSlugGeneratorInterface $slugGenerator,
        LoggerInterface $logger
    ) {
        $this->factory = $factory;
        $this->repository = $repository;
        $this->slugGenerator = $slugGenerator;
        $this->logger = $logger;
    }

    /**
     * @param TaxonCreated $event
     */
    public function __invoke(TaxonCreated $event)
    {
        $this->logger->debug(sprintf('Projecting taxon with code "%s".', $event->code()));

        /** @var TaxonInterface|null $taxon */
        $taxon = $this->repository->findOneBy(['code' => $event->code()]);
        if (null === $taxon) {
            $taxon = $this->factory->createNew();
            $taxon->setCode($event->code());
        }

        $parent = null;
        if (null !== $event->parent()) {
            /** @var TaxonInterface|null $parent */
            $parent = $this->repository->findOneBy(['code' => $event->parent()]);
        }

        $taxon->setParent($parent);

        foreach ($event->names() as $locale => $name) {
            if (null !== $parent) {
                $parent->setFallbackLocale($locale);
                $parent->setCurrentLocale($locale);
            }

            $taxon->setFallbackLocale($locale);
            $taxon->setCurrentLocale($locale);

            $taxon->setName($name);
            $taxon->setSlug($this->slugGenerator->generate($name, null !== $parent ? $parent->getId() : null));
        }

        $this->repository->add($taxon);
    }
}
