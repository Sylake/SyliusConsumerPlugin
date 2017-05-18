<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Denormalizer;

use PhpAmqpLib\Message\AMQPMessage;
use Sylake\SyliusConsumerPlugin\Event\ProductCreated;
use SyliusLabs\RabbitMqSimpleBusBundle\Denormalizer\DenormalizerInterface;

final class ProductCreatedDenormalizer implements DenormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(AMQPMessage $message)
    {
        $body = json_decode($message->getBody(), true);

        return isset($body['type'], $body['payload']) && MessageType::PRODUCT_CREATED_MESSAGE_TYPE === $body['type'];
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize(AMQPMessage $message)
    {
        $payload = json_decode($message->getBody(), true)['payload'];

        unset($payload['values']['description']);
        unset($payload['values']['sku']);

        return new ProductCreated(
            $payload['identifier'],
            $payload['categories'],
            \DateTime::createFromFormat(\DateTime::W3C, $payload['created']),
            $payload['associations'],
            $payload['price'],
            $payload['values'],
            $payload['description']
        );
    }
}
