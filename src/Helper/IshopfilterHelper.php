<?php
/**
 * @package    mod_ishop_filter
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/mod_ishop_filter
 * @copyright  (C) 2026 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

namespace Ilange\Module\Ishopfilter\Site\Helper;

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

		return $categoryModel->getFilterObject();
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

        $totalProducts = $filterObject->total ?? 0;

        $availableOptions = [
            'manufacturers' => isset($filterObject->manufacturers) ? array_column((array) $filterObject->manufacturers, 'id') : [],
            'warehouses'    => isset($filterObject->warehouses) ? array_map(fn($w) => $w->id ?? null, (array) $filterObject->warehouses) : [],
            'ishop_fields'  => [],
            'price_range'   => [
                'min' => $filterObject->main->min_price ?? 0,
                'max' => $filterObject->main->max_price ?? 0,
            ],
            'sizes'         => [
                'width'  => [
                    'min' => $filterObject->main->min_width ?? 0,
                    'max' => $filterObject->main->max_width ?? 0,
                ],
                'height' => [
                    'min' => $filterObject->main->min_height ?? 0,
                    'max' => $filterObject->main->max_height ?? 0,
                ],
                'depth'  => [
                    'min' => $filterObject->main->min_depth ?? 0,
                    'max' => $filterObject->main->max_depth ?? 0,
                ],
                'weight' => [
                    'min' => $filterObject->main->min_weight ?? 0,
                    'max' => $filterObject->main->max_weight ?? 0,
                ],
            ],
        ];

        if (!empty($filterObject->ishop_fields)) {
            foreach ($filterObject->ishop_fields as $field) {
                $fieldId = $field->id ?? null;
                if ($fieldId === null) {
                    continue;
                }

                $fieldType = $field->type ?? 0;

                if ($fieldType === 0) {
                    $values = explode(',', $field->values ?? '0,0');
                    $availableOptions['ishop_fields'][$fieldId] = [
                        'type' => 'range',
                        'min'  => (float) ($values[0] ?? 0),
                        'max'  => (float) ($values[1] ?? 0),
                    ];
                } elseif ($fieldType === 1) {
                    $values = explode('||', $field->values ?? '');
                    $valuesId = explode('||', $field->values_id ?? '');
                    $availableOptions['ishop_fields'][$fieldId] = [
                        'type'   => 'list',
                        'values' => array_combine($valuesId, $values),
                    ];
                } elseif ($fieldType === 2) {
                    $availableOptions['ishop_fields'][$fieldId] = [
                        'type' => 'boolean',
                    ];
                }
            }
        }

        $response = [
            'success'        => true,
            'productCount'   => $totalProducts,
            'availableOptions' => $availableOptions,
        ];

        $app->sendJsonMessage(true, $response);
    }
}