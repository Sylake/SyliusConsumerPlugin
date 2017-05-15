<?php

namespace Sylake\RabbitmqAkeneo\Event;

final class ProductCreated
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var array
     */
    private $taxons;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var array
     */
    private $associations;

    /**
     * @var array
     */
    private $price;

    /**
     * @var array
     */
    private $attributes;

    /**
     * @var array
     */
    private $description;

    /**
     * @param string $code
     * @param array $taxons
     * @param \DateTime $createdAt
     * @param array $associations
     * @param array $price
     * @param array $attributes
     * @param array $description
     */
    public function __construct(
        $code,
        array $taxons,
        \DateTime $createdAt,
        array $associations,
        array $price,
        array $attributes,
        array $description
    ) {
        $this->code = $code;
        $this->taxons = $taxons;
        $this->createdAt = $createdAt;
        $this->associations = $associations;
        $this->price = $price;
        $this->attributes = $attributes;
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function code()
    {
        return $this->code;
    }

    /**
     * @return array
     */
    public function taxons()
    {
        return $this->taxons;
    }

    /**
     * @return \DateTime
     */
    public function createdAt()
    {
        return $this->createdAt;
    }

    /**
     * @return array
     */
    public function associations()
    {
        return $this->associations;
    }

    /**
     * @return array
     */
    public function price()
    {
        return $this->price;
    }

    /**
     * @return array
     */
    public function attributes()
    {
        return $this->attributes;
    }

    /**
     * @return array
     */
    public function description()
    {
        return $this->description;
    }
}
