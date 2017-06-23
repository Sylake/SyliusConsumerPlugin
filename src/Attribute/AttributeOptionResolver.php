<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Attribute;

use Sylake\SyliusConsumerPlugin\Entity\AkeneoAttributeOption;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class AttributeOptionResolver implements AttributeOptionResolverInterface
{
    /** @var RepositoryInterface */
    private $repository;

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /** {@inheritdoc} */
    public function resolve(string $attribute, string $locale, string $data): string
    {
        /** @var AkeneoAttributeOption $option */
        $option = $this->repository->findOneBy(['attribute' => $attribute, 'code' => $data]);
        if (null === $option) {
            return $data;
        }

        /** @var array $labels */
        $labels = $option->getLabels();
        if (!isset($labels[$locale])) {
            return $data;
        }

        return $labels[$locale];
    }
}
