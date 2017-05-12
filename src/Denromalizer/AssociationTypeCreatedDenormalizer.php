<?php

namespace Sylake\RabbitmqAkeneo\Denormalizer;

use Sylake\RabbitmqAkeneo\Event\AssociationTypeCreated;
use PhpAmqpLib\Message\AMQPMessage;
use Sylius\RabbitMqSimpleBusBundle\Denormalizer\DenormalizerInterface;

final class AssociationTypeCreatedDenormalizer implements DenormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(AMQPMessage $message)
    {
        $decodedMessage = json_decode($message->getBody(), true);

        if (
            isset($decodedMessage['type']) &&
            MessageTypes::ASSOCIATION_TYPE_CREATED_MESSAGE_TYPE === $decodedMessage['type'] &&
            $message->get('content_type') === 'json'
        ) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize(AMQPMessage $message)
    {
        $decodedMessage = json_decode($message->getBody(), true);

        return new AssociationTypeCreated($decodedMessage['code'], $decodedMessage['labels']);
    }
}
