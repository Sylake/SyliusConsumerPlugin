<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Entity;

use Sylake\SyliusConsumerPlugin\Model\Translations;

class AkeneoAttributeOption
{
    /** @var string */
    private $code;

    /** @var string */
    private $attributeCode;

    /** @var array */
    private $labels;

    public function __construct(string $code, string $attributeCode, Translations $labels)
    {
        $this->code = $code;
        $this->attributeCode = $attributeCode;
        $this->labels = $labels->toArray();
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getAttributeCode(): string
    {
        return $this->attributeCode;
    }

    public function setAttributeCode(string $attributeCode): void
    {
        $this->attributeCode = $attributeCode;
    }

    public function getLabels(): array
    {
        return $this->labels;
    }

    public function setLabels(Translations $labels): void
    {
        $this->labels = $labels->toArray();
    }
}
