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
     * @var string
     */
    private $nameAttribute;

    /**
     * @var string
     */
    private $descriptionAttribute;

    /**
     * @var string
     */
    private $priceAttribute;

    /**
     * @param string $nameAttribute
     * @param string $descriptionAttribute
     * @param string $priceAttribute
     */
    public function __construct($nameAttribute, $descriptionAttribute, $priceAttribute)
    {
        $this->nameAttribute = $nameAttribute;
        $this->descriptionAttribute = $descriptionAttribute;
        $this->priceAttribute = $priceAttribute;
    }

    /**
     * {@inheritdoc}
     */
    protected function denormalizePayload(array $payload)
    {
        return new ProductCreated(
            $payload['identifier'],
            $this->getName($payload),
            $this->getDescription($payload),
            $payload['enabled'],
            $payload['family'],
            $payload['categories'],
            $this->getPrices($payload),
            $payload['values'],
            $this->getAssociations($payload),
            \DateTime::createFromFormat(\DateTime::W3C, $payload['created'])
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedMessageType(): string
    {
        return MessageType::PRODUCT_CREATED_MESSAGE_TYPE;
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

    /**
     * @param array $payload
     *
     * @return mixed
     */
    private function getName(array $payload)
    {
        return $this->getAttribute($payload, 'name', $this->nameAttribute);
    }

    /**
     * @param array $payload
     *
     * @return mixed
     */
    private function getDescription(array $payload)
    {
        return $this->getAttribute($payload, 'description', $this->descriptionAttribute);
    }

    /**
     * @param array $payload
     *
     * @return mixed
     */
    private function getPrices(array $payload)
    {
        return $this->getAttribute($payload, 'price', $this->priceAttribute);
    }

    /**
     * @param array $payload
     * @param string $attributeName
     * @param string $attributeKey
     *
     * @return mixed
     */
    private function getAttribute(array $payload, $attributeName, $attributeKey)
    {
        if (!isset($payload['values'][$attributeKey][0]) || !array_key_exists('data', $payload['values'][$attributeKey][0])) {
            throw new \InvalidArgumentException(sprintf(
                'Cannot find %s attribute (used key: payload.%s.0.data). Payload: %s',
                $attributeName,
                $attributeKey,
                json_encode($payload)
            ));
        }

        return $payload['values'][$attributeKey][0]['data'];
    }
}
