<?php

namespace Sylake\SyliusConsumerPlugin\Projector;

use Psr\Log\LoggerInterface;
use Sylake\SyliusConsumerPlugin\Event\AttributeCreated;
use Sylius\Component\Attribute\AttributeType\TextAttributeType;
use Sylius\Component\Attribute\Factory\AttributeFactoryInterface;
use Sylius\Component\Product\Model\ProductAttributeInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class AttributeProjector
{
    /**
     * @var AttributeFactoryInterface
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
     * @param AttributeFactoryInterface $factory
     * @param RepositoryInterface $repository
     * @param LoggerInterface $logger
     */
    public function __construct(AttributeFactoryInterface $factory, RepositoryInterface $repository, LoggerInterface $logger)
    {
        $this->factory = $factory;
        $this->repository = $repository;
        $this->logger = $logger;
    }

    /**
     * @param AttributeCreated $event
     */
    public function __invoke(AttributeCreated $event)
    {
        $this->logger->debug(sprintf('Projecting attribute with code "%s".', $event->code()));

        /** @var ProductAttributeInterface|null $attribute */
        $attribute = $this->repository->findOneBy(['code' => $event->code()]);
        if (null === $attribute) {
            $attribute = $this->factory->createTyped(TextAttributeType::TYPE);
            $attribute->setCode($event->code());
        }

        foreach ($event->names() as $locale => $name) {
            $attribute->setFallbackLocale($locale);
            $attribute->setCurrentLocale($locale);
            $attribute->setName($name);
        }

        $this->repository->add($attribute);
    }
}
