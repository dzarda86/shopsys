<?php

namespace SS6\ShopBundle\Model\Breadcrumb;

use SS6\ShopBundle\Component\Breadcrumb\BreadcrumbGeneratorInterface;
use SS6\ShopBundle\Component\Breadcrumb\BreadcrumbItem;
use SS6\ShopBundle\Component\Breadcrumb\BreadcrumbResolver;

/**
 * @return \SS6\ShopBundle\Component\Breadcrumb\BreadcrumbItem
 */
class FrontBreadcrumbGenerator implements BreadcrumbGeneratorInterface {

	public function getBreadcrumbItems($routeName, array $routeParameters = []) {
		switch ($routeName) {
			case 'front_customer_edit':
				return [
					new BreadcrumbItem(t('Upravit údaje')),
				];
			case 'front_customer_orders':
				return [
					new BreadcrumbItem(t('Objednávky')),
				];
			case 'front_registration_reset_password':
				return [
					new BreadcrumbItem(t('Zapomenuté heslo')),
				];
			case 'front_customer_order_detail_registered':
			case 'front_customer_order_detail_unregistered':
				return [
					new BreadcrumbItem(t('Detail objednávky')),
				];
			case 'front_login':
				return [
					new BreadcrumbItem(t('Prihlásení')),
				];
			case 'front_product_search':
				return [
					new BreadcrumbItem(t('Hledání')),
				];
			case 'front_registration_register':
				return [
					new BreadcrumbItem(t('Registrace')),
				];
			case 'front_brand_list':
				return [
					new BreadcrumbItem(t('Prehled značek')),
				];
			case 'front_error_page':
				return $this->getBreacrumbItemForErrorPage($routeParameters['code']);

			case 'front_error_page_format':
				return $this->getBreacrumbItemForErrorPage($routeParameters['code']);
		}
	}

	/**
	 * @param \SS6\ShopBundle\Component\Breadcrumb\BreadcrumbResolver $frontBreadcrumbResolver
	 */
	public function registerAll(BreadcrumbResolver $frontBreadcrumbResolver) {
		$frontBreadcrumbResolver->registerGenerator('front_customer_edit', $this);
		$frontBreadcrumbResolver->registerGenerator('front_customer_orders', $this);
		$frontBreadcrumbResolver->registerGenerator('front_registration_reset_password', $this);
		$frontBreadcrumbResolver->registerGenerator('front_customer_order_detail_registered', $this);
		$frontBreadcrumbResolver->registerGenerator('front_customer_order_detail_unregistered', $this);
		$frontBreadcrumbResolver->registerGenerator('front_login', $this);
		$frontBreadcrumbResolver->registerGenerator('front_product_search', $this);
		$frontBreadcrumbResolver->registerGenerator('front_registration_register', $this);
		$frontBreadcrumbResolver->registerGenerator('front_brand_list', $this);
		$frontBreadcrumbResolver->registerGenerator('front_customer_edit', $this);
		$frontBreadcrumbResolver->registerGenerator('front_error_page', $this);
		$frontBreadcrumbResolver->registerGenerator('front_error_page_format', $this);
	}

	/**
	 * @param string $code
	 * @return \SS6\ShopBundle\Component\Breadcrumb\BreadcrumbItem
	 */
	private function getBreacrumbItemForErrorPage($code) {
		$breadcrumbName = t('Jejda! Nastala chyba');
		if ($code === '404') {
			$breadcrumbName = t('Stránka nenalezena');
		}

		return [
			new BreadcrumbItem($breadcrumbName),
		];
	}

}