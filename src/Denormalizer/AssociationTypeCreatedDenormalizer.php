<?php

namespace Sylake\SyliusConsumerPlugin\Denormalizer;

use Sylake\SyliusConsumerPlugin\Event\AssociationTypeCreated;
use PhpAmqpLib\Message\AMQPMessage;
use SyliusLabs\RabbitMqSimpleBusBundle\Denormalizer\DenormalizerInterface;

final class AssociationTypeCreatedDenormalizer implements DenormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(AMQPMessage $message)
    {
        $body = json_decode($message->getBody(), true);

        return isset($body['type'], $body['payload']) && MessageType::ASSOCIATION_TYPE_CREATED_MESSAGE_TYPE === $body['type'];
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize(AMQPMessage $message)
    {
        $payload = json_decode($message->getBody(), true)['payload'];

        return new AssociationTypeCreated($payload['code'], $payload['labels']);
    }
}
