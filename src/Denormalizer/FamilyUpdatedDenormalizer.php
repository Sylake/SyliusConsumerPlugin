<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Denormalizer;

use Sylake\SyliusConsumerPlugin\Event\FamilyUpdated;
use Sylake\SyliusConsumerPlugin\Model\Translations;

final class FamilyUpdatedDenormalizer extends AkeneoDenormalizer
{
    /**
     * {@inheritdoc}
     */
    protected function denormalizePayload(array $payload)
    {
        return new FamilyUpdated($payload['code'], new Translations($payload['labels']));
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedMessageType(): string
    {
        return MessageType::FAMILY_UPDATED_MESSAGE_TYPE;
    }
}
