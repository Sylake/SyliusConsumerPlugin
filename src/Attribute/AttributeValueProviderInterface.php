<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Attribute;

use Sylius\Component\Attribute\Model\AttributeValueInterface;
use Sylius\Component\Core\Model\ProductInterface;

interface AttributeValueProviderInterface
{
    public function provide(ProductInterface $product, string $attributeCode, string $locale): ?AttributeValueInterface;
}
