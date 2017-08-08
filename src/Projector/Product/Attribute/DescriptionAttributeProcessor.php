<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Projector\Product\Attribute;

use Sylake\SyliusConsumerPlugin\Model\Attribute;
use Sylius\Component\Core\Model\ProductInterface;

final class DescriptionAttributeProcessor implements AttributeProcessorInterface
{
    /** @var string */
    private $descriptionAttribute;

    public function __construct(string $descriptionAttribute)
    {
        $this->descriptionAttribute = $descriptionAttribute;
    }

    /** {@inheritdoc} */
    public function process(ProductInterface $product, Attribute $attribute): array
    {
        if (!$this->supports($attribute)) {
            return [];
        }

        $product->setFallbackLocale($attribute->locale());
        $product->setCurrentLocale($attribute->locale());

        $product->setDescription($attribute->data());

        return [];
    }

    private function supports(Attribute $attribute): bool
    {
        return $this->descriptionAttribute === $attribute->attribute() && is_string($attribute->data());
    }

}
