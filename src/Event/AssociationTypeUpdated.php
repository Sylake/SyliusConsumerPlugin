<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Event;

use Sylake\SyliusConsumerPlugin\Model\Translations;

final class AssociationTypeUpdated
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var Translations
     */
    private $names;

    /**
     * @param string $code
     * @param Translations $names
     */
    public function __construct($code, Translations $names)
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
     * @return Translations
     */
    public function names()
    {
        return $this->names;
    }
}
