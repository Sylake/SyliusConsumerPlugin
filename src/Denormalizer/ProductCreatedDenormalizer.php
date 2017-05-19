<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Denormalizer;

use PhpAmqpLib\Message\AMQPMessage;
use Sylake\SyliusConsumerPlugin\Event\ProductCreated;
use SyliusLabs\RabbitMqSimpleBusBundle\Denormalizer\DenormalizationFailedException;
use SyliusLabs\RabbitMqSimpleBusBundle\Denormalizer\DenormalizerInterface;

final class ProductCreatedDenormalizer extends AkeneoDenormalizer
{
    /**
     * {@inheritdoc}
     */
    protected function denormalizePayload(array $payload)
    {
        return new ProductCreated(
            $payload['identifier'],
            $payload['values']['name'][0]['data'],
            $payload['values']['description'][0]['data'],
            $payload['enabled'],
            $payload['family'],
            $payload['categories'],
            $payload['values']['price'][0]['data'],
            $this->getAttributes($payload),
            $this->getAssociations($payload),
            \DateTime::createFromFormat(\DateTime::W3C, $payload['created'])
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedMessageType()
    {
        return MessageType::PRODUCT_CREATED_MESSAGE_TYPE;
    }

    /**
     * @param array $payload
     *
     * @return array
     */
    private function getAttributes(array $payload)
    {
        $attributes = [];
        foreach ($payload['values'] as $attributeCode => $value) {
            $value = $value[0]['data'];

            if (is_array($value)) {
                $hasNestedArrays = !array_reduce($value, function ($acc, $value) {
                    return $acc && !is_array($value);
                }, true);

                if ($hasNestedArrays) {
                    continue;
                }

                $value = implode(', ', $value);
            }

            $attributes[$attributeCode] = $value;
        }

        return $attributes;
    }

    /**
     * @param array $payload
     *
     * @return array
     */
    private function getAssociations(array $payload)
    {
        $associations = [];
        foreach ($payload['associations'] as $associationTypeCode => $value) {
            $associations[$associationTypeCode] = $value['products'];
        }

        return $associations;
    }
}
