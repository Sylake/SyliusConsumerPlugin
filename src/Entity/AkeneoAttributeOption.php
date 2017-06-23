<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Entity;

use Sylake\SyliusConsumerPlugin\Model\Translations;
use Sylius\Component\Resource\Model\ResourceInterface;

class AkeneoAttributeOption implements ResourceInterface
{
    /** @var string */
    private $code;

    /** @var string */
    private $attribute;

    /** @var array */
    private $labels;

    public function __construct(string $code, string $attribute, Translations $labels)
    {
        $this->code = $code;
        $this->attribute = $attribute;
        $this->labels = $labels->toArray();
    }

    /** {@inheritdoc} */
    public function getId(): string
    {
        return $this->code . ':' . $this->attribute;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getAttribute(): string
    {
        return $this->attribute;
    }

    public function setAttribute(string $attribute): void
    {
        $this->attribute = $attribute;
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
