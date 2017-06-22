<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Denormalizer;

use Sylake\SyliusConsumerPlugin\Event\AttributeOptionCreated;
use Sylake\SyliusConsumerPlugin\Model\Translations;

final class AttributeOptionCreatedDenormalizer extends AkeneoDenormalizer
{
    /**
     * {@inheritdoc}
     */
    protected function denormalizePayload(array $payload)
    {
        return new AttributeOptionCreated($payload['code'], $payload['attribute'], new Translations($payload['labels']));
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedMessageType(): string
    {
        return MessageType::ATTRIBUTE_OPTION_CREATED_MESSAGE_TYPE;
    }
}
