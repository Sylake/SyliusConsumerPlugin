<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Model;

final class Attributes implements \IteratorAggregate
{
    /** @var array */
    private $attributes;

    /** @var array */
    private $locales;

    public function __construct(array $attributes, array $locales)
    {
        $this->attributes = $attributes;
        $this->locales = $locales;
    }

    /** {@inheritdoc} */
    public function getIterator(): \Generator
    {
        foreach ($this->attributes as $attributeCode => $attributeValues) {
            if (!is_array($attributeValues)) {
                continue;
            }

            /** @var array $attributeValues */
            foreach ($attributeValues as $attributeValue) {
                yield from $this->generateLocalisedAttributes($attributeCode, $attributeValue);
            }
        }
    }

    /**
     * Akeneo uses `null` as value for locale if given attribute value is not meant to be translated and valid for all langauges.
     * Sylius requires all attribute values to be localised, so we hack it by creating an attribute value for each language.
     */
    private function generateLocalisedAttributes(string $attributeCode, array $attributeValue): \Generator
    {
        if ($attributeValue['locale'] !== null) {
            yield new Attribute($attributeCode, $attributeValue['locale'], $attributeValue['data']);

            return;
        }

        foreach ($this->locales as $locale) {
            yield new Attribute($attributeCode, $locale, $attributeValue['data']);
        }
    }
}
