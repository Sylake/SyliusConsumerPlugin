<?php

namespace Sylake\SyliusConsumerPlugin\Projector;

use Psr\Log\LoggerInterface;
use Sylake\SyliusConsumerPlugin\Event\AttributeUpdated;
use Sylius\Component\Attribute\AttributeType\AttributeTypeInterface;
use Sylius\Component\Attribute\AttributeType\CheckboxAttributeType;
use Sylius\Component\Attribute\AttributeType\DateAttributeType;
use Sylius\Component\Attribute\AttributeType\TextareaAttributeType;
use Sylius\Component\Attribute\AttributeType\TextAttributeType;
use Sylius\Component\Attribute\Factory\AttributeFactoryInterface;
use Sylius\Component\Product\Model\ProductAttributeInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class AttributeProjector
{
    /** @var AttributeFactoryInterface */
    private $factory;

    /** @var RepositoryInterface */
    private $repository;

    /** @var ServiceRegistryInterface */
    private $attributeTypesRegistry;

    /** @var LoggerInterface */
    private $logger;

    /** @var array */
    private static $akeneoTypeToSyliusType = [
        'pim_catalog_text' => TextAttributeType::TYPE,
        'pim_catalog_textarea' => TextareaAttributeType::TYPE,
        'pim_catalog_boolean' => CheckboxAttributeType::TYPE,
        'pim_catalog_date' => DateAttributeType::TYPE,
    ];

    public function __construct(
        AttributeFactoryInterface $factory,
        RepositoryInterface $repository,
        ServiceRegistryInterface $attributeTypesRegistry,
        LoggerInterface $logger
    ) {
        $this->factory = $factory;
        $this->repository = $repository;
        $this->attributeTypesRegistry = $attributeTypesRegistry;
        $this->logger = $logger;
    }

    /**
     * @param AttributeUpdated $event
     */
    public function __invoke(AttributeUpdated $event): void
    {
        $this->logger->debug(sprintf('Projecting attribute with code "%s".', $event->code()));

        /** @var ProductAttributeInterface|null $attribute */
        $attribute = $this->repository->findOneBy(['code' => $event->code()]);
        if (null === $attribute) {
            $attribute = $this->factory->createTyped(TextAttributeType::TYPE);
            $attribute->setCode($event->code());
        }

        $attribute->setType($this->mapAkeneoTypeToSyliusType($event->type()));
        $attribute->setStorageType($this->mapAkeneoTypeToSyliusStorageType($event->type()));

        foreach ($event->names() as $locale => $name) {
            $attribute->setFallbackLocale($locale);
            $attribute->setCurrentLocale($locale);
            $attribute->setName($name);
        }

        $this->repository->add($attribute);
    }

    private function mapAkeneoTypeToSyliusType(string $akeneoType): string
    {
        return static::$akeneoTypeToSyliusType[$akeneoType] ?? TextAttributeType::TYPE;
    }

    private function mapAkeneoTypeToSyliusStorageType(string $akeneoType): string
    {
        $syliusType = $this->mapAkeneoTypeToSyliusType($akeneoType);

        /** @var AttributeTypeInterface $attributeType */
        $attributeType = $this->attributeTypesRegistry->get($syliusType);

        return $attributeType->getStorageType();
    }
}
