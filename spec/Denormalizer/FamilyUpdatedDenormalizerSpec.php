<?php

namespace spec\Sylake\SyliusConsumerPlugin\Denormalizer;

use PhpAmqpLib\Message\AMQPMessage;
use PhpSpec\ObjectBehavior;
use Sylake\SyliusConsumerPlugin\Event\FamilyUpdated;
use Sylake\SyliusConsumerPlugin\Model\Translations;
use SyliusLabs\RabbitMqSimpleBusBundle\Denormalizer\DenormalizationFailedException;
use SyliusLabs\RabbitMqSimpleBusBundle\Denormalizer\DenormalizerInterface;

final class FamilyUpdatedDenormalizerSpec extends ObjectBehavior
{
    function it_is_a_denormalizer()
    {
        $this->shouldImplement(DenormalizerInterface::class);
    }

    function it_does_not_support_messages_with_invalid_body()
    {
        $invalidMessage = new AMQPMessage('invalid JSON');

        $this->supports($invalidMessage)->shouldReturn(false);
        $this->shouldThrow(DenormalizationFailedException::class)->during('denormalize', [$invalidMessage]);
    }

    function it_does_not_support_messages_without_payload_or_type()
    {
        $messageWithPayloadOnly = new AMQPMessage(json_encode(['payload' => []]));

        $this->supports($messageWithPayloadOnly)->shouldReturn(false);
        $this->shouldThrow(DenormalizationFailedException::class)->during('denormalize', [$messageWithPayloadOnly]);

        $messageWithTypeOnly = new AMQPMessage(json_encode(['type' => 'akeneo_family_updated']));

        $this->supports($messageWithTypeOnly)->shouldReturn(false);
        $this->shouldThrow(DenormalizationFailedException::class)->during('denormalize', [$messageWithTypeOnly]);
    }

    function it_supports_messages_with_payload_and_specific_type()
    {
        $supportedMessage = new AMQPMessage(json_encode([
            'type' => 'akeneo_family_updated',
            'payload' => [
                'code' => 'FAMILY',
                'labels' => [
                    'en_US' => 'Family',
                    'pl_PL' => 'Rodzina',
                ],
            ],
        ]));

        $this->supports($supportedMessage)->shouldReturn(true);
        $this->denormalize($supportedMessage)->shouldBeLike(new FamilyUpdated(
            'FAMILY',
            new Translations(['en_US' => 'Family', 'pl_PL' => 'Rodzina'])
        ));
    }
}
