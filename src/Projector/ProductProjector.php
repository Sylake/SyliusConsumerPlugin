<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Projector;

use Sylake\SyliusConsumerPlugin\Event\ProductCreated;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Currency\Model\CurrencyInterface;
use Sylius\Component\Product\Factory\ProductFactoryInterface;
use Sylius\Component\Product\Generator\SlugGeneratorInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class ProductProjector
{
    /**
     * @var ProductFactoryInterface
     */
    private $productFactory;

    /**
     * @var FactoryInterface
     */
    private $productTaxonFactory;

    /**
     * @var SlugGeneratorInterface
     */
    private $slugGenerator;

    /**
     * @var RepositoryInterface
     */
    private $channelPricingRepository;

    /**
     * @var RepositoryInterface
     */
    private $productRepository;

    /**
     * @var RepositoryInterface
     */
    private $productTaxonRepository;

    /**
     * @var RepositoryInterface
     */
    private $taxonRepository;

    /**
     * @var RepositoryInterface
     */
    private $currencyRepository;

    /**
     * @var RepositoryInterface
     */
    private $channelRepository;

    /**
     * @var FactoryInterface
     */
    private $channelPricingFactory;

    /**
     * @param ProductFactoryInterface $productFactory
     * @param FactoryInterface $productTaxonFactory
     * @param FactoryInterface $channelPricingFactory
     * @param SlugGeneratorInterface $slugGenerator
     * @param RepositoryInterface $productRepository
     * @param RepositoryInterface $productTaxonRepository
     * @param RepositoryInterface $taxonRepository
     * @param RepositoryInterface $currencyRepository
     * @param RepositoryInterface $channelRepository
     * @param RepositoryInterface $channelPricingRepository
     */
    public function __construct(
        ProductFactoryInterface $productFactory,
        FactoryInterface $productTaxonFactory,
        FactoryInterface $channelPricingFactory,
        SlugGeneratorInterface $slugGenerator,
        RepositoryInterface $productRepository,
        RepositoryInterface $productTaxonRepository,
        RepositoryInterface $taxonRepository,
        RepositoryInterface $currencyRepository,
        RepositoryInterface $channelRepository,
        RepositoryInterface $channelPricingRepository
    ) {
        $this->productFactory = $productFactory;
        $this->productTaxonFactory = $productTaxonFactory;
        $this->channelPricingFactory = $channelPricingFactory;
        $this->slugGenerator = $slugGenerator;
        $this->productRepository = $productRepository;
        $this->productTaxonRepository = $productTaxonRepository;
        $this->taxonRepository = $taxonRepository;
        $this->currencyRepository = $currencyRepository;
        $this->channelRepository = $channelRepository;
        $this->channelPricingRepository = $channelPricingRepository;
    }

    /**
     * @param ProductCreated $event
     */
    public function handleProductCreated(ProductCreated $event)
    {
        $product = $this->provideProduct($event->code());
        $productVariant = $this->provideProductVariant($event->code(), $product);

        $this->handleNameAndDescription($event->name(), $event->description(), $product);
        $this->handleEnabled($event->enabled(), $product);
        $this->handleChannels($event->prices(), $product);
        $this->handleChannelPricings($event->prices(), $productVariant);
        $this->handleMainTaxon($event->mainTaxon(), $product);
        $this->handleProductTaxons($event->taxons(), $product);
        $this->handleCreatedAt($event->createdAt(), $product, $productVariant);

        $this->productRepository->add($product);
    }

    /**
     * @param string $name
     * @param string $description
     * @param ProductInterface $product
     */
    private function handleNameAndDescription($name, $description, ProductInterface $product)
    {
        $product->setName($name ?: $product->getCode());
        $product->setSlug($this->slugGenerator->generate($product->getCode() . ($name ? ' ' . $name : '')));
        $product->setDescription($description);
    }

    /**
     * @param bool $enabled
     * @param ProductInterface $product
     */
    private function handleEnabled($enabled, ProductInterface $product)
    {
        $product->setEnabled($enabled);
    }

    /**
     * @param array $prices
     * @param ProductInterface $product
     */
    private function handleChannels(array $prices, ProductInterface $product)
    {
        foreach ($product->getChannels() as $channel) {
            $product->removeChannel($channel);
        }

        /** @var ChannelInterface[] $channels */
        $channels = [];
        foreach ($prices as $price) {
            /** @var CurrencyInterface $currency */
            $currency = $this->currencyRepository->findOneBy(['code' => $price['currency']]);

            $channels = array_unique(array_merge(
                $channels,
                $this->channelRepository->findBy(['baseCurrency' => $currency])
            ));
        }

        foreach ($channels as $channel) {
            $product->addChannel($channel);
        }
    }

    /**
     * @param array $prices
     * @param ProductVariantInterface $productVariant
     */
    private function handleChannelPricings(array $prices, ProductVariantInterface $productVariant)
    {
        foreach ($productVariant->getChannelPricings() as $channelPricing) {
            $productVariant->removeChannelPricing($channelPricing);
        }

        foreach ($prices as $price) {
            /** @var CurrencyInterface $currency */
            $currency = $this->currencyRepository->findOneBy(['code' => $price['currency']]);

            /** @var ChannelInterface[] $channels */
            $channels = $this->channelRepository->findBy(['baseCurrency' => $currency]);

            foreach ($channels as $channel) {
                /** @var ChannelPricingInterface|null $channelPricing */
                $channelPricing = $this->channelPricingRepository->findOneBy(['productVariant' => $productVariant, 'channelCode' => $channel->getCode()]);

                if (null === $channelPricing) {
                    $channelPricing = $this->channelPricingFactory->createNew();
                    $channelPricing->setChannelCode($channel->getCode());
                    $channelPricing->setProductVariant($productVariant);
                }

                $channelPricing->setPrice($price['amount'] * 100);

                $productVariant->addChannelPricing($channelPricing);
            }
        }
    }

    /**
     * @param string|null $mainTaxonCode
     * @param ProductInterface $product
     */
    private function handleMainTaxon($mainTaxonCode, ProductInterface $product)
    {
        $mainTaxon = $this->taxonRepository->findOneBy(['code' => $mainTaxonCode]);

        $product->setMainTaxon($mainTaxon);
    }

    /**
     * @param array $taxonCodes
     * @param ProductInterface $product
     */
    private function handleProductTaxons(array $taxonCodes, ProductInterface $product)
    {
        foreach ($product->getProductTaxons() as $productTaxon) {
            $product->removeProductTaxon($productTaxon);
        }

        foreach ($taxonCodes as $taxonCode) {
            /** @var TaxonInterface $taxon */
            $taxon = $this->taxonRepository->findOneBy(['code' => $taxonCode]);

            $productTaxon = $this->provideProductTaxon($product, $taxon);

            $product->addProductTaxon($productTaxon);
        }
    }

    /**
     * @param \DateTime $createdAt
     * @param ProductInterface $product
     * @param ProductVariantInterface $productVariant
     */
    private function handleCreatedAt(\DateTime $createdAt, ProductInterface $product, ProductVariantInterface $productVariant)
    {
        $product->setCreatedAt($createdAt);
        $productVariant->setCreatedAt($createdAt);
    }

    /**
     * @param string $code
     *
     * @return ProductInterface
     */
    private function provideProduct($code)
    {
        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => $code]);

        if (null === $product) {
            $product = $this->productFactory->createWithVariant();
            $product->setCode($code);
        }

        return $product;
    }

    /**
     * @param string $code
     * @param ProductInterface $product
     *
     * @return ProductVariantInterface
     */
    private function provideProductVariant($code, ProductInterface $product)
    {
        /** @var ProductVariantInterface $productVariant */
        $productVariant = current($product->getVariants()->slice(0, 1));
        $productVariant->setCode($code);

        return $productVariant;
    }

    /**
     * @param ProductInterface $product
     * @param TaxonInterface $taxon
     *
     * @return ProductTaxonInterface
     */
    private function provideProductTaxon(ProductInterface $product, $taxon)
    {
        $productTaxon = $this->productTaxonRepository->findOneBy(['product' => $product, 'taxon' => $taxon]);

        if (null === $productTaxon) {
            /** @var ProductTaxonInterface $productTaxon */
            $productTaxon = $this->productTaxonFactory->createNew();
            $productTaxon->setTaxon($taxon);
            $productTaxon->setProduct($product);
        }

        return $productTaxon;
    }
}
