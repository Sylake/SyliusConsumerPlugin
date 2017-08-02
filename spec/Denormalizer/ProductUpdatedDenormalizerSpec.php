<?php

declare(strict_types=1);

namespace spec\Sylake\SyliusConsumerPlugin\Denormalizer;

use PhpAmqpLib\Message\AMQPMessage;
use PhpSpec\ObjectBehavior;
use Sylake\SyliusConsumerPlugin\Event\ProductUpdated;
use SyliusLabs\RabbitMqSimpleBusBundle\Denormalizer\DenormalizationFailedException;
use SyliusLabs\RabbitMqSimpleBusBundle\Denormalizer\DenormalizerInterface;

final class ProductUpdatedDenormalizerSpec extends ObjectBehavior
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

        $messageWithTypeOnly = new AMQPMessage(json_encode(['type' => 'akeneo_product_updated']));

        $this->supports($messageWithTypeOnly)->shouldReturn(false);
        $this->shouldThrow(DenormalizationFailedException::class)->during('denormalize', [$messageWithTypeOnly]);
    }

    function it_supports_product_updated_messages()
    {
        $message = new AMQPMessage('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "description": [{"locale": "en_US", "scope": "mobile", "data": "T-Shirt description"}]
                },
                "created": "2017-04-18T12:30:45+02:30",
                "associations": {}
            }
        }');

        $this->supports($message)->shouldReturn(true);
        $this->denormalize($message)->shouldBeLike(new ProductUpdated(
            'AKNTS_BPXS',
            true,
            [],
            [
                'name' => [['locale' => null, 'scope' => null, 'data' => 'Akeneo T-Shirt black and purple with short sleeve']],
                'description' => [['locale' => 'en_US', 'scope' => 'mobile', 'data' => 'T-Shirt description']],
            ],
            [],
            \DateTime::createFromFormat(\DateTime::ATOM, '2017-04-18T12:30:45+02:30')
        ));
    }

    function it_does_not_support_messages_without_identifier()
    {
        $messageWithoutIdentifier = new AMQPMessage('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": null,
                "categories": [],
                "enabled": false,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}]
                },
                "created": "2017-04-18T12:45:45+02:30",
                "associations": {}
            }
        }');

        $this->supports($messageWithoutIdentifier)->shouldReturn(false);
        $this->shouldThrow(DenormalizationFailedException::class)->during('denormalize', [$messageWithoutIdentifier]);

        $messageWithoutIdentifier = new AMQPMessage('{
            "type": "akeneo_product_updated",
            "payload": {
                "categories": [],
                "enabled": false,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}]
                },
                "created": "2017-04-18T12:45:45+02:30",
                "associations": {}
            }
        }');

        $this->supports($messageWithoutIdentifier)->shouldReturn(false);
        $this->shouldThrow(DenormalizationFailedException::class)->during('denormalize', [$messageWithoutIdentifier]);
    }

    function it_ignores_extra_fields_passed_with_payload()
    {
        $messageWithExtraFields = new AMQPMessage('{
            "type": "akeneo_product_updated",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "categories": [],
                "enabled": true,
                "values": {
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "description": [{"locale": "en_US", "scope": "mobile", "data": "T-Shirt description"}]
                },
                "created": "2017-04-18T12:30:45+02:30",
                "associations": {},
                "extraField": {}
            }
        }');

        $this->supports($messageWithExtraFields)->shouldReturn(true);
        $this->denormalize($messageWithExtraFields)->shouldBeLike(new ProductUpdated(
            'AKNTS_BPXS',
            true,
            [],
            [
                'name' => [['locale' => null, 'scope' => null, 'data' => 'Akeneo T-Shirt black and purple with short sleeve']],
                'description' => [['locale' => 'en_US', 'scope' => 'mobile', 'data' => 'T-Shirt description']],
            ],
            [],
            \DateTime::createFromFormat(\DateTime::ATOM, '2017-04-18T12:30:45+02:30')
        ));
    }
}
