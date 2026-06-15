<?php

declare(strict_types=1);

namespace Tests\Php\Layout;

use PHPUnit\Framework\TestCase;
use Tests\Php\Support\LayoutRenderer;

/**
 * Проверяет HTML-контракт основного layout и partial layouts фильтра.
 */
final class DefaultLayoutTest extends TestCase
{
    /**
     * Пустой filter должен завершать layout без вывода HTML.
     */
    public function testEmptyFilterDoesNotRenderHtml(): void
    {
        $result = (new LayoutRenderer())->render([], null);

        self::assertSame('', $result->html);
    }

    /**
     * Filter с empty=true должен завершать layout без вывода HTML.
     */
    public function testFilterMarkedEmptyDoesNotRenderHtml(): void
    {
        $result = (new LayoutRenderer())->render([], (object) ['empty' => true]);

        self::assertSame('', $result->html);
    }

    /**
     * Layout должен подключать CSS/JS assets только когда это разрешено params.
     */
    public function testAssetsFollowModuleParams(): void
    {
        $enabled = (new LayoutRenderer())->render([], $this->createFilter());

        self::assertSame(['bootstrap.offcanvas', 'ishop_filter.front', 'ishop_filter.range'], $enabled->webAssetManager->scripts);
        self::assertSame(['ishop_filter.front', 'ishop_filter.range'], $enabled->webAssetManager->styles);

        $disabled = (new LayoutRenderer())->render(['use_css' => 0, 'use_js' => 0], $this->createFilter());

        self::assertSame(['bootstrap.offcanvas'], $disabled->webAssetManager->scripts);
        self::assertSame([], $disabled->webAssetManager->styles);
    }

    /**
     * Корневой wrapper должен быть responsive offcanvas справа.
     */
    public function testRootWrapperUsesResponsiveEndOffcanvas(): void
    {
        $html = (new LayoutRenderer())->render([], $this->createFilter())->html;

        self::assertStringContainsString('class="mod_ishop_filter offcanvas-lg offcanvas-end"', $html);
        self::assertStringContainsString('id="moduleFilter"', $html);
        self::assertStringContainsString('data-offcanvas-panels', $html);
        self::assertStringContainsString('class="btn-close filter-header-close d-lg-none"', $html);
        self::assertStringContainsString('data-bs-target="#moduleFilter"', $html);
        self::assertStringNotContainsString('offcanvas-start', $html);
    }

    /**
     * Позиция модуля не должна менять сторону mobile offcanvas.
     */
    public function testRootWrapperUsesResponsiveEndOffcanvasForAnyModulePosition(): void
    {
        foreach (['sidebar-left', 'sidebar-right', 'top'] as $position) {
            $html = (new LayoutRenderer())->render(['module_position' => $position], $this->createFilter())->html;

            self::assertStringContainsString('class="mod_ishop_filter offcanvas-lg offcanvas-end"', $html);
            self::assertStringContainsString('id="moduleFilter"', $html);
            self::assertStringContainsString('data-bs-target="#moduleFilter"', $html);
            self::assertStringNotContainsString('offcanvas-start', $html);
        }
    }

    /**
     * Основная форма должна сохранять data-контракт JS-фильтра и CSRF token.
     */
    public function testFormContractAttributesAreRendered(): void
    {
        $html = (new LayoutRenderer())->render(['module_id' => 222], $this->createFilter(), ['id' => 12, 'Itemid' => 34])->html;

        self::assertStringContainsString('name="ishop_filter"', $html);
        self::assertStringContainsString('id="i-filter-222"', $html);
        self::assertStringContainsString('data-category-id="12"', $html);
        self::assertStringContainsString('data-item-id="34"', $html);
        self::assertStringContainsString('data-product-count="5"', $html);
        self::assertStringContainsString('data-preview-url="/index.php?option=com_ishop&amp;task=filter.preview&amp;format=json"', $html);
        self::assertStringContainsString('data-reset-url="/index.php?option=com_ishop&amp;task=filter.reset&amp;format=json"', $html);
        self::assertStringContainsString('data-submit-template="MOD_ISHOP_FILTER_MODULE_SUBMIT_COUNT"', $html);
        self::assertStringContainsString('data-submit-unavailable-text="MOD_ISHOP_FILTER_MODULE_SUBMIT_UNAVAILABLE"', $html);
        self::assertStringContainsString('name="csrf.token" value="1"', $html);
    }

    /**
     * Layout должен использовать canonical com_ishop endpoints, а не legacy com_ajax.
     */
    public function testEndpointContractUsesComIshop(): void
    {
        $html = (new LayoutRenderer())->render([], $this->createFilter())->html;

        self::assertStringContainsString('option=com_ishop&amp;task=filter.preview&amp;format=json', $html);
        self::assertStringContainsString('option=com_ishop&amp;task=filter.reset&amp;format=json', $html);
        self::assertStringNotContainsString('option=com_ajax', $html);
    }

