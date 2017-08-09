<?php

namespace Sylake\SyliusConsumerPlugin\Projector;

use Psr\Log\LoggerInterface;
use Sylake\SyliusConsumerPlugin\Entity\AkeneoFamily;
use Sylake\SyliusConsumerPlugin\Event\FamilyUpdated;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class FamilyProjector
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

    public function __invoke(FamilyUpdated $event): void
    {
        $this->logger->debug(sprintf('Projecting family with code "%s".', $event->code()));

        /** @var AkeneoFamily|null $akeneoFamily */
        $akeneoFamily = $this->repository->findOneBy(['code' => $event->code()]);
        if (null === $akeneoFamily) {
            $akeneoFamily = new AkeneoFamily($event->code(), $event->labels());
        }

        $akeneoFamily->setCode($event->code());
        $akeneoFamily->setLabels($event->labels());

        $this->repository->add($akeneoFamily);
    }
}
