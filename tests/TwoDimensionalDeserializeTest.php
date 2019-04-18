<?php

namespace App\Tests;

use App\Model\Child;
use App\Model\Root;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;
use Symfony\Component\Serializer\SerializerInterface;

class TwoDimensionalDeserializeTest extends KernelTestCase
{
    /* @var SerializerInterface */
    protected $serializer;

    protected function setUp()
    {
        self::bootKernel();

        $normalizers = [
            new ArrayDenormalizer(),
            new ObjectNormalizer(
                self::$container->get(ClassMetadataFactoryInterface::class),
                null,
                null,
                new PhpDocExtractor()
            )
        ];

        $this->serializer = new SymfonySerializer(
            $normalizers,
            [new JsonEncoder()]
        );
    }

    public function test()
    {
        $json = '{"content":[[{"name": "John"}], []]}';

        /* @var Root $result */
        $result = $this->serializer->deserialize($json, Root::class, 'json', [AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false]);

        $this->assertInstanceOf(Root::class, $result);
        $this->assertCount(2, $result->content);
        $this->assertCount(1, $result->content[0]);

        $firstChild = $result->content[0][0];
        $this->assertInstanceOf(Child::class, $firstChild);
        $this->assertSame('John', $firstChild->name);
    }
}