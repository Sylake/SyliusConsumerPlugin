<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Projector\Product;

use Sylake\SyliusConsumerPlugin\Event\ProductUpdated;
use Sylake\SyliusConsumerPlugin\Model\Attributes;
use Sylake\SyliusConsumerPlugin\Projector\Product\Attribute\AttributeProcessorInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Locale\Model\LocaleInterface;
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
        $attributes = array_merge(
            $event->attributes(),
            $this->provideFamilyRelatedAttributes($event),
            $this->provideGroupsRelatedAttributes($event)
        );

        $this->handleAttributes($attributes, $product);
    }

    private function handleAttributes(array $attributes, ProductInterface $product): void
    {
        $currentProductAttributes = $product->getAttributes()->toArray();
        $processedProductAttributes = $this->processAttributes($attributes, $product);

        $productTaxonToAdd = ResourceUtil::diff($processedProductAttributes, $currentProductAttributes);
        foreach ($productTaxonToAdd as $productTaxon) {
            $product->addAttribute($productTaxon);
        }

        $productTaxonToRemove = ResourceUtil::diff($currentProductAttributes, $processedProductAttributes);
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

    private function provideFamilyRelatedAttributes(ProductUpdated $event): array
    {
        $attributes = [];

        if (null === $event->family()) {
            return $attributes;
        }

        $attributes['AKENEO_FAMILY_CODE'] = [['locale' => null, 'data' => $event->family()]];

        return $attributes;
    }

    private function provideGroupsRelatedAttributes(ProductUpdated $event): array
    {
        $attributes = [];

        if ([] === $event->groups()) {
            return $attributes;
        }

        $attributes['AKENEO_GROUPS_CODES'] = [['locale' => null, 'data' => $event->groups()]];

        return $attributes;
    }
}
