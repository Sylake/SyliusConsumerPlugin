<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Denormalizer;

use Sylake\SyliusConsumerPlugin\Event\ProductUpdated;
use SyliusLabs\RabbitMqSimpleBusBundle\Denormalizer\DenormalizationFailedException;

final class ProductUpdatedDenormalizer extends AkeneoDenormalizer
{

    /**
     * {@inheritdoc}
     */
    protected function denormalizePayload(array $payload): ProductUpdated
    {
        if (!array_key_exists('identifier', $payload) || !is_string($payload['identifier'])) {
            throw new DenormalizationFailedException('Identifier should be a string.');
        }

        $payload['enabled'] = $payload['enabled'] ?? false;
        if (!is_bool($payload['enabled'])) {
            throw new DenormalizationFailedException('Enabled should be a boolean.');
        }

        $payload['categories'] = $payload['categories'] ?? [];
        if (!is_array($payload['categories'])) {
            throw new DenormalizationFailedException('Categories should be an array.');
        }

        $payload['values'] = $payload['values'] ?? [];
        if (!is_array($payload['values'])) {
            throw new DenormalizationFailedException('Values should be an array.');
        }

        $payload['associations'] = $payload['associations'] ?? [];
        if (!is_array($payload['associations'])) {
            throw new DenormalizationFailedException('Associations should be an array.');
        }

        $payload['created'] = $payload['created'] ?? null;
        if (null !== $payload['created'] && !is_string($payload['created'])) {
            throw new DenormalizationFailedException('Created should be a string or null.');
        }

        return new ProductUpdated(
            $payload['identifier'],
            $payload['enabled'],
            $payload['categories'],
            $payload['values'],
            $this->getAssociations($payload),
            $payload['family'] ?? '',
            array_values($payload['groups'] ?? []),
            \DateTime::createFromFormat(\DateTime::ATOM, $payload['created']) ?: null
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedMessageType(): string
    {
        return 'akeneo_product_updated';
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
