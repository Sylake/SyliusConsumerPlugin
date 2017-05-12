<?php

namespace Sylake\RabbitmqAkeneo\Event;

final class TaxonCreated
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $parent;

    /**
     * @var array
     */
    private $names;

    /**
     * @param string $code
     * @param string $parent
     * @param array $names
     */
    public function __construct($code, $parent, array $names)
    {
        $this->code = $code;
        $this->parent = $parent;
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
     * @return string
     */
    public function parent()
    {
        return $this->parent;
    }

    /**
     * @return array
     */
    public function names()
    {
        return $this->names;
    }
}
