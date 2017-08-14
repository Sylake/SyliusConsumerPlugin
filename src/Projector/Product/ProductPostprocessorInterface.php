<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Projector\Product;

use Sylake\SyliusConsumerPlugin\Event\ProductUpdated;
use Sylius\Component\Core\Model\ProductInterface;

interface ProductPostprocessorInterface
{
    public function __invoke(ProductUpdated $event, ProductInterface $product): void;
}
