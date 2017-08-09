<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Command;

use Sylake\SyliusConsumerPlugin\Model\Translations;
use Sylius\Component\Attribute\AttributeType\TextAttributeType;
use Sylius\Component\Attribute\Factory\AttributeFactoryInterface;
use Sylius\Component\Product\Model\ProductAttributeInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class SetupCommand extends Command
{
    /** @var RepositoryInterface */
    private $attributeRepository;

    /** @var AttributeFactoryInterface */
    private $attributeFactory;

    public function __construct(RepositoryInterface $attributeRepository, AttributeFactoryInterface $attributeFactory)
    {
        $this->attributeRepository = $attributeRepository;
        $this->attributeFactory = $attributeFactory;

        parent::__construct('sylake:consumer:setup');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->createAttributeIfNotExists('AKENEO_FAMILY_CODE', new Translations([
            'en_GB' => 'Family code',
            'de_DE' => 'Familiencode',
        ]), $output);
    }

    private function createAttributeIfNotExists(string $code, Translations $names, OutputInterface $output)
    {
        /** @var ProductAttributeInterface|null $attribute */
        $attribute = $this->attributeRepository->findOneBy(['code' => $code]);
        if (null !== $attribute) {
            $output->writeln(sprintf('Attribute "%s" already exists, skipping', $code));

            return;
        }

        $output->writeln('Creating attribute ' . $code);

        $attribute = $this->attributeFactory->createTyped(TextAttributeType::TYPE);
        $attribute->setCode($code);

        foreach ($names as $locale => $name) {
            $attribute->setFallbackLocale($locale);
            $attribute->setCurrentLocale($locale);
            $attribute->setName($name);
        }

        $this->attributeRepository->add($attribute);
    }
}
