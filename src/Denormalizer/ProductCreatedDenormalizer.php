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
            $payload['categories'],
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
}
