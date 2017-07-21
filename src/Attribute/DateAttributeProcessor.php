<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Attribute;

use Sylake\SyliusConsumerPlugin\Model\Attribute;
use Sylius\Component\Attribute\Model\AttributeValueInterface;
use Sylius\Component\Core\Model\ProductInterface;

final class DateAttributeProcessor implements AttributeProcessorInterface
{
    /** @var AttributeValueProviderInterface */
    private $attributeValueProvider;

    public function __construct(AttributeValueProviderInterface $attributeValueProvider)
    {
        $this->attributeValueProvider = $attributeValueProvider;
    }

    /** {@inheritdoc} */
    public function process(ProductInterface $product, Attribute $attribute): array
    {
        if (!$this->supports($attribute)) {
            return [];
        }

        /** @var AttributeValueInterface|null $attributeValue */
        $attributeValue = $this->attributeValueProvider->provide($product, $attribute->attribute(), $attribute->locale());
        if (null === $attributeValue) {
            return [];
        }

        $attributeValue->setValue(\DateTime::createFromFormat(\DateTime::ATOM, $attribute->data()));

        return [$attributeValue];
    }

    private function supports(Attribute $attribute): bool
    {
        return is_string($attribute->data())
            && '' !== $attribute->data()
            && false !== \DateTime::createFromFormat(\DateTime::ATOM, $attribute->data())
        ;
    }
}
