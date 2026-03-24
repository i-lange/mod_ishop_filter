<?php
/**
 * @package    mod_ishop_filter
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/mod_ishop_filter
 * @copyright  (C) 2026 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

namespace Ilange\Module\Ishopfilter\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

defined('_JEXEC') or die;

/**
 * Класс распаковщик
 * @since 1.0.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Возвращает данные для отображения
     * Метод родителя возвращает 'module', 'app', 'input', 'params', 'template'
     * @return array
     * @since 1.0.0
     */
    protected function getLayoutData()
    {
	    $data = parent::getLayoutData();

	    $helper = $data['app']->bootModule('mod_ishop_filter', 'Site')->getHelper('IshopfilterHelper');
	    $data['filter'] = $helper->prepareFilter();

	    $wa = $data['app']->getDocument()->getWebAssetManager();
	    $wa->getRegistry()->addRegistryFile('media/mod_ishop_filter/joomla.asset.json');
	    $data['wa'] = $wa;

	    return $data;
    }
}
