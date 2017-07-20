<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Attribute;

use Sylake\SyliusConsumerPlugin\Model\Attribute;
use Sylius\Component\Core\Model\ProductImageInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class ImageAttributeProcessor implements AttributeProcessorInterface
{
    /** @var FactoryInterface */
    private $productImageFactory;

    /** @var RepositoryInterface */
    private $productImageRepository;

    /** @var string */
    private $imageAttribute;

    public function __construct(
        FactoryInterface $productImageFactory,
        RepositoryInterface $productImageRepository,
        string $imageAttribute
    ) {
        $this->productImageFactory = $productImageFactory;
        $this->productImageRepository = $productImageRepository;
        $this->imageAttribute = $imageAttribute;
    }

    /** {@inheritdoc} */
    public function process(ProductInterface $product, Attribute $attribute): array
    {
        if (!$this->supports($attribute)) {
            return [];
        }

        foreach ($product->getImagesByType('akeneo') as $productImage) {
            $product->removeImage($productImage);
        }

        if (null === $attribute->data()) {
            return [];
        }

        /** @var ProductImageInterface|null $image */
        $productImage = $this->productImageRepository->findOneBy([
            'owner' => $product,
            'type' => 'akeneo',
            'path' => $attribute->data(),
        ]);

        if (null === $productImage) {
            /** @var ProductImageInterface $productImage */
            $productImage = $this->productImageFactory->createNew();
            $productImage->setType('akeneo');
            $productImage->setPath($attribute->data());
        }

        $product->addImage($productImage);

        return [];
    }

    private function supports(Attribute $attribute): bool
    {
        return $this->imageAttribute === $attribute->attribute() && (null === $attribute->data() || is_string($attribute->data()));
    }
}
