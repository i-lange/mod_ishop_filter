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
     * @throws
     * @since 1.0.0
     */
    public static function getAjax()
    {
        /*if (!Session::checkToken()) {
            self::setResponse(
                'success',
                [],
                Text::_('JINVALID_TOKEN'));
        }

        $app = Factory::getApplication();
        $app->getLanguage()->load('mod_ishop_filter', JPATH_SITE);

        $fields = [];
        $input_data = $app->getInput()->getArray();

        $params = new \StdClass;
        if (isset($input_data['module_id'])) {
            $module = ModuleHelper::getModuleById((string) $input_data['module_id']);
            $params = json_decode($module->params);
        }*/
    }
}