<?php

namespace Tests\FrameworkBundle\Unit\Model\Product;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductData;

class ProductTest extends TestCase
{
    public function testNoVariant()
    {
        $productData = new ProductData();
        $product = Product::create($productData);

        $this->assertFalse($product->isVariant());
        $this->assertFalse($product->isMainVariant());
    }

    public function testIsVariant()
    {
        $productData = new ProductData();
        $variant = Product::create($productData);
        Product::createMainVariant($productData, [$variant]);

        $this->assertTrue($variant->isVariant());
        $this->assertFalse($variant->isMainVariant());
    }

    public function testIsMainVariant()
    {
        $productData = new ProductData();
        $variant = Product::create($productData);
        $mainVariant = Product::createMainVariant($productData, [$variant]);

        $this->assertFalse($mainVariant->isVariant());
        $this->assertTrue($mainVariant->isMainVariant());
    }

    public function testGetMainVariant()
    {
        $productData = new ProductData();
        $variant = Product::create($productData);
        $mainVariant = Product::createMainVariant($productData, [$variant]);

        $this->assertSame($mainVariant, $variant->getMainVariant());
    }

    public function testGetMainVariantException()
    {
        $productData = new ProductData();
        $product = Product::create($productData);

        $this->expectException(\Shopsys\FrameworkBundle\Model\Product\Exception\ProductIsNotVariantException::class);
        $product->getMainVariant();
    }

    public function testCreateVariantFromVariantException()
    {
        $productData = new ProductData();
        $variant = Product::create($productData);
        $variant2 = Product::create($productData);
        $mainVariant = Product::createMainVariant($productData, [$variant]);
        Product::createMainVariant($productData, [$variant2]);

        $this->expectException(\Shopsys\FrameworkBundle\Model\Product\Exception\ProductIsAlreadyVariantException::class);
        $mainVariant->addVariant($variant2);
    }

    public function testCreateVariantFromMainVariantException()
    {
        $productData = new ProductData();
        $variant = Product::create($productData);
        $variant2 = Product::create($productData);
        $mainVariant = Product::createMainVariant($productData, [$variant]);
        $mainVariant2 = Product::createMainVariant($productData, [$variant2]);

        $this->expectException(\Shopsys\FrameworkBundle\Model\Product\Exception\MainVariantCannotBeVariantException::class);
        $mainVariant->addVariant($mainVariant2);
    }

    public function testCreateMainVariantFromVariantException()
    {
        $productData = new ProductData();
        $variant = Product::create($productData);
        $variant2 = Product::create($productData);
        $variant3 = Product::create($productData);
        Product::createMainVariant($productData, [$variant]);
        Product::createMainVariant($productData, [$variant2]);

        $this->expectException(\Shopsys\FrameworkBundle\Model\Product\Exception\VariantCanBeAddedOnlyToMainVariantException::class);
        $variant2->addVariant($variant3);
    }

    public function testAddSelfAsVariantException()
    {
        $productData = new ProductData();
        $variant = Product::create($productData);
        $mainVariant = Product::createMainVariant($productData, [$variant]);

        $this->expectException(\Shopsys\FrameworkBundle\Model\Product\Exception\MainVariantCannotBeVariantException::class);
        $mainVariant->addVariant($mainVariant);
    }

    public function testMarkForVisibilityRecalculation()
    {
        $productData = new ProductData();
        $product = Product::create($productData);
        $product->markForVisibilityRecalculation();
        $this->assertTrue($product->isMarkedForVisibilityRecalculation());
    }

    public function testMarkForVisibilityRecalculationMainVariant()
    {
        $productData = new ProductData();
        $variant = Product::create($productData);
        $mainVariant = Product::createMainVariant($productData, [$variant]);
        $mainVariant->markForVisibilityRecalculation();
        $this->assertTrue($mainVariant->isMarkedForVisibilityRecalculation());
        $this->assertTrue($variant->isMarkedForVisibilityRecalculation());
    }

    public function testMarkForVisibilityRecalculationVariant()
    {
        $productData = new ProductData();
        $variant = Product::create($productData);
        $mainVariant = Product::createMainVariant($productData, [$variant]);
        $variant->markForVisibilityRecalculation();
        $this->assertTrue($variant->isMarkedForVisibilityRecalculation());
        $this->assertTrue($mainVariant->isMarkedForVisibilityRecalculation());
    }

    public function testDeleteResultNotVariant()
    {
        $productData = new ProductData();
        $product = Product::create($productData);

        $this->assertEmpty($product->getProductDeleteResult()->getProductsForRecalculations());
    }

    public function testDeleteResultVariant()
    {
        $productData = new ProductData();
        $variant = Product::create($productData);
        $mainVariant = Product::createMainVariant($productData, [$variant]);

        $this->assertSame([$mainVariant], $variant->getProductDeleteResult()->getProductsForRecalculations());
    }

    public function testDeleteResultMainVariant()
    {
        $productData = new ProductData();
        $variant = Product::create($productData);
        $mainVariant = Product::createMainVariant($productData, [$variant]);

        $this->assertEmpty($mainVariant->getProductDeleteResult()->getProductsForRecalculations());
        $this->assertFalse($variant->isVariant());
    }
}
