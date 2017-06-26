<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Attribute;

use Sylake\SyliusConsumerPlugin\Model\Attribute;
use Sylius\Component\Attribute\Model\AttributeValueInterface;
use Sylius\Component\Core\Model\ProductInterface;

final class StringCollectionAttributeProcessor implements AttributeProcessorInterface
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

        /** @var array $data */
        $data = $attribute->data();
        if ([] === $data) {
            return;
        }

        /** @var AttributeValueInterface|null $attributeValue */
        $attributeValue = $this->attributeValueProvider->provide($product, $attribute->attribute(), $attribute->locale());
        if (null === $attributeValue) {
            return;
        }

        $attributeValue->setValue(implode(', ', array_map(function (string $value) use ($attribute): string {
            return $this->attributeOptionResolver->resolve($attribute->attribute(), $attribute->locale(), $value);
        }, $data)));

        $product->addAttribute($attributeValue);
    }

    private function supports(Attribute $attribute): bool
    {
        return is_array($attribute->data()) && array_reduce($attribute->data(), function (bool $accumulator, $value): bool {
            return $accumulator && is_string($value) && !empty($value);
        }, true);
    }
}
