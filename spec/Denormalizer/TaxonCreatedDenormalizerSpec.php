<?php

namespace spec\Sylake\SyliusConsumerPlugin\Denormalizer;

use PhpAmqpLib\Message\AMQPMessage;
use PhpSpec\ObjectBehavior;
use Sylake\SyliusConsumerPlugin\Event\TaxonCreated;
use SyliusLabs\RabbitMqSimpleBusBundle\Denormalizer\DenormalizationFailedException;
use SyliusLabs\RabbitMqSimpleBusBundle\Denormalizer\DenormalizerInterface;

final class TaxonCreatedDenormalizerSpec extends ObjectBehavior
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

        $messageWithTypeOnly = new AMQPMessage(json_encode(['type' => 'akeneo_category_created']));

        $this->supports($messageWithTypeOnly)->shouldReturn(false);
        $this->shouldThrow(DenormalizationFailedException::class)->during('denormalize', [$messageWithTypeOnly]);
    }

    function it_supports_messages_with_payload_and_specific_type_with_parent()
    {
        $supportedMessage = new AMQPMessage(json_encode([
            'type' => 'akeneo_category_created',
            'payload' => [
                'code' => 'SUBCATEGORY',
                'parent' => 'CATEGORY',
                'labels' => [
                    'en_US' => 'Subcategory',
                    'pl_PL' => 'Podkategoria',
                ],
            ],
        ]));

        $this->supports($supportedMessage)->shouldReturn(true);
        $this->denormalize($supportedMessage)->shouldBeLike(new TaxonCreated(
            'SUBCATEGORY',
            'CATEGORY',
            ['en_US' => 'Subcategory', 'pl_PL' => 'Podkategoria']
        ));
    }

    function it_supports_messages_with_payload_and_specific_type_without_parent()
    {
        $supportedMessage = new AMQPMessage(json_encode([
            'type' => 'akeneo_category_created',
            'payload' => [
                'code' => 'SUBCATEGORY',
                'parent' => null,
                'labels' => [
                    'en_US' => 'Subcategory',
                    'pl_PL' => 'Podkategoria',
                ],
            ],
        ]));

        $this->supports($supportedMessage)->shouldReturn(true);
        $this->denormalize($supportedMessage)->shouldBeLike(new TaxonCreated(
            'SUBCATEGORY',
            null,
            ['en_US' => 'Subcategory', 'pl_PL' => 'Podkategoria']
        ));
    }
}
