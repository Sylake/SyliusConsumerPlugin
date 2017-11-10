<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Denormalizer;

use Sylake\SyliusConsumerPlugin\Event\AssociationTypeUpdated;
use Sylake\SyliusConsumerPlugin\Model\Translations;

final class AssociationTypeUpdatedDenormalizer extends AkeneoDenormalizer
{
    /**
     * {@inheritdoc}
     */
    protected function denormalizePayload(array $payload)
    {
        return new AssociationTypeUpdated($payload['code'], new Translations($payload['labels']));
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedMessageType(): string
    {
        return 'akeneo_association_type_updated';
    }
}
