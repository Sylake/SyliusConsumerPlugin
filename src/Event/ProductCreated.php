<?php

namespace Sylake\SyliusConsumerPlugin\Event;

final class ProductCreated
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var string|null
     */
    private $mainTaxon;

    /**
     * @var array
     */
    private $taxons;

    /**
     * @var array
     */
    private $prices;

    /**
     * @var array
     */
    private $attributes;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @param string $code
     * @param string $name
     * @param string $description
     * @param bool $enabled
     * @param string|null $mainTaxon
     * @param array $taxons
     * @param array $prices
     * @param array $attributes
     * @param \DateTime $createdAt
     */
    public function __construct(
        $code,
        $name,
        $description,
        $enabled,
        $mainTaxon,
        array $taxons,
        array $prices,
        array $attributes,
        \DateTime $createdAt
    ) {
        $this->code = $code;
        $this->name = $name;
        $this->description = $description;
        $this->enabled = $enabled;
        $this->mainTaxon = $mainTaxon;
        $this->taxons = $taxons;
        $this->prices = $prices;
        $this->attributes = $attributes;
        $this->createdAt = $createdAt;
    }

    /**
     * @return string
     */
    public function code()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function description()
    {
        return $this->description;
    }

    /**
     * @return bool
     */
    public function enabled()
    {
        return $this->enabled;
    }

    /**
     * @return string|null
     */
    public function mainTaxon()
    {
        return $this->mainTaxon;
    }

    /**
     * @return array
     */
    public function taxons()
    {
        return $this->taxons;
    }

    /**
     * @return array
     */
    public function prices()
    {
        return $this->prices;
    }

    /**
     * @return array
     */
    public function attributes()
    {
        return $this->attributes;
    }

    /**
     * @return \DateTime
     */
    public function createdAt()
    {
        return $this->createdAt;
    }
}
