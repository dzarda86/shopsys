<?php

namespace Shopsys\ShopBundle\Controller\Front;

use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Symfony\Component\HttpFoundation\Request;

class BrandController extends FrontBaseController
{
    const PAGE_QUERY_PARAMETER = 'brandPage';
    const ITEMS_PER_PAGE = 5;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade
     */
    private $brandFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade $brandFacade
     */
    public function __construct(
        BrandFacade $brandFacade
    ) {
        $this->brandFacade = $brandFacade;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param mixed $drawLayout
     */
    public function listAction(Request $request, $drawLayout = true)
    {
        if ($request->isXmlHttpRequest() || $drawLayout === false) {
            $template = '@ShopsysShop/Front/Content/Brand/ajaxList.html.twig';
        } else {
            $template = '@ShopsysShop/Front/Content/Brand/list.html.twig';
        }

        $requestPage = $request->get(self::PAGE_QUERY_PARAMETER);
        $page = $requestPage === null ? 1 : (int)$requestPage;

        return $this->render($template, [
            'paginationResult' => $this->brandFacade->getPaginatedResult($page, self::ITEMS_PER_PAGE),
        ]);
    }
}
