<?php

namespace Sylake\SyliusConsumerPlugin\Event;

use Sylake\SyliusConsumerPlugin\Model\Translations;

final class AttributeUpdated
{
    /** @var string */
    private $code;

    /** @var string */
    private $type;

    /**@var Translations */
    private $names;

    public function __construct(string $code, string $type, Translations $names)
    {
        $this->code = $code;
        $this->type = $type;
        $this->names = $names;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function names(): Translations
    {
        return $this->names;
    }
}
