<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Projector\Product\Attribute;

use Sylake\SyliusConsumerPlugin\Model\Attribute;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;

interface AttributeProcessorInterface
{
    /**
     * @param ProductInterface $product
     * @param Attribute $attribute
     *
     * @return ProductAttributeValueInterface[]
     */
    public function process(ProductInterface $product, Attribute $attribute): array;
}
