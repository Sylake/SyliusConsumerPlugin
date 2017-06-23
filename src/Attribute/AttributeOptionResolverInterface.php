<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Attribute;

interface AttributeOptionResolverInterface
{
    public function resolve(string $attribute, string $locale, string $data): string;
}
