<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Projector;

use Sylake\SyliusConsumerPlugin\Event\ProductCreated;
use Sylius\Component\Channel\Factory\ChannelFactoryInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Product\Factory\ProductFactoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Taxonomy\Factory\TaxonFactoryInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;

final class ProductProjector
{
    /**
     * @var TaxonFactoryInterface
     */
    private $taxonFactory;

    /**
     * @var FactoryInterface
     */
    private $associationFactory;

    /**
     * @var FactoryInterface
     */
    private $channelPricingFactory;

    /**
     * @var FactoryInterface
     */
    private $productAttributeFactory;

    /**
     * @var ProductFactoryInterface
     */
    private $productFactory;

    /**
     * @var ChannelFactoryInterface
     */
    private $channelFactory;

    /**
     * @var TaxonRepositoryInterface
     */
    private $taxonRepository;

    /**
     * @var RepositoryInterface
     */
    private $associationRepository;

    /**
     * @var RepositoryInterface
     */
    private $channelPricingRepository;

    /**
     * @var RepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ChannelRepositoryInterface
     */
    private $channelRepository;

    /**
     * @param TaxonFactoryInterface $taxonFactory
     * @param FactoryInterface $associationFactory
     * @param FactoryInterface $channelPricingFactory
     * @param FactoryInterface $productAttributeFactory
     * @param ProductFactoryInterface $productFactory
     * @param ChannelFactoryInterface $channelFactory
     * @param TaxonRepositoryInterface $taxonRepository
     * @param RepositoryInterface $associationRepository
     * @param RepositoryInterface $channelPricingRepository
     * @param RepositoryInterface $productAttributeRepository
     * @param ProductRepositoryInterface $productRepository
     * @param ChannelRepositoryInterface $channelRepository
     */
    public function __construct(
        TaxonFactoryInterface $taxonFactory,
        FactoryInterface $associationFactory,
        FactoryInterface $channelPricingFactory,
        FactoryInterface $productAttributeFactory,
        ProductFactoryInterface $productFactory,
        ChannelFactoryInterface $channelFactory,
        TaxonRepositoryInterface $taxonRepository,
        RepositoryInterface $associationRepository,
        RepositoryInterface $channelPricingRepository,
        RepositoryInterface $productAttributeRepository,
        ProductRepositoryInterface $productRepository,
        ChannelRepositoryInterface $channelRepository
    ) {
        $this->taxonFactory = $taxonFactory;
        $this->associationFactory = $associationFactory;
        $this->channelPricingFactory = $channelPricingFactory;
        $this->productAttributeFactory = $productAttributeFactory;
        $this->productFactory = $productFactory;
        $this->channelFactory = $channelFactory;
        $this->taxonRepository = $taxonRepository;
        $this->associationRepository = $associationRepository;
        $this->channelPricingRepository = $channelPricingRepository;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->productRepository = $productRepository;
        $this->channelRepository = $channelRepository;
    }

    /**
     * @param ProductCreated $event
     */
    public function handleProductCreated(ProductCreated $event)
    {
        /** @var ProductInterface $product */
        $product = $this->productFactory->createWithVariant();

        /** @var ChannelPricingInterface $channelPrice */
        $channelPrice = $this->channelPricingFactory->createNew();

        foreach ($event->price() as $priceInformation) {
            $channelPrice->setChannelCode($priceInformation['scope']);
            $channelPrice->setPrice($priceInformation['data'][0]['amount']);
        }

        foreach ($event->description() as $locale => $description) {
            $product->setCurrentLocale($locale);
            $product->setDescription($description);
        }

        $this->productRepository->add($product);
    }
}
