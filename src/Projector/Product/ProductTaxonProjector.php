<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Projector\Product;

use Sylake\SyliusConsumerPlugin\Event\ProductUpdated;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class ProductTaxonProjector
{
    /** @var FactoryInterface */
    private $productTaxonFactory;

    /** @var RepositoryInterface */
    private $productTaxonRepository;

    /** @var RepositoryInterface */
    private $taxonRepository;

    public function __construct(
        FactoryInterface $productTaxonFactory,
        RepositoryInterface $productTaxonRepository,
        RepositoryInterface $taxonRepository
    ) {
        $this->productTaxonFactory = $productTaxonFactory;
        $this->productTaxonRepository = $productTaxonRepository;
        $this->taxonRepository = $taxonRepository;
    }

    public function __invoke(ProductUpdated $event, ProductInterface $product): void
    {
        $this->handleMainTaxon($event->taxons(), $product);
        $this->handleProductTaxons($event->taxons(), $product);
    }

    /**
     * Set main taxon to be the first most specific taxon.
     */
    private function handleMainTaxon(array $taxonCodes, ProductInterface $product): void
    {
        $mainTaxonCode = array_reduce($taxonCodes, function (string $mainTaxonCode, string $taxonCode): string {
            return 0 === strpos($taxonCode, $mainTaxonCode) ? $taxonCode : $mainTaxonCode;
        }, current($taxonCodes));

        $mainTaxon = $this->taxonRepository->findOneBy(['code' => $mainTaxonCode]);

        $product->setMainTaxon($mainTaxon);
    }

    private function handleProductTaxons(array $taxonCodes, ProductInterface $product): void
    {
        $currentProductTaxons = $product->getProductTaxons()->toArray();
        $processedProductTaxons = $this->processProductTaxons($taxonCodes, $product);

        $productTaxonToAdd = ResourceUtil::diff($processedProductTaxons, $currentProductTaxons);
        foreach ($productTaxonToAdd as $productTaxon) {
            $product->addProductTaxon($productTaxon);
        }

        $productTaxonToRemove = ResourceUtil::diff($currentProductTaxons, $processedProductTaxons);
        foreach ($productTaxonToRemove as $productTaxon) {
            $product->removeProductTaxon($productTaxon);
        }
    }

    private function processProductTaxons(array $taxonCodes, ProductInterface $product): array
    {
        /** @var ProductTaxonInterface[] $productTaxons */
        $productTaxons = [];

        foreach ($taxonCodes as $taxonCode) {
            /** @var TaxonInterface $taxon */
            $taxon = $this->taxonRepository->findOneBy(['code' => $taxonCode]);

            if (null === $taxon) {
                continue;
            }

            $productTaxons[] = $this->provideProductTaxon($product, $taxon);
        }

        return $productTaxons;
    }

    private function provideProductTaxon(ProductInterface $product, TaxonInterface $taxon): ProductTaxonInterface
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
