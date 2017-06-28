<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Projector;

use Psr\Log\LoggerInterface;
use Sylake\SyliusConsumerPlugin\Attribute\AttributeProcessorInterface;
use Sylake\SyliusConsumerPlugin\Event\ProductCreated;
use Sylake\SyliusConsumerPlugin\Model\Attributes;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Core\Model\ProductTranslationInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Locale\Model\LocaleInterface;
use Sylius\Component\Product\Factory\ProductFactoryInterface;
use Sylius\Component\Product\Model\ProductAssociationInterface;
use Sylius\Component\Product\Model\ProductAssociationTypeInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Model\TranslationInterface;
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
     * @var FactoryInterface
     */
    private $associationFactory;

    /**
     * @var ProductSlugGeneratorInterface
     */
    private $productSlugGenerator;

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
    private $associationTypeRepository;

    /**
     * @var RepositoryInterface
     */
    private $associationRepository;

    /**
     * @var RepositoryInterface
     */
    private $localeRepository;

    /**
     * @var AttributeProcessorInterface
     */
    private $attributeProcessor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ProductFactoryInterface $productFactory,
        FactoryInterface $productTaxonFactory,
        FactoryInterface $associationFactory,
        ProductSlugGeneratorInterface $productSlugGenerator,
        RepositoryInterface $productRepository,
        RepositoryInterface $productTaxonRepository,
        RepositoryInterface $taxonRepository,
        RepositoryInterface $associationTypeRepository,
        RepositoryInterface $associationRepository,
        RepositoryInterface $localeRepository,
        AttributeProcessorInterface $attributeProcessor,
        LoggerInterface $logger
    ) {
        $this->productFactory = $productFactory;
        $this->productTaxonFactory = $productTaxonFactory;
        $this->associationFactory = $associationFactory;
        $this->productSlugGenerator = $productSlugGenerator;
        $this->productRepository = $productRepository;
        $this->productTaxonRepository = $productTaxonRepository;
        $this->taxonRepository = $taxonRepository;
        $this->associationTypeRepository = $associationTypeRepository;
        $this->associationRepository = $associationRepository;
        $this->localeRepository = $localeRepository;
        $this->attributeProcessor = $attributeProcessor;
        $this->logger = $logger;
    }

    public function __invoke(ProductCreated $event): void
    {
        $this->logger->debug(sprintf('Projecting product with code "%s".', $event->code()));

        $product = $this->provideProduct($event->code());
        $productVariant = $this->provideProductVariant($event->code(), $product);

        $this->handleEnabled($event->enabled(), $product);
        $this->handleMainTaxon($event->taxons(), $product);
        $this->handleProductTaxons($event->taxons(), $product);
        $this->handleAttributes($event->attributes(), $product);
        $this->handleAssociations($event->associations(), $product);
        $this->handleCreatedAt($event->createdAt(), $product, $productVariant);
        $this->handleSlug($product);

        $this->productRepository->add($product);
    }

    private function handleEnabled(bool $enabled, ProductInterface $product): void
    {
        $product->setEnabled($enabled);
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
        foreach ($product->getProductTaxons() as $productTaxon) {
            $product->removeProductTaxon($productTaxon);
        }

        foreach ($taxonCodes as $taxonCode) {
            /** @var TaxonInterface $taxon */
            $taxon = $this->taxonRepository->findOneBy(['code' => $taxonCode]);

            if (null === $taxon) {
                continue;
            }

            $productTaxon = $this->provideProductTaxon($product, $taxon);

            $product->addProductTaxon($productTaxon);
        }
    }

    private function handleAttributes(array $attributes, ProductInterface $product): void
    {
        foreach ($product->getAttributes() as $attributeValue) {
            $product->removeAttribute($attributeValue);
        }

        $locales = array_map(function (LocaleInterface $locale): string {
            return $locale->getCode();
        }, $this->localeRepository->findAll());

        foreach (new Attributes($attributes, $locales) as $attribute) {
            $this->attributeProcessor->process($product, $attribute);
        }
    }

    private function handleAssociations(array $associations, ProductInterface $product): void
    {
        foreach ($product->getAssociations() as $association) {
            $product->removeAssociation($association);
        }

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

            $product->addAssociation($association);
        }
    }

    private function handleCreatedAt(\DateTime $createdAt, ProductInterface $product, ProductVariantInterface $productVariant): void
    {
        $product->setCreatedAt($createdAt);
        $productVariant->setCreatedAt($createdAt);
    }

    private function handleSlug(ProductInterface $product): void
    {
        foreach ($product->getTranslations() as $productTranslation) {
            /** @var ProductTranslationInterface|TranslationInterface $productTranslation */
            $productTranslation->setSlug($this->productSlugGenerator->generate($product, $productTranslation->getLocale()));
        }
    }

    private function provideProduct(string $code): ProductInterface
    {
        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => $code]);

        if (null === $product) {
            $product = $this->productFactory->createWithVariant();
            $product->setCode($code);
        }

        return $product;
    }

    private function provideProductVariant(string $code, ProductInterface $product): ProductVariantInterface
    {
        /** @var ProductVariantInterface $productVariant */
        $productVariant = current($product->getVariants()->slice(0, 1));
        $productVariant->setCode($code);

        return $productVariant;
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
