<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Projector;

use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductTranslationInterface;
use Sylius\Component\Product\Generator\SlugGeneratorInterface;

final class ProductSlugGenerator implements ProductSlugGeneratorInterface
{
    /**
     * @var SlugGeneratorInterface
     */
    private $slugGenerator;

    /**
     * @param SlugGeneratorInterface $slugGenerator
     */
    public function __construct(SlugGeneratorInterface $slugGenerator)
    {
        $this->slugGenerator = $slugGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(ProductInterface $product, $locale)
    {
        /** @var ProductTranslationInterface $productTranslation */
        $productTranslation = $product->getTranslation($locale);
        $name = $productTranslation->getName();

        return $this->slugGenerator->generate($product->getCode() . ($name ? ' ' . $name : ''));
    }
}
