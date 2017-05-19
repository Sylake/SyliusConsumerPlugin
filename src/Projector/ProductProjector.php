<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Projector;

use Sylake\SyliusConsumerPlugin\Event\ProductCreated;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Product\Factory\ProductFactoryInterface;
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
     * @param ProductFactoryInterface $productFactory
     * @param FactoryInterface $productTaxonFactory
     * @param RepositoryInterface $productRepository
     * @param RepositoryInterface $productTaxonRepository
     * @param RepositoryInterface $taxonRepository
     */
    public function __construct(
        ProductFactoryInterface $productFactory,
        FactoryInterface $productTaxonFactory,
        RepositoryInterface $productRepository,
        RepositoryInterface $productTaxonRepository,
        RepositoryInterface $taxonRepository
    ) {
        $this->productFactory = $productFactory;
        $this->productTaxonFactory = $productTaxonFactory;
        $this->productRepository = $productRepository;
        $this->productTaxonRepository = $productTaxonRepository;
        $this->taxonRepository = $taxonRepository;
    }

    /**
     * @param ProductCreated $event
     */
    public function handleProductCreated(ProductCreated $event)
    {
        $product = $this->provideProduct($event->code());
        $productVariant = $this->provideProductVariant($event->code(), $product);

        $product->setCreatedAt($event->createdAt());
        $productVariant->setCreatedAt($event->createdAt());

        $this->handleProductTaxons($event->taxons(), $product);

        $this->productRepository->add($product);
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
