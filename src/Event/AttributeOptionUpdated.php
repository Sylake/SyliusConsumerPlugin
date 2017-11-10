<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Event;

use Sylake\SyliusConsumerPlugin\Model\Translations;

final class AttributeOptionUpdated
{
    /** @var string */
    private $code;

    /** @var string */
    private $attributeCode;

    /** @var Translations */
    private $labels;

    public function __construct(string $code, string $attributeCode, Translations $labels)
    {
        $this->code = $code;
        $this->attributeCode = $attributeCode;
        $this->labels = $labels;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function attributeCode(): string
    {
        return $this->attributeCode;
    }

    public function labels(): Translations
    {
        return $this->labels;
    }
}
