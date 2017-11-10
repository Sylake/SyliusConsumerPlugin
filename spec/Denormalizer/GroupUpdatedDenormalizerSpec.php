<?php

declare(strict_types=1);

namespace spec\Sylake\SyliusConsumerPlugin\Denormalizer;

use PhpAmqpLib\Message\AMQPMessage;
use PhpSpec\ObjectBehavior;
use Sylake\SyliusConsumerPlugin\Event\GroupUpdated;
use Sylake\SyliusConsumerPlugin\Model\Translations;
use SyliusLabs\RabbitMqSimpleBusBundle\Denormalizer\DenormalizationFailedException;
use SyliusLabs\RabbitMqSimpleBusBundle\Denormalizer\DenormalizerInterface;

final class GroupUpdatedDenormalizerSpec extends ObjectBehavior
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

        $messageWithTypeOnly = new AMQPMessage(json_encode(['type' => 'akeneo_group_updated']));

        $this->supports($messageWithTypeOnly)->shouldReturn(false);
        $this->shouldThrow(DenormalizationFailedException::class)->during('denormalize', [$messageWithTypeOnly]);
    }

    function it_supports_messages_with_payload_and_specific_type()
    {
        $supportedMessage = new AMQPMessage(json_encode([
            'type' => 'akeneo_group_updated',
            'payload' => [
                'code' => 'GROUP',
                'labels' => [
                    'en_US' => 'Group',
                    'pl_PL' => 'Grupa',
                ],
            ],
        ]));

        $this->supports($supportedMessage)->shouldReturn(true);
        $this->denormalize($supportedMessage)->shouldBeLike(new GroupUpdated(
            'GROUP',
            new Translations(['en_US' => 'Group', 'pl_PL' => 'Grupa'])
        ));
    }
}
