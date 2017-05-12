<?php

declare(strict_types=1);

namespace Sylake\RabbitmqAkeneo\Projector;

use Sylake\RabbitmqAkeneo\Event\ProductCreated;
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
     * @param TaxonFactoryInterface $taxonFactory
     * @param FactoryInterface $associationFactory
     * @param FactoryInterface $channelPricingFactory
     * @param FactoryInterface $productAttributeFactory
     * @param ProductFactoryInterface $productFactory
     * @param TaxonRepositoryInterface $taxonRepository
     * @param RepositoryInterface $associationRepository
     * @param RepositoryInterface $channelPricingRepository
     * @param RepositoryInterface $productAttributeRepository
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        TaxonFactoryInterface $taxonFactory,
        FactoryInterface $associationFactory,
        FactoryInterface $channelPricingFactory,
        FactoryInterface $productAttributeFactory,
        ProductFactoryInterface $productFactory,
        TaxonRepositoryInterface $taxonRepository,
        RepositoryInterface $associationRepository,
        RepositoryInterface $channelPricingRepository,
        RepositoryInterface $productAttributeRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->taxonFactory = $taxonFactory;
        $this->associationFactory = $associationFactory;
        $this->channelPricingFactory = $channelPricingFactory;
        $this->productAttributeFactory = $productAttributeFactory;
        $this->productFactory = $productFactory;
        $this->taxonRepository = $taxonRepository;
        $this->associationRepository = $associationRepository;
        $this->channelPricingRepository = $channelPricingRepository;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * @param ProductCreated $event
     */
    public function handleProductCreated(ProductCreated $event)
    {
        /** @var ProductInterface $product */
        $product = $this->productFactory->createWithVariant();

//        $this->productRepository->add($product);
    }
}
