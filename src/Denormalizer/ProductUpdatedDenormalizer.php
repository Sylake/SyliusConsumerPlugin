<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Denormalizer;

use Sylake\SyliusConsumerPlugin\Event\ProductUpdated;

final class ProductUpdatedDenormalizer extends AkeneoDenormalizer
{
    /**
     * {@inheritdoc}
     */
    protected function denormalizePayload(array $payload): ProductUpdated
    {
        return new ProductUpdated(
            $payload['identifier'],
            $payload['enabled'],
            $payload['categories'],
            $payload['values'],
            $this->getAssociations($payload),
            \DateTime::createFromFormat(\DateTime::W3C, $payload['created']) ?: null
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedMessageType(): string
    {
        return MessageType::PRODUCT_CREATED_MESSAGE_TYPE;
    }

    private function getAssociations(array $payload): array
    {
        $associations = [];
        foreach ($payload['associations'] as $associationTypeCode => $value) {
            $associations[$associationTypeCode] = $value['products'];
        }

        return $associations;
    }
}
