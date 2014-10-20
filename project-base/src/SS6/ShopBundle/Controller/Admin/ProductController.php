<?php

namespace SS6\ShopBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SS6\ShopBundle\Form\Admin\Product\QuickSearchFormType;
use SS6\ShopBundle\Model\AdminNavigation\MenuItem;
use SS6\ShopBundle\Model\Grid\QueryBuilderDataSource;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ProductController extends Controller {

	/**
	 * @Route("/product/edit/{id}", requirements={"id" = "\d+"})
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param int $id
	 */
	public function editAction(Request $request, $id) {
		$flashMessageSender = $this->get('ss6.shop.flash_message.sender.admin');
		/* @var $flashMessageSender \SS6\ShopBundle\Model\FlashMessage\FlashMessageSender */
		$productRepository = $this->get('ss6.shop.product.product_repository');
		/* @var $productRepository \SS6\ShopBundle\Model\Product\ProductRepository */
		$productDetailFactory = $this->get('ss6.shop.product.product_detail_factory');
		/* @var $productDetailFactory \SS6\ShopBundle\Model\Product\Detail\Factory */
		$productFormTypeFactory = $this->get('ss6.shop.form.admin.product.product_form_type_factory');
		/* @var $productFormTypeFactory \SS6\ShopBundle\Form\Admin\Product\ProductFormTypeFactory */
		$domain = $this->get('ss6.shop.domain');
		/* @var $domain \SS6\ShopBundle\Model\Domain\Domain */
		$productDataFactory = $this->get('ss6.shop.product.product_data_factory');
		/* @var $productDataFactory \SS6\ShopBundle\Model\Product\ProductDataFactory */

		$product = $productRepository->getById($id);

		$form = $this->createForm($productFormTypeFactory->create());
		$productData = $productDataFactory->createFromProduct($product);

		$form->setData($productData);
		$form->handleRequest($request);

		if ($form->isValid()) {
			$productEditFacade = $this->get('ss6.shop.product.product_edit_facade');
			/* @var $productEditFacade \SS6\ShopBundle\Model\Product\ProductEditFacade */
			$product = $productEditFacade->edit($id, $form->getData());

			$flashMessageSender->addSuccessTwig('Bylo upraveno zboží <strong>{{ name }}</strong>', array(
				'name' => $product->getName(),
			));
			return $this->redirect($this->generateUrl('admin_product_edit', array('id' => $product->getId())));
		}

		if ($form->isSubmitted() && !$form->isValid()) {
			$flashMessageSender->addErrorTwig('Prosím zkontrolujte si správnost vyplnění všech údajů');
		}

		$breadcrumb = $this->get('ss6.shop.admin_navigation.breadcrumb');
		/* @var $breadcrumb \SS6\ShopBundle\Model\AdminNavigation\Breadcrumb */
		$breadcrumb->replaceLastItem(new MenuItem('Editace zboží - ' . $product->getName()));

		return $this->render('@SS6Shop/Admin/Content/Product/edit.html.twig', array(
			'form' => $form->createView(),
			'product' => $product,
			'productDetail' => $productDetailFactory->getDetailForProduct($product),
			'domainService' => $domain,
		));
	}

	/**
	 * @Route("/product/new/")
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 */
	public function newAction(Request $request) {
		$flashMessageSender = $this->get('ss6.shop.flash_message.sender.admin');
		/* @var $flashMessageSender \SS6\ShopBundle\Model\FlashMessage\FlashMessageSender */
		$productFormTypeFactory = $this->get('ss6.shop.form.admin.product.product_form_type_factory');
		/* @var $productFormTypeFactory \SS6\ShopBundle\Form\Admin\Product\ProductFormTypeFactory */
		$domain = $this->get('ss6.shop.domain');
		/* @var $domain \SS6\ShopBundle\Model\Domain\Domain */
		$productDataFactory = $this->get('ss6.shop.product.product_data_factory');
		/* @var $productDataFactory \SS6\ShopBundle\Model\Product\ProductDataFactory */

		$form = $this->createForm($productFormTypeFactory->create());

		$productData = $productDataFactory->createDefault();

		$form->setData($productData);
		$form->handleRequest($request);

		if ($form->isValid()) {
			$productEditFacade = $this->get('ss6.shop.product.product_edit_facade');
			/* @var $productEditFacade \SS6\ShopBundle\Model\Product\ProductEditFacade */
			$product = $productEditFacade->create($form->getData());

			$flashMessageSender->addSuccessTwig('Bylo vytvořeno zboží'
					. ' <strong><a href="{{ url }}">{{ name }}</a></strong>', array(
				'name' => $product->getName(),
				'url' => $this->generateUrl('admin_product_edit', array('id' => $product->getId())),
			));
			return $this->redirect($this->generateUrl('admin_product_list'));
		}

		if ($form->isSubmitted() && !$form->isValid()) {
			$flashMessageSender->addErrorTwig('Prosím zkontrolujte si správnost vyplnění všech údajů');
		}

		return $this->render('@SS6Shop/Admin/Content/Product/new.html.twig', array(
			'form' => $form->createView(),
			'domainService' => $domain,
		));
	}

	/**
	 * @Route("/product/list/")
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 */
	public function listAction(Request $request) {
		$administratorGridFacade = $this->get('ss6.shop.administrator.administrator_grid_facade');
		/* @var $administratorGridFacade \SS6\ShopBundle\Model\Administrator\AdministratorGridFacade */
		$administrator = $this->getUser();
		/* @var $administrator \SS6\ShopBundle\Model\Administrator\Administrator */
		$gridFactory = $this->get('ss6.shop.grid.factory');
		/* @var $gridFactory \SS6\ShopBundle\Model\Grid\GridFactory */
		$productListAdminFacade = $this->get('ss6.shop.product.list.product_list_admin_facade');
		/* @var $productListAdminFacade \SS6\ShopBundle\Model\Product\Listing\ProductListAdminFacade */

		$form = $this->createForm(new QuickSearchFormType());
		$form->handleRequest($request);
		$searchData = $form->getData();
		$queryBuilder = $productListAdminFacade->getQueryBuilderByQuickSearchData($searchData);
		$dataSource = new QueryBuilderDataSource($queryBuilder, 'p.id');

		$grid = $gridFactory->create('productList', $dataSource);
		$grid->allowPaging();
		$grid->setDefaultOrder('name');

		$grid->addColumn('visible', 'p.visible', 'Viditelnost', true)->setClassAttribute('table-col table-col-10');
		$grid->addColumn('name', 'p.name', 'Název', true);
		$grid->addColumn('price', 'p.price', 'Cena', true)->setClassAttribute('text-right');

		$grid->setActionColumnClassAttribute('table-col table-col-10');
		$grid->addActionColumn('edit', 'Upravit', 'admin_product_edit', array('id' => 'p.id'));
		$grid->addActionColumn('delete', 'Smazat', 'admin_product_delete', array('id' => 'p.id'))
			->setConfirmMessage('Opravdu chcete odstranit toto zboží?');

		$administratorGridFacade->restoreAndRememberGridLimit($administrator, $grid);

		return $this->render('@SS6Shop/Admin/Content/Product/list.html.twig', array(
			'gridView' => $grid->createView(),
			'quickSearchForm' => $form->createView(),
		));
	}

	/**
	 * @Route("/product/delete/{id}", requirements={"id" = "\d+"})
	 * @param int $id
	 */
	public function deleteAction($id) {
		$flashMessageSender = $this->get('ss6.shop.flash_message.sender.admin');
		/* @var $flashMessageSender \SS6\ShopBundle\Model\FlashMessage\FlashMessageSender */
		$productRepository = $this->get('ss6.shop.product.product_repository');
		/* @var $productRepository \SS6\ShopBundle\Model\Product\ProductRepository */

		$productName = $productRepository->getById($id)->getName();
		$this->get('ss6.shop.product.product_edit_facade')->delete($id);

		$flashMessageSender->addSuccessTwig('Produkt <strong>{{ name }}</strong> byl smazán', array(
			'name' => $productName,
		));
		return $this->redirect($this->generateUrl('admin_product_list'));
	}
}
