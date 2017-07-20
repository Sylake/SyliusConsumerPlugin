<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Attribute;

use Sylake\SyliusConsumerPlugin\Model\Attribute;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Currency\Model\CurrencyInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class ChannelPricingAttributeProcessor implements AttributeProcessorInterface
{
    /** @var FactoryInterface */
    private $channelPricingFactory;

    /** @var RepositoryInterface */
    private $channelRepository;

    /** @var RepositoryInterface */
    private $currencyRepository;

    /** @var RepositoryInterface */
    private $channelPricingRepository;

    /** @var string */
    private $priceAttribute;

    public function __construct(
        FactoryInterface $channelPricingFactory,
        RepositoryInterface $channelRepository,
        RepositoryInterface $currencyRepository,
        RepositoryInterface $channelPricingRepository,
        string $priceAttribute
    ) {
        $this->channelPricingFactory = $channelPricingFactory;
        $this->channelRepository = $channelRepository;
        $this->currencyRepository = $currencyRepository;
        $this->channelPricingRepository = $channelPricingRepository;
        $this->priceAttribute = $priceAttribute;
    }

    /** {@inheritdoc} */
    public function process(ProductInterface $product, Attribute $attribute): array
    {
        if (!$this->supports($attribute)) {
            return [];
        }

        /** @var ProductVariantInterface $productVariant */
        $productVariant = current($product->getVariants()->slice(0, 1));

        foreach ($productVariant->getChannelPricings() as $channelPricing) {
            $productVariant->removeChannelPricing($channelPricing);
        }

        foreach ($attribute->data() as $price) {
            if (null === $price['amount']) {
                continue;
            }

            /** @var CurrencyInterface $currency */
            $currency = $this->currencyRepository->findOneBy(['code' => $price['currency']]);

            /** @var ChannelInterface[] $channels */
            $channels = $this->channelRepository->findBy(['baseCurrency' => $currency]);

            foreach ($channels as $channel) {
                /** @var ChannelPricingInterface|null $channelPricing */
                $channelPricing = $this->channelPricingRepository->findOneBy([
                    'productVariant' => $productVariant,
                    'channelCode' => $channel->getCode()
                ]);

                if (null === $channelPricing) {
                    $channelPricing = $this->channelPricingFactory->createNew();
                    $channelPricing->setChannelCode($channel->getCode());
                    $channelPricing->setProductVariant($productVariant);
                }

                $channelPricing->setPrice($price['amount'] * 100);

                $productVariant->addChannelPricing($channelPricing);
            }
        }

        return [];
    }

    private function supports(Attribute $attribute): bool
    {
        return $this->priceAttribute === $attribute->attribute() && is_array($attribute->data());
    }

}
