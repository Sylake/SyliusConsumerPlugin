<?php

namespace Sylake\SyliusConsumerPlugin\Denormalizer;

use Sylake\SyliusConsumerPlugin\Event\AttributeCreated;
use Sylake\SyliusConsumerPlugin\Model\Translations;

final class AttributeCreatedDenormalizer extends AkeneoDenormalizer
{
    /**
     * {@inheritdoc}
     */
    protected function denormalizePayload(array $payload)
    {
        return new AttributeCreated($payload['code'], new Translations($payload['labels']));
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedMessageType(): string
    {
        return MessageType::ATTRIBUTE_CREATED_MESSAGE_TYPE;
    }
}
