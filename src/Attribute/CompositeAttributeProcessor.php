<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Attribute;

use Sylake\SyliusConsumerPlugin\Model\Attribute;
use Sylius\Component\Core\Model\ProductInterface;

final class CompositeAttributeProcessor implements AttributeProcessorInterface
{
    /** @var AttributeProcessorInterface[] */
    private $processors;

    public function __construct(AttributeProcessorInterface ...$processors)
    {
        $this->processors = $processors;
    }

    /** {@inheritdoc} */
    public function process(ProductInterface $product, Attribute $attribute): void
    {
        foreach ($this->processors as $processor) {
            $processor->process($product, $attribute);
        }
    }
}
