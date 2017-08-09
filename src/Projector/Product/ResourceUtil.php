<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Projector\Product;

use Sylius\Component\Resource\Model\ResourceInterface;

abstract class ResourceUtil
{
    private function __construct()
    {
    }

    public static function diff(array $a, array $b): array
    {
        return array_udiff(
            $a,
            $b,
            function (ResourceInterface $a, ResourceInterface $b): int {
                return $a->getId() <=> $b->getId();
            }
        );
    }
}
