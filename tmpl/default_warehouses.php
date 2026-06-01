<?php
/**
 * @package    mod_ishop_filter
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/mod_ishop_filter
 * @copyright  (C) 2026 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

/**
 * Список доступных переменных
 * @var stdClass $module
 * @var Joomla\CMS\Application\CMSApplicationInterface $app
 * @var Joomla\Input\Input $input
 * @var Joomla\Registry\Registry $params
 * @var stdClass $template
 * @var Joomla\CMS\WebAsset\WebAssetManager $wa
 * @var string $captcha
 * @var object $filter
 * @var array $subPanels
 */

$accessLevels = Factory::getApplication()->getIdentity()->getAuthorisedViewLevels();
$warehousesAccess = (int) $params->get('warehouses_access', 0);
?>
<?php if ($warehousesAccess > 0 && in_array($warehousesAccess, $accessLevels)) : ?>
    <?php
    // Добавляем панель с выбором склада
    $subPanels['warehouses']['title'] = Text::_('MOD_ISHOP_FILTER_BY_WAREHOUSES');
    $subPanels['warehouses']['alias'] = 'warehouses';
    ?>
    <span class="nav-link separator" data-panel-target="off-panel-warehouses"><?php echo Text::_('MOD_ISHOP_FILTER_BY_WAREHOUSES'); ?></span>
<?php endif; ?>
