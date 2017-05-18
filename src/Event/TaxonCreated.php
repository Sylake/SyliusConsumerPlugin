<?php

namespace Sylake\SyliusConsumerPlugin\Event;

use Sylake\SyliusConsumerPlugin\Model\Translations;

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
     * @var Translations
     */
    private $names;

    /**
     * @param string $code
     * @param string $parent
     * @param Translations $names
     */
    public function __construct($code, $parent, Translations $names)
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
     * @return Translations
     */
    public function names()
    {
        return $this->names;
    }
}
