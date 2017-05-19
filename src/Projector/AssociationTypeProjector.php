<?php

namespace Sylake\SyliusConsumerPlugin\Projector;

use Sylake\SyliusConsumerPlugin\Event\AssociationTypeCreated;
use Sylius\Component\Product\Model\ProductAssociationTypeInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class AssociationTypeProjector
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
     * @param FactoryInterface $factory
     * @param RepositoryInterface $repository
     */
    public function __construct(FactoryInterface $factory, RepositoryInterface $repository)
    {
        $this->factory = $factory;
        $this->repository = $repository;
    }

    /**
     * @param AssociationTypeCreated $event
     */
    public function handleAssociationTypeCreated(AssociationTypeCreated $event)
    {
        /** @var ProductAssociationTypeInterface|null $associationType */
        $associationType = $this->repository->findOneBy(['code' => $event->code()]);
        if (null === $associationType) {
            $associationType = $this->factory->createNew();
            $associationType->setCode($event->code());
        }

        foreach ($event->names() as $locale => $name) {
            $associationType->setFallbackLocale($locale);
            $associationType->setCurrentLocale($locale);
            $associationType->setName($name);
        }

        $this->repository->add($associationType);
    }
}
