<?php
/**
 * @package    mod_ishop_filter
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/mod_ishop_filter
 * @copyright  (C) 2026 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

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

// Добавляем панель с выбором бренда
$subPanels['manufacturers']['title'] = Text::_('MOD_ISHOP_FILTER_BY_BRAND');
$subPanels['manufacturers']['alias'] = 'manufacturers';
?>
<span class="nav-link separator" data-panel-target="off-panel-manufacturers"><?php echo Text::_('MOD_ISHOP_FILTER_BY_BRAND'); ?></span>
