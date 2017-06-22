<?php

namespace Sylake\SyliusConsumerPlugin\Projector;

use Psr\Log\LoggerInterface;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param FactoryInterface $factory
     * @param RepositoryInterface $repository
     * @param LoggerInterface $logger
     */
    public function __construct(FactoryInterface $factory, RepositoryInterface $repository, LoggerInterface $logger)
    {
        $this->factory = $factory;
        $this->repository = $repository;
        $this->logger = $logger;
    }

    /**
     * @param AssociationTypeCreated $event
     */
    public function __invoke(AssociationTypeCreated $event)
    {
        $this->logger->debug(sprintf('Projecting association type with code "%s".', $event->code()));

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
