<?php

namespace Sylake\SyliusConsumerPlugin\Event;

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
     * @param string $code
     * @param array $taxons
     * @param \DateTime $createdAt
     */
    public function __construct(
        $code,
        array $taxons,
        \DateTime $createdAt
    ) {
        $this->code = $code;
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
