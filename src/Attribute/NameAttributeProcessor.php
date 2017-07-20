<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Attribute;

use Sylake\SyliusConsumerPlugin\Model\Attribute;
use Sylius\Component\Core\Model\ProductInterface;

final class NameAttributeProcessor implements AttributeProcessorInterface
{
    /** @var string */
    private $nameAttribute;

    public function __construct(string $nameAttribute)
    {
        $this->nameAttribute = $nameAttribute;
    }

    /** {@inheritdoc} */
    public function process(ProductInterface $product, Attribute $attribute): array
    {
        if (!$this->supports($attribute)) {
            return [];
        }

        $product->setFallbackLocale($attribute->locale());
        $product->setCurrentLocale($attribute->locale());

        $product->setName($attribute->data() ?: $product->getCode());

        return [];
    }

    private function supports(Attribute $attribute): bool
    {
        return $this->nameAttribute === $attribute->attribute() && (is_string($attribute->data()) || null === $attribute->data());
    }

}
