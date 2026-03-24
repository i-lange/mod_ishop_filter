<?php
/**
 * @package    mod_ishop_filter
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/mod_ishop_filter
 * @copyright  (C) 2026 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

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
 */

if ($filter->empty) {
    return;
}

$formId = 'i-filter-' . $module->id;
?>
<div class="offcanvas offcanvas_right offcanvas_filter offcanvas_light" id="smartfilter">
    <form class="offcanvas_cnt"
          action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>"
          method="post"
          name="ishop_filter">
        <nav class="main" data-title="<?php echo Text::_('MOD_ISHOP_FILTER_MODULE_TITLE'); ?>">
            <span class="back"><?php echo Text::_('MOD_ISHOP_FILTER_MODULE_TITLE'); ?></span>
            <ul class="open">
                <?php if ($params->get('show_prices', 0)) : ?>
                    <?php require ModuleHelper::getLayoutPath('mod_ishop_filter', $params->get('layout', 'default') . '_prices'); ?>
                <?php endif; ?>
                <?php if ($params->get('show_sales', 0)) : ?>
	                <?php require ModuleHelper::getLayoutPath('mod_ishop_filter', $params->get('layout', 'default') . '_sales'); ?>
                <?php endif; ?>
                <?php require ModuleHelper::getLayoutPath('mod_ishop_filter', $params->get('layout', 'default') . '_warehouses'); ?>
                <?php if ($params->get('show_brand', 0) && count($filter->manufacturers) > 1) : ?>
                    <?php require ModuleHelper::getLayoutPath('mod_ishop_filter', $params->get('layout', 'default') . '_brands'); ?>
                <?php endif; ?>
                <?php if ($params->get('show_fields', 0) && count($filter->ishop_fields) > 0) : ?>
                    <?php require ModuleHelper::getLayoutPath('mod_ishop_filter', $params->get('layout', 'default') . '_fields'); ?>
                <?php endif; ?>
                <?php require ModuleHelper::getLayoutPath('mod_ishop_filter', $params->get('layout', 'default') . '_sizes'); ?>
            </ul>
            <p class="offcanvas_buttons">
                <button class="btn" type="submit"><?php echo Text::_('MOD_ISHOP_FILTER_MODULE_SUBMIT'); ?></button>
                <button class="btn btn-link" type="button" onclick="iFilterClear('ishop_filter');"><?php echo Text::_('MOD_ISHOP_FILTER_MODULE_RESET'); ?></button>
            </p>
        </nav>
    </form>
    <div class="offcanvas_bg"></div>
</div>