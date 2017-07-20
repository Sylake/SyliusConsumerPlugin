<?php

namespace Sylake\SyliusConsumerPlugin\Model;

final class Translations implements \IteratorAggregate
{
    /**
     * @var array
     */
    private $translations = [];

    /**
     * @param array $translations
     */
    public function __construct(array $translations)
    {
        foreach ($translations as $locale => $translation) {
            if (false === (bool) preg_match('/^[a-z]{2}(?:_[A-Z]{2})?$/', $locale)) {
                throw new \InvalidArgumentException('Invalid locale passed: ' . $locale);
            }

            if (null === $translation) {
                continue;
            }

            if (!is_scalar($translation)) {
                throw new \InvalidArgumentException(sprintf(
                    'Translation should be a scalar, %s given.',
                    gettype($translation)
                ));
            }

            $this->translations[$locale] = $translation;
        }
    }

    public function toArray(): array
    {
        return $this->translations;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->translations);
    }
}
