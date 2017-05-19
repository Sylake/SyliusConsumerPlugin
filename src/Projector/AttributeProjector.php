<?php

namespace Sylake\SyliusConsumerPlugin\Projector;

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
     * @param AttributeFactoryInterface $factory
     * @param RepositoryInterface $repository
     */
    public function __construct(AttributeFactoryInterface $factory, RepositoryInterface $repository)
    {
        $this->factory = $factory;
        $this->repository = $repository;
    }

    /**
     * @param AttributeCreated $event
     */
    public function handleAttributeCreated(AttributeCreated $event)
    {
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
