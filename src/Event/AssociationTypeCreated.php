<?php

namespace Sylake\SyliusConsumerPlugin\Event;

final class AssociationTypeCreated
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var array
     */
    private $names;

    /**
     * @param string $code
     * @param array $names
     */
    public function __construct($code, array $names)
    {
        $this->code = $code;
        $this->names = $names;
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
    public function names()
    {
        return $this->names;
    }
}
