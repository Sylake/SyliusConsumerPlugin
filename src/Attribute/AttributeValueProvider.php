<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Attribute;

use Sylius\Component\Attribute\Model\AttributeInterface;
use Sylius\Component\Attribute\Model\AttributeValueInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class AttributeValueProvider implements AttributeValueProviderInterface
{
    /** @var FactoryInterface */
    private $attributeValueFactory;

    /** @var RepositoryInterface */
    private $attributeRepository;

    /** @var RepositoryInterface */
    private $attributeValueRepository;

    public function __construct(
        FactoryInterface $attributeValueFactory,
        RepositoryInterface $attributeRepository,
        RepositoryInterface $attributeValueRepository
    ) {
        $this->attributeValueFactory = $attributeValueFactory;
        $this->attributeRepository = $attributeRepository;
        $this->attributeValueRepository = $attributeValueRepository;
    }

    /** {@inheritdoc} */
    public function provide(ProductInterface $product, string $attributeCode, string $locale): ?AttributeValueInterface
    {
        /** @var AttributeInterface|null $attribute */
        $attribute = $this->attributeRepository->findOneBy(['code' => $attributeCode]);
        if (null === $attribute) {
            return null;
        }

        /** @var AttributeValueInterface $attributeValue */
        $attributeValue = $this->attributeValueRepository->findOneBy([
            'attribute' => $attribute,
            'subject' => $product,
            'localeCode' => $locale,
        ]);

        if (null === $attributeValue) {
            $attributeValue = $this->attributeValueFactory->createNew();
            $attributeValue->setLocaleCode($locale);
            $attributeValue->setAttribute($attribute);
            $attributeValue->setSubject($product);
        }

        return $attributeValue;
    }
}