    /**
     * HTML hooks должны совпадать с тем, что ожидает media/js/front.js.
     */
    public function testSubmitResetTagsAndLoadingHooksAreRendered(): void
    {
        $html = (new LayoutRenderer())->render(['module_id' => 333], $this->createFilter())->html;

        self::assertStringContainsString('form="i-filter-333"', $html);
        self::assertStringContainsString('data-filter-submit', $html);
        self::assertStringContainsString('data-filter-reset', $html);
        self::assertStringContainsString('data-filter-active-tags', $html);
        self::assertStringContainsString('class="filter-loading-overlay"', $html);
        self::assertStringContainsString('class="visually-hidden">MOD_ISHOP_FILTER_LOADING</span>', $html);
    }

    /**
     * Состояние submit должно зависеть от количества товаров.
     */
    public function testSubmitStateFollowsProductCount(): void
    {
        $availableHtml = (new LayoutRenderer())->render([], $this->createFilter(['total' => 5]))->html;
        $emptyHtml = (new LayoutRenderer())->render([], $this->createFilter(['total' => 0]))->html;

        self::assertStringContainsString('data-filter-submit', $availableHtml);
        self::assertStringContainsString('aria-disabled="false"', $availableHtml);
        self::assertStringContainsString('data-filter-submit-hint', $availableHtml);
        self::assertStringContainsString('hidden>MOD_ISHOP_FILTER_MODULE_SUBMIT_UNAVAILABLE_HINT', $availableHtml);

        self::assertStringContainsString('aria-disabled="true" disabled', $emptyHtml);
        self::assertStringContainsString('>MOD_ISHOP_FILTER_MODULE_SUBMIT_UNAVAILABLE</span>', $emptyHtml);
        self::assertStringContainsString('data-filter-submit-hint', $emptyHtml);
        self::assertStringNotContainsString('hidden>MOD_ISHOP_FILTER_MODULE_SUBMIT_UNAVAILABLE_HINT', $emptyHtml);
    }

    /**
     * Price и sale partials должны сохранять имена полей и slider-разметку.
     */
    public function testPriceAndSalePartialsRenderExpectedControls(): void
    {
        $html = (new LayoutRenderer())->render([], $this->createFilter())->html;

        self::assertStringContainsString('name="min_price"', $html);
        self::assertStringContainsString('name="max_price"', $html);
        self::assertStringContainsString('name="good_price"', $html);
        self::assertStringContainsString('step="1"', $html);
        self::assertStringContainsString('class="form-control range-min"', $html);
        self::assertStringContainsString('class="form-control range-max"', $html);
        self::assertStringContainsString('class="range-slider__line"', $html);
        self::assertStringContainsString('class="range-slider__point range-slider__point--upper"', $html);
        self::assertStringContainsString('class="range-slider__point range-slider__point--lower"', $html);
    }

    /**
     * Size partial должен выводить стандартные поля габаритов и веса.
     */
    public function testSizePartialRendersDimensionControls(): void
    {
        $html = (new LayoutRenderer())->render([], $this->createFilter())->html;

        foreach ([
            'min_width',
            'max_width',
            'min_height',
            'max_height',
            'min_depth',
            'max_depth',
            'min_weight',
            'max_weight',
        ] as $name) {
            self::assertStringContainsString('name="' . $name . '"', $html);
        }

        self::assertGreaterThanOrEqual(5, substr_count($html, 'step="1"'));
    }

    /**
     * Brand и warehouse partials должны сохранять массивные имена и доступность значений.
     */
    public function testBrandAndWarehousePartialsRenderOptionsAndAvailability(): void
    {
        $html = (new LayoutRenderer())->render([], $this->createFilter())->html;

        self::assertStringContainsString('name="manufacturers[]" value="0"', $html);
        self::assertStringContainsString('name="warehouses[]" value="0"', $html);
        self::assertStringContainsString('id="manufacturers-1"', $html);
        self::assertStringContainsString('id="manufacturers-2"', $html);
        self::assertStringContainsString('id="warehouses-10"', $html);
        self::assertStringContainsString('id="warehouses-11"', $html);
        self::assertStringContainsString('id="manufacturers-2"', $html);
        self::assertStringContainsString('value="2" checked', $html);
        self::assertStringContainsString('value="11" checked', $html);
        self::assertStringContainsString('id="manufacturers-3"', $html);
        self::assertStringContainsString('value="3"  disabled', $html);
        self::assertStringContainsString('id="warehouses-12"', $html);
        self::assertStringContainsString('value="12"  disabled', $html);
    }

