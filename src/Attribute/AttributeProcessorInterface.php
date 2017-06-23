<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Attribute;

use Sylake\SyliusConsumerPlugin\Model\Attribute;
use Sylius\Component\Core\Model\ProductInterface;

interface AttributeProcessorInterface
{
    public function process(ProductInterface $product, Attribute $attribute): void;
}
