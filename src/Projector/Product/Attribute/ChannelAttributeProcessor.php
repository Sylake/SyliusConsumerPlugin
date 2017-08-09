<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Projector\Product\Attribute;

use Sylake\SyliusConsumerPlugin\Model\Attribute;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Currency\Model\CurrencyInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class ChannelAttributeProcessor implements AttributeProcessorInterface
{
    /** @var RepositoryInterface */
    private $channelRepository;

    /** @var RepositoryInterface */
    private $currencyRepository;

    /** @var string */
    private $priceAttribute;

    public function __construct(
        RepositoryInterface $channelRepository,
        RepositoryInterface $currencyRepository,
        string $priceAttribute
    ) {
        $this->channelRepository = $channelRepository;
        $this->currencyRepository = $currencyRepository;
        $this->priceAttribute = $priceAttribute;
    }

    /** {@inheritdoc} */
    public function process(ProductInterface $product, Attribute $attribute): array
    {
        if (!$this->supports($attribute)) {
            return [];
        }

        $currentChannels = $product->getChannels()->toArray();
        $processedChannels = $this->processChannels($attribute);

        $compareChannels = function (ChannelInterface $a, ChannelInterface $b): int {
            return $a->getId() <=> $b->getId();
        };

        $productChannelToAdd = array_udiff(
            $processedChannels,
            $currentChannels,
            $compareChannels
        );
        foreach ($productChannelToAdd as $productChannel) {
            $product->addChannel($productChannel);
        }

        $productChannelToRemove = array_udiff(
            $currentChannels,
            $processedChannels,
            $compareChannels
        );
        foreach ($productChannelToRemove as $productChannel) {
            $product->removeChannel($productChannel);
        }

        return [];
    }

    private function supports(Attribute $attribute): bool
    {
        return $this->priceAttribute === $attribute->attribute() && is_array($attribute->data());
    }

    private function processChannels(Attribute $attribute): array
    {
        /** @var ChannelInterface[] $channels */
        $channels = [];

        foreach ($attribute->data() as $price) {
            if (null === $price['amount']) {
                continue;
            }

            /** @var CurrencyInterface $currency */
            $currency = $this->currencyRepository->findOneBy(['code' => $price['currency']]);

            $channels = array_unique(array_merge(
                $channels,
                $this->channelRepository->findBy(['baseCurrency' => $currency])
            ));
        }

        return $channels;
    }
}
