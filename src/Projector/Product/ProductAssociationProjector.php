<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Projector\Product;

use Doctrine\Common\Collections\Collection;
use Sylake\SyliusConsumerPlugin\Event\ProductUpdated;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Product\Model\ProductAssociationInterface;
use Sylius\Component\Product\Model\ProductAssociationTypeInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class ProductAssociationProjector
{
    /** @var FactoryInterface */
    private $associationFactory;

    /** @var RepositoryInterface */
    private $productRepository;

    /** @var RepositoryInterface */
    private $associationTypeRepository;

    /** @var RepositoryInterface */
    private $associationRepository;

    public function __construct(
        FactoryInterface $associationFactory,
        RepositoryInterface $productRepository,
        RepositoryInterface $associationTypeRepository,
        RepositoryInterface $associationRepository
    ) {
        $this->associationFactory = $associationFactory;
        $this->productRepository = $productRepository;
        $this->associationTypeRepository = $associationTypeRepository;
        $this->associationRepository = $associationRepository;
    }

    public function __invoke(ProductUpdated $event, ProductInterface $product): void
    {
        $this->handleAssociations($event->associations(), $product);
    }

    private function handleAssociations(array $associations, ProductInterface $product): void
    {
        /** @var Collection|ProductAssociationInterface[] $currentProductAssociations */
        $currentProductAssociations = $product->getAssociations();
        $currentProductAssociations = $currentProductAssociations->toArray();
        $processedProductAssociations = $this->processAssociations($associations, $product);

        $productAssociationToAdd = ResourceUtil::diff($processedProductAssociations, $currentProductAssociations);
        foreach ($productAssociationToAdd as $productAssociation) {
            $product->addAssociation($productAssociation);
        }

        $productAssociationToRemove = ResourceUtil::diff($currentProductAssociations, $processedProductAssociations);
        foreach ($productAssociationToRemove as $productAssociation) {
            $product->removeAssociation($productAssociation);
        }
    }

    private function processAssociations(array $associations, ProductInterface $product): array
    {
        /** @var ProductAssociationInterface[] $productAssociations */
        $productAssociations = [];

        foreach ($associations as $associationTypeCode => $productsCodes) {
            /** @var ProductAssociationTypeInterface $associationType */
            $associationType = $this->associationTypeRepository->findOneBy(['code' => $associationTypeCode]);

            if (null === $associationType) {
                continue;
            }

            $association = $this->provideAssociation($product, $associationType);

            foreach ($association->getAssociatedProducts() as $associatedProduct) {
                $association->removeAssociatedProduct($associatedProduct);
            }

            foreach ($productsCodes as $productCode) {
                /** @var ProductInterface|null $relatedProduct */
                $relatedProduct = $this->productRepository->findOneBy(['code' => $productCode]);

                if (null === $relatedProduct) {
                    continue;
                }

                $association->addAssociatedProduct($relatedProduct);
            }

            $productAssociations[] = $association;
        }

        return $productAssociations;
    }

    private function provideAssociation(ProductInterface $product, ProductAssociationTypeInterface $associationType): ProductAssociationInterface
    {
        /** @var ProductAssociationInterface $association */
        $association = $this->associationRepository->findOneBy(['type' => $associationType, 'owner' => $product]);

        if (null === $association) {
            $association = $this->associationFactory->createNew();
            $association->setOwner($product);
            $association->setType($associationType);
        }

        return $association;
    }
}
