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
<div class="filter-product-count">
    <span class="count-label"><?php echo Text::_('MOD_ISHOP_FILTER_FOUND'); ?>:</span>
    <span class="count-value" data-count="<?php echo $filter->total ?? 0; ?>"><?php echo $filter->total ?? 0; ?></span>
</div>
<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>"
      method="post"
      name="ishop_filter"
      id="<?php echo $formId; ?>">
    <div class="filter-loading-overlay" style="display: none;">
        <div class="filter-loading-spinner">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden"><?php echo Text::_('MOD_ISHOP_FILTER_LOADING'); ?></span>
            </div>
        </div>
    </div>
    <ul class="open">
        <?php if ($params->get('show_prices', 0)) : ?>
            <?php require ModuleHelper::getLayoutPath('mod_ishop_filter', $params->get('layout', 'default') . '_prices'); ?>
        <?php endif; ?>
        <?php if ($params->get('show_sales', 0)) : ?>
            <?php require ModuleHelper::getLayoutPath('mod_ishop_filter', $params->get('layout', 'default') . '_sales'); ?>
        <?php endif; ?>
        <?php if ($params->get('show_warehouses', 0)) : ?>
            <?php require ModuleHelper::getLayoutPath('mod_ishop_filter', $params->get('layout', 'default') . '_warehouses'); ?>
        <?php endif; ?>
        <?php if ($params->get('show_brand', 0) && count($filter->manufacturers) > 1) : ?>
            <?php require ModuleHelper::getLayoutPath('mod_ishop_filter', $params->get('layout', 'default') . '_brands'); ?>
        <?php endif; ?>
        <?php if ($params->get('show_fields', 0) && count($filter->ishop_fields) > 0) : ?>
            <?php require ModuleHelper::getLayoutPath('mod_ishop_filter', $params->get('layout', 'default') . '_fields'); ?>
        <?php endif; ?>
        <?php require ModuleHelper::getLayoutPath('mod_ishop_filter', $params->get('layout', 'default') . '_sizes'); ?>
    </ul>
    <div class="offcanvas-buttons mt-3">
        <button class="btn btn-primary me-2" type="submit"><?php echo Text::_('MOD_ISHOP_FILTER_MODULE_SUBMIT'); ?></button>
        <button class="btn btn-link" type="button" id="ishop-filter-reset"><?php echo Text::_('MOD_ISHOP_FILTER_MODULE_RESET'); ?></button>
    </div>
</form>
