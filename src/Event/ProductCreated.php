<?php

namespace Sylake\SyliusConsumerPlugin\Event;

final class ProductCreated
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var ?string
     */
    private $mainTaxon;

    /**
     * @var array
     */
    private $taxons;

    /**
     * @var array
     */
    private $attributes;

    /**
     * @var array
     */
    private $associations;

    /**
     * @var ?\DateTime
     */
    private $createdAt;

    public function __construct(
        string $code,
        bool $enabled,
        ?string $mainTaxon,
        array $taxons,
        array $attributes,
        array $associations,
        ?\DateTime $createdAt
    ) {
        $this->code = $code;
        $this->enabled = $enabled;
        $this->mainTaxon = $mainTaxon;
        $this->taxons = $taxons;
        $this->attributes = $attributes;
        $this->associations = $associations;
        $this->createdAt = $createdAt;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }

    public function mainTaxon(): ?string
    {
        return $this->mainTaxon;
    }

    public function taxons(): array
    {
        return $this->taxons;
    }

    public function attributes(): array
    {
        return $this->attributes;
    }

    public function associations(): array
    {
        return $this->associations;
    }

    public function createdAt(): ?\DateTime
    {
        return $this->createdAt;
    }
}
