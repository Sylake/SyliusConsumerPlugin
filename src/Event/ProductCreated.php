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
     * @var string|null
     */
    private $mainTaxon;

    /**
     * @var array
     */
    private $taxons;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @param string $code
     * @param bool $enabled
     * @param string|null $mainTaxon
     * @param array $taxons
     * @param \DateTime $createdAt
     */
    public function __construct(
        $code,
        $enabled,
        $mainTaxon,
        array $taxons,
        \DateTime $createdAt
    ) {
        $this->code = $code;
        $this->enabled = $enabled;
        $this->mainTaxon = $mainTaxon;
        $this->taxons = $taxons;
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
     * @return \DateTime
     */
    public function createdAt()
    {
        return $this->createdAt;
    }
}
