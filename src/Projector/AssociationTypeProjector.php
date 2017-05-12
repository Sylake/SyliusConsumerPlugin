<?php

namespace Sylake\RabbitmqAkeneo\Projector;

use Sylake\RabbitmqAkeneo\Event\AssociationTypeCreated;
use Sylius\Component\Product\Model\ProductAssociationTypeInterface;
use Sylius\Component\Product\Repository\ProductAssociationTypeRepositoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class AssociationTypeProjector
{
    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var ProductAssociationTypeRepositoryInterface
     */
    private $repository;

    /**
     * @param FactoryInterface $factory
     * @param ProductAssociationTypeRepositoryInterface $repository
     */
    public function __construct(FactoryInterface $factory, ProductAssociationTypeRepositoryInterface $repository)
    {
        $this->factory = $factory;
        $this->repository = $repository;
    }

    /**
     * @param AssociationTypeCreated $event
     */
    public function handleAssociationTypeCreated(AssociationTypeCreated $event)
    {
        /** @var ProductAssociationTypeInterface $associationType */
        $associationType = $this->factory->createNew();
        $associationType->setCode($event->code());
        foreach ($event->names() as $locale => $name) {
            $associationType->setCurrentLocale($locale);
            $associationType->setName($name);
        }

        $this->repository->add($associationType);
    }
}
