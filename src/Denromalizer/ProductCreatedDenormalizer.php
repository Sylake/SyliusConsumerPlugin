<?php

declare(strict_types=1);

namespace Sylake\RabbitmqAkeneo\Denormalizer;

use PhpAmqpLib\Message\AMQPMessage;
use Sylake\RabbitmqAkeneo\Event\ProductCreated;
use Sylius\RabbitMqSimpleBusBundle\Denormalizer\DenormalizerInterface;

final class ProductCreatedDenormalizer implements DenormalizerInterface
{
    public function supports(AMQPMessage $message)
    {
        $decodedMessage = $this->denormalize($message);

        if (
            isset($decodedMessage['type']) &&
            MessageTypes::PRODUCT_CREATED_MESSAGE_TYPE === $decodedMessage['type'] &&
            $message->get('content_type') === 'json'
        ) {
            return true;
        }

        return false;
    }

    public function denormalize(AMQPMessage $message)
    {
        $message = json_decode($message->getBody(), true);

        unset($message['values']['description']);
        unset($message['values']['sku']);

        return new ProductCreated(
            $message['identifier'],
            $message['categories'],
            \DateTime::createFromFormat(\DateTime::W3C, $message['created']),
            $message['association'],
            $message['price'],
            $message['values'],
            $message['description']
        );
    }
}
