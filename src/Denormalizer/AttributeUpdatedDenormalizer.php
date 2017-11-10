<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Denormalizer;

use Sylake\SyliusConsumerPlugin\Event\AttributeUpdated;
use Sylake\SyliusConsumerPlugin\Model\Translations;

final class AttributeUpdatedDenormalizer extends AkeneoDenormalizer
{
    /**
     * {@inheritdoc}
     */
    protected function denormalizePayload(array $payload)
    {
        return new AttributeUpdated($payload['code'], $payload['type'], new Translations($payload['labels']));
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedMessageType(): string
    {
        return 'akeneo_attribute_updated';
    }
}
