<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Model;

final class Attribute
{
    /** @var string */
    private $attribute;

    /** @var string */
    private $locale;

    /** @var mixed */
    private $data;

    public function __construct(string $attribute, string $locale, $data)
    {
        $this->attribute = $attribute;
        $this->locale = $locale;
        $this->data = $data;
    }

    public function attribute(): string
    {
        return $this->attribute;
    }

    public function locale(): string
    {
        return $this->locale;
    }

    public function data()
    {
        return $this->data;
    }
}
