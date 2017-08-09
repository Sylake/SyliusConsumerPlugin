<?php

namespace Sylake\SyliusConsumerPlugin\Projector;

use Psr\Log\LoggerInterface;
use Sylake\SyliusConsumerPlugin\Entity\AkeneoGroup;
use Sylake\SyliusConsumerPlugin\Event\GroupUpdated;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class GroupProjector
{
    /** @var RepositoryInterface */
    private $repository;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(RepositoryInterface $repository, LoggerInterface $logger)
    {
        $this->repository = $repository;
        $this->logger = $logger;
    }

    public function __invoke(GroupUpdated $event): void
    {
        $this->logger->debug(sprintf('Projecting group with code "%s".', $event->code()));

        /** @var AkeneoGroup|null $akeneoGroup */
        $akeneoGroup = $this->repository->findOneBy(['code' => $event->code()]);
        if (null === $akeneoGroup) {
            $akeneoGroup = new AkeneoGroup($event->code(), $event->labels());
        }

        $akeneoGroup->setCode($event->code());
        $akeneoGroup->setLabels($event->labels());

        $this->repository->add($akeneoGroup);
    }
}
