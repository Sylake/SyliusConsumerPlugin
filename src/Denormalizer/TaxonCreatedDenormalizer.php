<?php

namespace Sylake\SyliusConsumerPlugin\Denormalizer;

use Sylake\SyliusConsumerPlugin\Event\TaxonCreated;

final class TaxonCreatedDenormalizer extends AkeneoDenormalizer
{
    /**
     * {@inheritdoc}
     */
    protected function denormalizePayload(array $payload)
    {
        return new TaxonCreated($payload['code'], $payload['parent'], $payload['labels']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedMessageType()
    {
        return MessageType::CATEGORY_CREATED_MESSAGE_TYPE;
    }
}
