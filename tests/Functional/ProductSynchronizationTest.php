<?php

namespace Tests\Sylake\SyliusConsumerPlugin\Functional;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\Assert;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author Kamil Kokot <kamil@kokot.me>
 */
final class ProductSynchronizationTest extends KernelTestCase
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ConsumerInterface
     */
    private $consumer;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        self::bootKernel();

        $this->entityManager = static::$kernel->getContainer()->get('doctrine')->getManager();
        $this->productRepository = static::$kernel->getContainer()->get('sylius.repository.product');
        $this->consumer = static::$kernel->getContainer()->get('rabbitmq_simplebus.consumer');

        (new ORMPurger($this->entityManager))->purge();
    }

    /**
     * @test
     */
    public function it_adds_a_new_product_with_basic_product_information()
    {
        $this->consumer->execute(new AMQPMessage('{
            "type": "akeneo_product_created",
            "payload": {
                "identifier": "AKNTS_BPXS",
                "family": "tshirts",
                "groups": [],
                "variant_group": "akeneo_tshirt",
                "categories": ["goodies", "tshirts"],
                "enabled": true,
                "values": {
                    "sku": [{"locale": null, "scope": null, "data": "AKNTS_BPXS"}],
                    "clothing_size": [{"locale": null, "scope": null, "data": "xs"}],
                    "main_color": [{"locale": null, "scope": null, "data": "black"}],
                    "name": [{"locale": null, "scope": null, "data": "Akeneo T-Shirt black and purple with short sleeve"}],
                    "secondary_color": [{"locale": null, "scope": null, "data": "purple"}],
                    "tshirt_materials": [{"locale": null, "scope": null, "data": "cotton"}],
                    "tshirt_style": [{"locale": null, "scope": null, "data": ["crewneck", "short_sleeve"]}],
                    "price": [{"locale": null, "scope": null, "data": [{"amount": 10, "currency": "EUR"}, {"amount": 14, "currency": "USD"}]}],
                    "description": [{"locale": "de_DE", "scope": "mobile", "data": "T-Shirt description"}],
                    "picture": [{"locale": null, "scope": null, "data": null}]
                },
                "created": "2017-04-18T16:12:55+02:00",
                "updated": "2017-04-18T16:12:55+02:00",
                "associations": {"SUBSTITUTION": {"groups": [], "products": ["AKNTS_WPXS", "AKNTS_PBXS", "AKNTS_PWXS"]}}
            },
            "recordedOn": "2017-05-22 10:13:34"
        }'));

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => 'AKNTS_BPXS']);

        Assert::assertNotNull($product);
        Assert::assertSame('Akeneo T-Shirt black and purple with short sleeve', $product->getTranslation('en_US')->getName());
        Assert::assertSame('aknts-bpxs-akeneo-t-shirt-black-and-purple-with-short-sleeve', $product->getTranslation('en_US')->getSlug());
        Assert::assertSame('T-Shirt description', $product->getTranslation('en_US')->getDescription());
        Assert::assertEquals(\DateTime::createFromFormat(\DateTime::W3C, '2017-04-18T16:12:55+02:00'), $product->getCreatedAt());
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $this->entityManager->clear();
        $this->entityManager = null;
    }
}
