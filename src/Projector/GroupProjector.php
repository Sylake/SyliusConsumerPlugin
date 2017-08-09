<?php

namespace Sylake\SyliusConsumerPlugin\Projector;

use Psr\Log\LoggerInterface;
use Sylake\SyliusConsumerPlugin\Entity\AkeneoAttributeOption;
use Sylake\SyliusConsumerPlugin\Entity\AkeneoGroup;
use Sylake\SyliusConsumerPlugin\Event\GroupUpdated;
use Sylake\SyliusConsumerPlugin\Model\Translations;
use Sylius\Component\Attribute\AttributeType\TextAttributeType;
use Sylius\Component\Attribute\Factory\AttributeFactoryInterface;
use Sylius\Component\Product\Model\ProductAttributeInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class GroupProjector
{
    /** @var RepositoryInterface */
    private $attributeRepository;

    /** @var RepositoryInterface */
    private $akeneoAttributeOptionRepository;

    /** @var AttributeFactoryInterface */
    private $attributeFactory;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        RepositoryInterface $attributeRepository,
        RepositoryInterface $akeneoAttributeOptionRepository,
        AttributeFactoryInterface $attributeFactory,
        LoggerInterface $logger
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->akeneoAttributeOptionRepository = $akeneoAttributeOptionRepository;
        $this->attributeFactory = $attributeFactory;
        $this->logger = $logger;
    }

    public function __invoke(GroupUpdated $event): void
    {
        $this->logger->debug(sprintf('Projecting family with code "%s".', $event->code()));

        $this->setupAttributes();

        /** @var AkeneoAttributeOption|null $akeneoAttributeOption */
        $akeneoAttributeOption = $this->akeneoAttributeOptionRepository->findOneBy([
            'code' => $event->code(),
            'attribute' => 'AKENEO_GROUPS_NAMES',
        ]);

        if (null === $akeneoAttributeOption) {
            $akeneoAttributeOption = new AkeneoAttributeOption($event->code(), 'AKENEO_GROUPS_NAMES', $event->labels());
        }

        $akeneoAttributeOption->setCode($event->code());
        $akeneoAttributeOption->setAttribute('AKENEO_GROUPS_NAMES');
        $akeneoAttributeOption->setLabels($event->labels());

        $this->akeneoAttributeOptionRepository->add($akeneoAttributeOption);
    }

    private function setupAttributes(): void
    {
        $this->createAttributeIfNotExists('AKENEO_GROUPS_CODES', new Translations([
            'en_GB' => 'Groups codes',
            'de_DE' => 'Gruppencodes',
        ]));

        $this->createAttributeIfNotExists('AKENEO_GROUPS_NAMES', new Translations([
            'en_GB' => 'Groups',
            'de_DE' => 'Gruppen',
        ]));
    }

    private function createAttributeIfNotExists(string $code, Translations $names): void
    {
        /** @var ProductAttributeInterface|null $attribute */
        $attribute = $this->attributeRepository->findOneBy(['code' => $code]);
        if (null !== $attribute) {
            return;
        }

        $attribute = $this->attributeFactory->createTyped(TextAttributeType::TYPE);
        $attribute->setCode($code);

        foreach ($names as $locale => $name) {
            $attribute->setFallbackLocale($locale);
            $attribute->setCurrentLocale($locale);
            $attribute->setName($name);
        }

        $this->attributeRepository->add($attribute);
    }
}
