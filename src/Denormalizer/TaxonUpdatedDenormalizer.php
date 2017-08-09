<?php

namespace Sylake\SyliusConsumerPlugin\Denormalizer;

use Sylake\SyliusConsumerPlugin\Event\TaxonUpdated;
use Sylake\SyliusConsumerPlugin\Model\Translations;

final class TaxonUpdatedDenormalizer extends AkeneoDenormalizer
{
    /**
     * {@inheritdoc}
     */
    protected function denormalizePayload(array $payload)
    {
        return new TaxonUpdated($payload['code'], $payload['parent'], new Translations($payload['labels']));
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedMessageType(): string
    {
        return 'akeneo_category_updated';
    }
}
