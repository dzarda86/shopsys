<?php

namespace SS6\ShopBundle\Model\Order;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\AbstractQuery;
use SS6\ShopBundle\Model\Order\Order;
use SS6\ShopBundle\Model\Order\Status\OrderStatus;

class OrderRepository {

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 */
	public function __construct(EntityManager $em) {
		$this->em = $em;
	}

	/**
	 * @return \Doctrine\ORM\EntityRepository
	 */
	private function getOrderRepository() {
		return $this->em->getRepository(Order::class);
	}

	/**
	 * @param int $userId
	 * @return \SS6\ShopBundle\Model\Order\Order[]
	 */
	public function findByUserId($userId) {
		return $this->getOrderRepository()->findBy(array(
			'customer' => $userId,
		));
	}

	/**
	 * @param int $userId
	 * @return \SS6\ShopBundle\Model\Order\Order|null
	 */
	public function findLastByUserId($userId) {
		return $this->getOrderRepository()->findOneBy(
			array(
				'customer' => $userId,
			),
			array(
				'createdAt' => 'DESC'
			)
		);
	}

	/**
	 * @param int $id
	 * @return \SS6\ShopBundle\Model\Order\Order|null
	 */
	public function findById($id) {
		return $this->getOrderRepository()->find($id);
	}

	/**
	 * @param int $id
	 * @return \SS6\ShopBundle\Model\Order\Order
	 * @throws \SS6\ShopBundle\Model\Order\Exception\OrderNotFoundException
	 */
	public function getById($id) {
		$order = $this->findById($id);

		if ($order === null) {
			throw new \SS6\ShopBundle\Model\Order\Exception\OrderNotFoundException($id);
		}

		return $order;
	}

	/**
	 * @param \SS6\ShopBundle\Model\Order\Status\OrderStatus $orderStatus
	 * @return int
	 */
	public function getOrdersCountByStatus(OrderStatus $orderStatus) {
		$query = $this->em->createQuery('
			SELECT COUNT(o)
			FROM ' . Order::class . ' o
			WHERE o.status = :status')
			->setParameter('status', $orderStatus->getId());
		$result = $query->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);
		return $result;
	}

	/**
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	public function getOrdersListQueryBuilder() {
		return $this->em->createQueryBuilder()
			->select('o')
			->from(Order::class, 'o')
			->where('o.deleted = :deleted')
			->setParameter('deleted', false);
	}

	/*
	 * @param int $orderStatusId
	 * @return \SS6\ShopBundle\Model\Order\Order[]
	 */
	public function findByStatusId($orderStatusId) {
		return $this->getOrderRepository()->findBy(array(
			'status' => $orderStatusId,
		));
	}

}
