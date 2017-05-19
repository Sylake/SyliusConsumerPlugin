<?php

namespace Sylake\SyliusConsumerPlugin\Event;

final class ProductCreated
{
    /**
     * @var string
     */
    private $code;

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
     * @param string|null $mainTaxon
     * @param array $taxons
     * @param \DateTime $createdAt
     */
    public function __construct(
        $code,
        $mainTaxon,
        array $taxons,
        \DateTime $createdAt
    ) {
        $this->code = $code;
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
