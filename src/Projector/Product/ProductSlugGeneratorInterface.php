<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Projector\Product;

use Sylius\Component\Core\Model\ProductInterface;

interface ProductSlugGeneratorInterface
{
    /**
     * @param ProductInterface $product
     * @param string $locale
     *
     * @return string
     */
    public function generate(ProductInterface $product, $locale);
}
