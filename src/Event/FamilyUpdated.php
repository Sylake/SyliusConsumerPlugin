<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Event;

use Sylake\SyliusConsumerPlugin\Model\Translations;

final class FamilyUpdated
{
    /** @var string */
    private $code;

    /** @var Translations */
    private $labels;

    public function __construct(string $code, Translations $labels)
    {
        $this->code = $code;
        $this->labels = $labels;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function labels(): Translations
    {
        return $this->labels;
    }
}
