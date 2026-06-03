<?php
/**
 * @package    mod_ishop_filter
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/mod_ishop_filter
 * @copyright  (C) 2026 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

namespace Ilange\Module\Ishopfilter\Site\Helper;

use Ilange\Component\Ishop\Site\Service\FilterAvailabilityService;
use Ilange\Component\Ishop\Site\Service\FilterRules;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

/**
 * Класс Helper
 * @since 1.0.0
 */
class IshopfilterHelper
{
	/**
	 * Подготовка полей фильтра
	 *
	 * @return array Ответ
	 * @throws
	 * @since 1.0.0
	 */
	public static function prepareFilter()
	{
		$app = Factory::getApplication();
		$input = $app->getInput();
		$result = [];

		$controller = $input->get('controller');
		if (!$controller) {
			$controller = $input->get('view');
		}

		// Фильтр работает только в категориях товаров
		if ($controller !== 'category' && $input->get('id')) {
			// Если мы не в категории - возвращаем пустой массив
			return $result;
		}

		$categoryModel = Factory::getApplication()
			->bootComponent('com_ishop')
			->getMVCFactory()
			->createModel('Category', 'Site');

		$filter = $categoryModel->getFilterObject();
        $categoryId = $input->getInt('id', 0) ?: (int) $categoryModel->getState('category.id', 0);

        if (!empty($filter) && empty($filter->empty) && $categoryId > 0) {
            $itemId = $input->getInt('Itemid', 0);
            $filters = self::getActiveFilters($filter);
            $availabilityService = new FilterAvailabilityService();

            $filter->total = count($availabilityService->getFilteredProductIds($categoryId, $itemId, $filters));
            $filter->availableOptions = $availabilityService->getAvailableOptions($categoryId, $itemId, $filters);
        }

		return $filter;
	}

    /**
     * Возвращает нормализованные активные значения фильтра из объекта CategoryModel.
     *
     * @param   object  $filter  Объект фильтра категории
     *
     * @return array
     * @since 1.0.0
     */
    private static function getActiveFilters(object $filter): array
    {
        $active = (array) ($filter->active ?? []);

        return FilterRules::normalizeFilterInput([
            'min_price'     => (int) ($active['min_price'] ?? 0),
            'max_price'     => (int) ($active['max_price'] ?? 0),
            'good_price'    => (int) ($active['good_price'] ?? 0),
            'min_width'     => (int) ($active['min_width'] ?? 0),
            'max_width'     => (int) ($active['max_width'] ?? 0),
            'min_height'    => (int) ($active['min_height'] ?? 0),
            'max_height'    => (int) ($active['max_height'] ?? 0),
            'min_depth'     => (int) ($active['min_depth'] ?? 0),
            'max_depth'     => (int) ($active['max_depth'] ?? 0),
            'min_weight'    => (int) ($active['min_weight'] ?? 0),
            'max_weight'    => (int) ($active['max_weight'] ?? 0),
            'manufacturers' => (array) ($active['manufacturers'] ?? []),
            'warehouses'    => (array) ($active['warehouses'] ?? []),
            'ishop_fields'  => (array) ($active['fields'] ?? []),
        ]);
    }

    /**
     * Метод принимающий Ajax запрос
     *
     * @return void
     * @throws \Exception
     * @since 1.1.0
     */
    public static function getAjax()
    {
        $app = Factory::getApplication();
        $input = $app->getInput();

        if (!\Joomla\CMS\Session\Session::checkToken('request')) {
            $app->sendJsonMessage(false, Text::_('JINVALID_TOKEN'), true);
        }

        $app->getLanguage()->load('mod_ishop_filter', JPATH_SITE);

        $categoryId = $input->getInt('category_id', 0);
        if (!$categoryId) {
            $app->sendJsonMessage(false, Text::_('MOD_ISHOP_FILTER_INVALID_CATEGORY'), true);
        }

        $filterData = $input->getArray([
            'min_price'     => ['int', 0],
            'max_price'     => ['int', 0],
            'min_width'     => ['int', 0],
            'max_width'     => ['int', 0],
            'min_height'    => ['int', 0],
            'max_height'    => ['int', 0],
            'min_depth'     => ['int', 0],
            'max_depth'     => ['int', 0],
            'min_weight'    => ['int', 0],
            'max_weight'    => ['int', 0],
            'good_price'    => ['int', 0],
            'manufacturers' => ['array', []],
            'warehouses'    => ['array', []],
            'ishop_fields'  => ['array', []],
        ]);

        $categoryModel = $app->bootComponent('com_ishop')
            ->getMVCFactory()
            ->createModel('Category', 'Site');

        $filterObject = $categoryModel->getFilterObject();

        if (empty($filterObject) || ($filterObject->empty ?? true)) {
            $app->sendJsonMessage(false, Text::_('MOD_ISHOP_FILTER_NO_DATA'), true);
        }

        $availabilityService = new FilterAvailabilityService();
        $filters = FilterRules::normalizeFilterInput($filterData);
        $totalProducts = count($availabilityService->getFilteredProductIds($categoryId, $input->getInt('Itemid', 0), $filters));
        $availableOptions = $availabilityService->getAvailableOptions($categoryId, $input->getInt('Itemid', 0), $filters);

        $response = [
            'success'        => true,
            'productCount'   => $totalProducts,
            'availableOptions' => $availableOptions,
        ];

        $app->sendJsonMessage(true, $response);
    }
}
