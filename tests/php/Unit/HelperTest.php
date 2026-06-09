<?php

declare(strict_types=1);

namespace Tests\Php\Unit;

use Ilange\Component\Ishop\Site\Service\FilterAvailabilityService;
use Ilange\Component\Ishop\Site\Service\FilterRules;
use Ilange\Module\Ishopfilter\Site\Helper\IshopfilterHelper;
use PHPUnit\Framework\TestCase;
use Tests\Php\Support\FakeEnvironment;

/**
 * Проверяет helper модуля без настоящего Joomla application context и com_ishop.
 */
final class HelperTest extends TestCase
{
    /**
     * prepareFilter() должен ничего не делать вне category context при наличии id.
     */
    public function testPrepareFilterReturnsEmptyArrayOutsideCategoryWithId(): void
    {
        $environment = FakeEnvironment::install(['controller' => 'product', 'id' => 15], $this->createFilter());

        $result = IshopfilterHelper::prepareFilter();

        self::assertSame([], $result);
        self::assertSame([], $environment['app']->bootComponentCalls);
    }

    /**
     * Если controller не задан, helper должен брать context из view.
     */
    public function testPrepareFilterUsesViewAsControllerFallback(): void
    {
        $filter = $this->createFilter();
        $environment = FakeEnvironment::install(['view' => 'category', 'id' => 22, 'Itemid' => 44], $filter);
        FilterAvailabilityService::$filteredProductIds = [1, 2, 3];
        FilterAvailabilityService::$availableOptions = ['manufacturers' => [7]];

        $result = IshopfilterHelper::prepareFilter();

        self::assertSame($filter, $result);
        self::assertSame([['Category', 'Site']], $environment['mvcFactory']->createModelCalls);
        self::assertSame(['com_ishop'], $environment['app']->bootComponentCalls);
        self::assertSame(3, $filter->total);
        self::assertSame(['manufacturers' => [7]], $filter->availableOptions);
        self::assertSame([[22, 44, FilterRules::$normalizeCalls[0]]], FilterAvailabilityService::$filteredProductCalls);
        self::assertSame([[22, 44, FilterRules::$normalizeCalls[0]]], FilterAvailabilityService::$availableOptionsCalls);
    }

    /**
     * Category id должен браться из state модели, если input id отсутствует.
     */
    public function testPrepareFilterReadsCategoryIdFromModelStateWhenInputIdIsMissing(): void
    {
        $filter = $this->createFilter();
        FakeEnvironment::install(['view' => 'category', 'Itemid' => 5], $filter, ['category.id' => 91]);
        FilterAvailabilityService::$filteredProductIds = [10];

        IshopfilterHelper::prepareFilter();

        self::assertSame([[91, 5, FilterRules::$normalizeCalls[0]]], FilterAvailabilityService::$filteredProductCalls);
    }

    /**
     * Пустой filter object не должен запускать availability service.
     */
    public function testPrepareFilterSkipsAvailabilityForEmptyFilter(): void
    {
        $filter = (object) ['empty' => true];
        FakeEnvironment::install(['view' => 'category', 'id' => 22], $filter);

        $result = IshopfilterHelper::prepareFilter();

        self::assertSame($filter, $result);
        self::assertSame([], FilterRules::$normalizeCalls);
        self::assertSame([], FilterAvailabilityService::$filteredProductCalls);
        self::assertSame([], FilterAvailabilityService::$availableOptionsCalls);
    }

    /**
     * getActiveFilters() должен мапить active.fields в ishop_fields и приводить числовые поля.
     */
    public function testGetActiveFiltersNormalizesActiveFilterValues(): void
    {
        $filter = (object) [
            'active' => [
                'min_price' => '12.8',
                'max_price' => '99.2',
                'good_price' => '1',
                'min_width' => '2',
                'max_width' => '8',
                'min_height' => '3',
                'max_height' => '9',
                'min_depth' => '4',
                'max_depth' => '10',
                'min_weight' => '5',
                'max_weight' => '11',
                'manufacturers' => ['7', '8'],
                'warehouses' => ['3'],
                'fields' => [
                    12 => ['100', '101'],
                    15 => ['min' => '1', 'max' => '4'],
                ],
            ],
        ];

        $result = $this->invokeGetActiveFilters($filter);

        self::assertSame(12, $result['min_price']);
        self::assertSame(99, $result['max_price']);
        self::assertSame(1, $result['good_price']);
        self::assertSame(2, $result['min_width']);
        self::assertSame(8, $result['max_width']);
        self::assertSame(3, $result['min_height']);
        self::assertSame(9, $result['max_height']);
        self::assertSame(4, $result['min_depth']);
        self::assertSame(10, $result['max_depth']);
        self::assertSame(5, $result['min_weight']);
        self::assertSame(11, $result['max_weight']);
        self::assertSame(['7', '8'], $result['manufacturers']);
        self::assertSame(['3'], $result['warehouses']);
        self::assertSame($filter->active['fields'], $result['ishop_fields']);
    }

    /**
     * getActiveFilters() должен давать безопасные defaults при пустом active.
     */
    public function testGetActiveFiltersUsesDefaultsForMissingActiveValues(): void
    {
        $result = $this->invokeGetActiveFilters((object) []);

        self::assertSame(0, $result['min_price']);
        self::assertSame(0, $result['max_price']);
        self::assertSame(0, $result['good_price']);
        self::assertSame([], $result['manufacturers']);
        self::assertSame([], $result['warehouses']);
        self::assertSame([], $result['ishop_fields']);
    }

    /**
     * Создает минимальный рабочий объект фильтра.
     */
    private function createFilter(array $active = []): object
    {
        return (object) [
            'empty' => false,
            'active' => $active,
        ];
    }

    /**
     * Вызывает private getActiveFilters() без изменения production API.
     */
    private function invokeGetActiveFilters(object $filter): array
    {
        $method = new \ReflectionMethod(IshopfilterHelper::class, 'getActiveFilters');
        $method->setAccessible(true);

        return $method->invoke(null, $filter);
    }

}
