<?php

namespace SS6\ShopBundle\Model\Order\Item;

use Doctrine\ORM\Mapping as ORM;
use SS6\ShopBundle\Model\Order\Item\OrderItem;
use SS6\ShopBundle\Model\Order\Order;
use SS6\ShopBundle\Model\Product\Product;

/**
 * @ORM\Entity
 */
class OrderProduct extends OrderItem {

	/**
	 * @var \SS6\ShopBundle\Model\Product\Product
	 *
	 * @ORM\ManyToOne(targetEntity="SS6\ShopBundle\Model\Product\Product")
	 */
	private $product;

	/**
	 * @param \SS6\ShopBundle\Model\Order\Order $order
	 * @param string $name
	 * @param string $priceWithoutVat
	 * @param string $priceWithVat
	 * @param string $vatPercent
	 * @param int $quantity
	 * @param \SS6\ShopBundle\Model\Product\Product $product
	 */
	public function __construct(
		Order $order,
		$name,
		$priceWithoutVat,
		$priceWithVat,
		$vatPercent,
		$quantity,
		Product $product
	) {
		parent::__construct(
			$order,
			$name,
			$priceWithoutVat,
			$priceWithVat,
			$vatPercent,
			$quantity
		);
		$this->product = $product;
	}

	/**
	 * @return \SS6\ShopBundle\Model\Product\Product
	 */
	public function getProduct() {
		return $this->product;
	}

}
