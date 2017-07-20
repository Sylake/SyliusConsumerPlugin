<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Attribute;

use Sylake\SyliusConsumerPlugin\Model\Attribute;
use Sylius\Component\Attribute\Model\AttributeValueInterface;
use Sylius\Component\Core\Model\ProductInterface;

final class ScalarAttributeProcessor implements AttributeProcessorInterface
{
    /** @var AttributeValueProviderInterface */
    private $attributeValueProvider;

    /** @var AttributeOptionResolverInterface */
    private $attributeOptionResolver;

    public function __construct(
        AttributeValueProviderInterface $attributeValueProvider,
        AttributeOptionResolverInterface $attributeOptionResolver
    ) {
        $this->attributeValueProvider = $attributeValueProvider;
        $this->attributeOptionResolver = $attributeOptionResolver;
    }

    /** {@inheritdoc} */
    public function process(ProductInterface $product, Attribute $attribute): void
    {
        if (!$this->supports($attribute)) {
            return;
        }

        /** @var AttributeValueInterface|null $attributeValue */
        $attributeValue = $this->attributeValueProvider->provide($product, $attribute->attribute(), $attribute->locale());
        if (null === $attributeValue) {
            return;
        }

        $attributeValue->setValue($this->getValue($attribute));

        $product->addAttribute($attributeValue);
    }

    private function getValue(Attribute $attribute)
    {
        $value = $attribute->data();

        if (!is_string($value)) {
            return $value;
        }

        return $this->attributeOptionResolver->resolve($attribute->attribute(), $attribute->locale(), $value);
    }

    private function supports(Attribute $attribute): bool
    {
        return is_scalar($attribute->data()) && (null !== $attribute->data() || '' !== $attribute->data());
    }
}