    /**
     * Field partials должны сохранять range, boolean и list field names.
     */
    public function testFieldPartialsRenderRangeBooleanAndListControls(): void
    {
        $html = (new LayoutRenderer())->render([], $this->createFilter())->html;

        self::assertStringContainsString('name="ishop_fields[12][min]"', $html);
        self::assertStringContainsString('name="ishop_fields[12][max]"', $html);
        self::assertStringContainsString('data-filter-label="Размер, см"', $html);
        self::assertStringContainsString('name="ishop_fields[13]" value="0"', $html);
        self::assertStringContainsString('name="ishop_fields[13]"', $html);
        self::assertStringContainsString('name="ishop_fields[14][]" value="0"', $html);
        self::assertStringContainsString('name="ishop_fields[14][]"', $html);
        self::assertStringContainsString('data-field-id="14"', $html);
        self::assertStringContainsString('data-selected-count', $html);
        self::assertStringContainsString('value="102" checked', $html);
        self::assertStringContainsString('value="103"  disabled', $html);
    }

    /**
     * Пользовательские title/label значения должны экранироваться в HTML.
     */
    public function testLayoutEscapesUserFacingValues(): void
    {
        $filter = $this->createFilter([
            'manufacturers' => [
                (object) ['id' => 1, 'title' => 'Brand <b>A</b>'],
                (object) ['id' => 2, 'title' => 'Brand B'],
            ],
            'warehouses' => [(object) ['id' => 10, 'title' => 'WH <script>x</script>']],
            'ishop_fields' => [
                (object) [
                    'id' => 12,
                    'type' => 0,
                    'title' => 'Размер <b>',
                    'unit' => 'см <u>',
                    'values' => '1,20',
                ],
                (object) [
                    'id' => 14,
                    'type' => 1,
                    'title' => 'Цвет <i>',
                    'alias' => 'color',
                    'values_id' => '101||102',
                    'values' => 'Red <b>||Blue <i>',
                ],
            ],
        ]);

        $html = (new LayoutRenderer())->render([], $filter)->html;

        self::assertStringContainsString('Brand &lt;b&gt;A&lt;/b&gt;', $html);
        self::assertStringContainsString('WH &lt;script&gt;x&lt;/script&gt;', $html);
        self::assertStringContainsString('Размер &lt;b&gt;, см &lt;u&gt;', $html);
        self::assertStringContainsString('Цвет &lt;i&gt;', $html);
        self::assertStringContainsString('Red &lt;b&gt;', $html);
        self::assertStringContainsString('Blue &lt;i&gt;', $html);
        self::assertStringNotContainsString('Brand <b>A</b>', $html);
        self::assertStringNotContainsString('WH <script>x</script>', $html);
    }

    /**
     * Создает полный fake filter для рендера всех partial layouts.
     */
    private function createFilter(array $overrides = []): object
    {
        $filter = [
            'empty' => false,
            'total' => 5,
            'availableOptions' => [
                'manufacturers' => [1],
                'warehouses' => [10],
                'price_range' => ['min' => 100, 'max' => 900],
                'sizes' => [
                    'width' => ['min' => 1, 'max' => 10],
                    'height' => ['min' => 2, 'max' => 20],
                    'depth' => ['min' => 3, 'max' => 30],
                    'weight' => ['min' => 4, 'max' => 40],
                ],
                'ishop_fields' => [
                    12 => ['type' => 'range', 'min' => 5, 'max' => 15],
                    13 => ['type' => 'boolean'],
                    14 => ['type' => 'list', 'values' => [101 => true]],
                ],
            ],
            'main' => [
                'min_price' => 100,
                'max_price' => 900,
                'min_width' => 1,
                'max_width' => 10,
                'min_height' => 2,
                'max_height' => 20,
                'min_depth' => 3,
                'max_depth' => 30,
                'min_weight' => 4,
                'max_weight' => 40,
            ],
            'active' => [
                'min_price' => 120,
                'max_price' => 800,
                'good_price' => 1,
                'manufacturers' => [2],
                'warehouses' => [11],
                'fields' => [
                    12 => ['min' => 6, 'max' => 14],
                    13 => 1,
                    14 => [102],
                ],
            ],
            'manufacturers' => [
                (object) ['id' => 1, 'title' => 'Brand A'],
                (object) ['id' => 2, 'title' => 'Brand B'],
                (object) ['id' => 3, 'title' => 'Brand C'],
            ],
            'warehouses' => [
                (object) ['id' => 10, 'title' => 'WH A'],
                (object) ['id' => 11, 'title' => 'WH B'],
                (object) ['id' => 12, 'title' => 'WH C'],
            ],
            'ishop_fields' => [
                (object) [
                    'id' => 12,
                    'type' => 0,
                    'title' => 'Размер',
                    'unit' => 'см',
                    'values' => '1,20',
                ],
                (object) [
                    'id' => 13,
                    'type' => 2,
                    'title' => 'В наличии',
                    'unit' => '',
                    'values' => '',
                ],
                (object) [
                    'id' => 14,
                    'type' => 1,
                    'title' => 'Цвет',
                    'alias' => 'color',
                    'values_id' => '101||102||103',
                    'values' => 'Red||Blue||Green',
                ],
            ],
        ];

        return (object) array_replace($filter, $overrides);
    }
}
