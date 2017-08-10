<?php

namespace Sylake\SyliusConsumerPlugin\Event;

final class ProductUpdated
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

    /**
     * @var ?string
     */
    private $family;

    /**
     * @var array
     */
    private $groups;

    public function __construct(
        string $code,
        bool $enabled,
        array $taxons,
        array $attributes,
        array $associations,
        ?string $family,
        array $groups,
        ?\DateTime $createdAt
    ) {
        $this->code = $code;
        $this->enabled = $enabled;
        $this->taxons = $taxons;
        $this->attributes = $attributes;
        $this->associations = $associations;
        $this->family = $family;
        $this->groups = $groups;
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

    public function family(): ?string
    {
        return $this->family;
    }

    public function groups(): array
    {
        return $this->groups;
    }

    public function createdAt(): ?\DateTime
    {
        return $this->createdAt;
    }
}
