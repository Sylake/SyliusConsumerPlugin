<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Entity;

use Sylake\SyliusConsumerPlugin\Model\Translations;
use Sylius\Component\Resource\Model\ResourceInterface;

class AkeneoFamily implements ResourceInterface
{
    /** @var string */
    private $code;

    /** @var array */
    private $labels;

    public function __construct(string $code, Translations $labels)
    {
        $this->code = $code;
        $this->labels = $labels->toArray();
    }

    /** {@inheritdoc} */
    public function getId(): string
    {
        return $this->code;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
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
