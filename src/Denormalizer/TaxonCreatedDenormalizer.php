<?php

namespace Sylake\SyliusConsumerPlugin\Denormalizer;

use Sylake\SyliusConsumerPlugin\Event\TaxonCreated;
use Sylake\SyliusConsumerPlugin\Model\Translations;

final class TaxonCreatedDenormalizer extends AkeneoDenormalizer
{
    /**
     * {@inheritdoc}
     */
    protected function denormalizePayload(array $payload)
    {
        return new TaxonCreated($payload['code'], $payload['parent'], new Translations($payload['labels']));
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedMessageType(): string
    {
        return MessageType::CATEGORY_CREATED_MESSAGE_TYPE;
    }
}
