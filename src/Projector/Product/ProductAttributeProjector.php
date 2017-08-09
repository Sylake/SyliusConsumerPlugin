<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Projector\Product;

use Sylake\SyliusConsumerPlugin\Event\ProductUpdated;
use Sylake\SyliusConsumerPlugin\Model\Attributes;
use Sylake\SyliusConsumerPlugin\Projector\Product\Attribute\AttributeProcessorInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Locale\Model\LocaleInterface;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class ProductAttributeProjector
{
    /** @var RepositoryInterface */
    private $localeRepository;

    /** @var AttributeProcessorInterface */
    private $attributeProcessor;

    public function __construct(
        RepositoryInterface $localeRepository,
        AttributeProcessorInterface $attributeProcessor
    ) {
        $this->localeRepository = $localeRepository;
        $this->attributeProcessor = $attributeProcessor;
    }

    public function __invoke(ProductUpdated $event, ProductInterface $product): void
    {
        $this->handleAttributes($event->attributes(), $product);
    }

    private function handleAttributes(array $attributes, ProductInterface $product): void
    {
        $currentProductAttributes = $product->getAttributes()->toArray();
        $processedProductAttributes = $this->processAttributes($attributes, $product);

        $compareProductAttributes = function (ProductAttributeValueInterface $a, ProductAttributeValueInterface $b): int {
            return $a->getId() <=> $b->getId();
        };

        $productTaxonToAdd = array_udiff(
            $processedProductAttributes,
            $currentProductAttributes,
            $compareProductAttributes
        );
        foreach ($productTaxonToAdd as $productTaxon) {
            $product->addAttribute($productTaxon);
        }

        $productTaxonToRemove = array_udiff(
            $currentProductAttributes,
            $processedProductAttributes,
            $compareProductAttributes
        );
        foreach ($productTaxonToRemove as $productTaxon) {
            $product->removeAttribute($productTaxon);
        }
    }

    private function processAttributes(array $attributes, ProductInterface $product): array
    {
        $processedAttributes = [];

        $locales = array_map(function (LocaleInterface $locale): string {
            return $locale->getCode();
        }, $this->localeRepository->findAll());

        foreach (new Attributes($attributes, $locales) as $attribute) {
            $processedAttributes = array_merge($processedAttributes, $this->attributeProcessor->process($product, $attribute));
        }

        return $processedAttributes;
    }
}
