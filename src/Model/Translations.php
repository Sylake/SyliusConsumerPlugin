<?php

namespace Sylake\SyliusConsumerPlugin\Model;

final class Translations implements \IteratorAggregate
{
    /**
     * @var array
     */
    private $translations;

    /**
     * @param array $translations
     */
    public function __construct(array $translations)
    {
        foreach ($translations as $locale => $translation) {
            if (false === (bool) preg_match('/^[a-z]{2}(?:_[A-Z]{2})?$/', $locale)) {
                throw new \InvalidArgumentException('Invalid locale passed: ' . $locale);
            }

            if (!is_string($translation)) {
                throw new \InvalidArgumentException(sprintf(
                    'Translation should be a string, %s given.',
                    gettype($translation)
                ));
            }
        }

        $this->translations = $translations;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->translations);
    }
}
