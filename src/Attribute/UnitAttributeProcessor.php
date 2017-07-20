<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Attribute;

use Sylake\SyliusConsumerPlugin\Model\Attribute;
use Sylius\Component\Attribute\Model\AttributeValueInterface;
use Sylius\Component\Core\Model\ProductInterface;

final class UnitAttributeProcessor implements AttributeProcessorInterface
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

        /** @var array $data */
        $data = $attribute->data();
        if (null === $data['amount']) {
            return [];
        }

        /** @var AttributeValueInterface|null $attributeValue */
        $attributeValue = $this->attributeValueProvider->provide($product, $attribute->attribute(), $attribute->locale());
        if (null === $attributeValue) {
            return [];
        }

        $attributeValue->setValue(sprintf('%s %s', $data['amount'], $data['unit']));

        return [$attributeValue];
    }

    private function supports(Attribute $attribute): bool
    {
        return is_array($attribute->data())
            && count($attribute->data()) === 2
            && array_key_exists('amount', $attribute->data())
            && array_key_exists('unit', $attribute->data())
        ;
    }
}
