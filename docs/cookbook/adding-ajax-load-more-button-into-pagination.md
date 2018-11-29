# Adding Ajax Load More Button into Pagination

In this cookbook, we will add a paginated brand list including ajax "load more" button to a product list page. After finishing the guide, you will know how to use multiple paginations on one page.

## Implementation of Brand Pagination

First we will add `getPaginationResult` method into `BrandRepository`.

```php
/**
 * @param int $page
 * @param int $limit
 * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
 */
public function getPaginationResult(
    $page,
    $limit
) {
    $queryBuilder = $this->getBrandRepository()->createQueryBuilder('b');
    $queryBuilder->orderBy('b.name', 'asc');

    /** @var \Shopsys\FrameworkBundle\Component\Paginator\QueryPaginator $queryPaginator */
    $queryPaginator = new QueryPaginator($queryBuilder);

    return $queryPaginator->getResult($page, $limit);
}
```

Then we will add `getPaginatedResult` method into `BrandFacade`.

```php
/**
 * @param int $page
 * @param int $limit
 * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
 */
public function getPaginatedResult(
    $page,
    $limit
) {
    $paginationResult = $this->brandRepository->getPaginationResult(
        $page,
        $limit
    );
    
    return new PaginationResult(
        $paginationResult->getPage(),
        $paginationResult->getPageSize(),
        $paginationResult->getTotalCount(),
        $paginationResult->getResults()
    );
}
```

Next we will modify list twig template and create twig template for generation of paging controls and paginated items via ajax.  
We will use `paginator.loadMoreButton(paginationResult, url('front_brand_list'), pageQueryParameter)` twig component that will asynchronously load next page when user clicks on its button. We will also define `pageQueryParameter` variable so it will have unique name and it will not interfere with other paging component on the same page.

```twig
{# ShopBundle/Resources/views/Front/Content/Brand/list.html.twig #}
{% extends '@ShopsysShop/Front/Layout/layoutWithPanel.html.twig' %}

{% block title %}
    {{ 'Brand overview'|trans }}
{% endblock %}

{% block main_content %}
    <div>
        <h1>{{ 'Brand overview'|trans }}</h1>
        {% include '@ShopsysShop/Front/Content/Brand/ajaxList.html.twig' with {paginationResult: paginationResult} %}
    </div>
{% endblock %}
```

```twig
{# ShopBundle/Resources/views/Front/Content/Brand/ajaxList.html.twig #}
{% import '@ShopsysShop/Front/Inline/Paginator/paginator.html.twig' as paginator %}
{% set entityName = 'brands'|trans %}
{% set pageQueryParameter = 'brandPage' %}

<div>
    <div class="js-list-with-paginator">
        {{ paginator.paginatorNavigation(paginationResult, entityName, pageQueryParameter) }}
        <ul class='list-images js-list'>
            {% for brand in paginationResult.results %}
                <li class="list-images__item">
                    <a href="{{ url('front_brand_detail', { id: brand.id }) }}" class="list-images__item__block list-images__item__block--with-label">
                        {{ image(brand, { alt: brand.name }) }}
                        <span>{{ brand.name }}</span>
                    </a>
                </li>
            {% endfor %}
        </ul>
        <div class="text-center margin-bottom-20">
            {{ paginator.loadMoreButton(paginationResult, url('front_brand_list'), pageQueryParameter) }}
        </div>
        {{ paginator.paginatorNavigation(paginationResult, entityName, pageQueryParameter) }}
    </div>
</div>
```

After that we will modify `listAction` method in `BrandController` so `Brand` list page will be paginated and we will be able to integrate it into another list page that has other paginated items.  
We will implement also constants for page query parameter `const PAGE_QUERY_PARAMETER = 'brandPage'` and for the count of items on one page `const ITEMS_PER_PAGE = 5;`

```php
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
```

## Integration of Paginated Brand List

Now we have working implementation of paginated `Brand` list page that can be loaded also from asynchronous calls.
We can try to integrate it into another `Product` list page that is also paginated with page query parameter `page`.
Only thing we need to do is to modify template for `Product` page.  
We will add twig code into `main_content` block.

```twig
{# ShopBundle/Resources/views/Front/Content/Product/list.html.twig #}
{{ render(controller('ShopsysShopBundle:Front/Brand:list', { drawLayout: false })) }}
```

## Conclusion

Customer can see 2 paginated lists with buttons for loading items from next pages on each `Product` list page. Since there are unique page query parameters, paginated lists can have displayed different page indexes after browser is loaded with these page query parameters.
