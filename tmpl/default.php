<?php
/**
 * @package    mod_ishop_filter
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/mod_ishop_filter
 * @copyright  (C) 2026 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
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

if (empty($filter) || $filter->empty) {
    return;
}

$wa->useScript('bootstrap.offcanvas');

if ($params->get('use_css', 1)) {
    $wa->useStyle('ishop_filter.front');
    $wa->useStyle('ishop_filter.range');
}

if ($params->get('use_js', 1)) {
    $wa->useScript('ishop_filter.front');
    $wa->useScript('ishop_filter.range');
}

$formId = 'i-filter-' . $module->id;
$categoryId = $input->getInt('id', 0);
$itemId = $input->getInt('Itemid', 0);
$rootUrl = rtrim(Uri::root(true), '/');
$endpointRoot = ($rootUrl === '' ? '' : $rootUrl) . '/index.php?option=com_ishop&task=filter.';
$previewUrl = $endpointRoot . 'preview&format=json';
$resetUrl = $endpointRoot . 'reset&format=json';
$filterTitle = Text::_('MOD_ISHOP_FILTER_MODULE_FILTERS');
$closeText = Text::_('MOD_ISHOP_FILTER_CLOSE');
$backText = Text::_('MOD_ISHOP_FILTER_BACK');
$siteName = Text::_('TPL_ITHEME_SITENAME');
$productCount = (int) ($filter->total ?? 0);
$submitUnavailable = $productCount === 0;
$submitText = $submitUnavailable
    ? Text::_('MOD_ISHOP_FILTER_MODULE_SUBMIT_UNAVAILABLE')
    : Text::sprintf('MOD_ISHOP_FILTER_MODULE_SUBMIT_COUNT', $productCount);
$submitUnavailableText = Text::_('MOD_ISHOP_FILTER_MODULE_SUBMIT_UNAVAILABLE');
$submitUnavailableHint = Text::_('MOD_ISHOP_FILTER_MODULE_SUBMIT_UNAVAILABLE_HINT');
$availableOptions = (array) ($filter->availableOptions ?? []);
$isAvailableOption = static function ($availableIds, int $valueId, bool $checked): bool {
    if ($checked || $availableIds === null) {
        return true;
    }

    return in_array($valueId, array_map('intval', (array) $availableIds), true);
};
$getFieldAvailableValues = static function (array $availableOptions, int $fieldId): ?array {
    $fields = (array) ($availableOptions['ishop_fields'] ?? []);

    if (empty($fields)) {
        return null;
    }

    if (!isset($fields[$fieldId]) || ($fields[$fieldId]['type'] ?? '') !== 'list') {
        return [];
    }

    return array_keys((array) ($fields[$fieldId]['values'] ?? []));
};

if ($siteName === 'TPL_ITHEME_SITENAME') {
    $siteName = $app->get('sitename', Text::_('MOD_ISHOP_FILTER_MODULE_TITLE'));
}

// Массив дочерних панелей
$subPanels = [];
?>
<div class="offcanvas offcanvas-end"
     tabindex="-1"
     id="moduleFilter"
     aria-labelledby="moduleFilterLabel"
     data-offcanvas-panels>
    <div class="offcanvas-header border-bottom">
        <div class="offcanvas-title-wrap">
            <?php
            try {
                echo LayoutHelper::render('itheme.logo', ['class' => 'offcanvas-logo', 'alt' => $siteName]);
            } catch (\Throwable $e) {
                // Layout is provided by tpl_itheme; keep the module usable without it.
            }
            ?>
            <h3 class="offcanvas-title" id="moduleFilterLabel"><?php echo $filterTitle; ?></h3>
        </div>
        <button type="button"
                class="btn-close"
                data-bs-dismiss="offcanvas"
                aria-label="<?php echo $closeText; ?>"></button>
    </div>
    <div class="offcanvas-body">
        <form class="menu-viewport"
              data-menu-viewport
              action="<?php echo htmlspecialchars(Uri::getInstance()->toString(), ENT_COMPAT, 'UTF-8'); ?>"
              method="post"
              name="ishop_filter"
              id="<?php echo $formId; ?>"
              data-category-id="<?php echo $categoryId; ?>"
              data-item-id="<?php echo $itemId; ?>"
              data-product-count="<?php echo $productCount; ?>"
              data-preview-url="<?php echo htmlspecialchars($previewUrl, ENT_COMPAT, 'UTF-8'); ?>"
              data-reset-url="<?php echo htmlspecialchars($resetUrl, ENT_COMPAT, 'UTF-8'); ?>"
              data-submit-template="<?php echo htmlspecialchars(Text::_('MOD_ISHOP_FILTER_MODULE_SUBMIT_COUNT'), ENT_COMPAT, 'UTF-8'); ?>"
              data-submit-unavailable-text="<?php echo htmlspecialchars($submitUnavailableText, ENT_COMPAT, 'UTF-8'); ?>">
            <div class="filter-loading-overlay" style="display: none;">
                <div class="filter-loading-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden"><?php echo Text::_('MOD_ISHOP_FILTER_LOADING'); ?></span>
                    </div>
                </div>
            </div>
            <section class="menu-panel is-active" id="off-panel-filter" data-panel data-title="<?php echo htmlspecialchars($filterTitle, ENT_COMPAT, 'UTF-8'); ?>" data-root>
                <nav class="nav flex-column">
                    <?php if ($params->get('show_prices', 0)) : ?>
                        <?php require ModuleHelper::getLayoutPath('mod_ishop_filter', $params->get('layout', 'default') . '_prices'); ?>
                    <?php endif; ?>
                    <?php require ModuleHelper::getLayoutPath('mod_ishop_filter', $params->get('layout', 'default') . '_sizes'); ?>
                    <?php if ($params->get('show_fields', 0) && count($filter->ishop_fields ?? []) > 0) : ?>
                        <?php require ModuleHelper::getLayoutPath('mod_ishop_filter', $params->get('layout', 'default') . '_fields_range'); ?>
                    <?php endif; ?>
                    <?php if ($params->get('show_sales', 0)) : ?>
                        <?php require ModuleHelper::getLayoutPath('mod_ishop_filter', $params->get('layout', 'default') . '_sales'); ?>
                    <?php endif; ?>
                    <?php if ($params->get('show_fields', 0) && count($filter->ishop_fields ?? []) > 0) : ?>
                        <?php require ModuleHelper::getLayoutPath('mod_ishop_filter', $params->get('layout', 'default') . '_fields_switch'); ?>
                    <?php endif; ?>
                    <?php if ($params->get('show_warehouses', 0)) : ?>
                        <?php require ModuleHelper::getLayoutPath('mod_ishop_filter', $params->get('layout', 'default') . '_warehouses'); ?>
                    <?php endif; ?>
                    <?php if ($params->get('show_brand', 0) && count($filter->manufacturers ?? []) > 1) : ?>
                        <?php require ModuleHelper::getLayoutPath('mod_ishop_filter', $params->get('layout', 'default') . '_brands'); ?>
                    <?php endif; ?>
                    <?php if ($params->get('show_fields', 0) && count($filter->ishop_fields ?? []) > 0) : ?>
                        <?php require ModuleHelper::getLayoutPath('mod_ishop_filter', $params->get('layout', 'default') . '_fields_list'); ?>
                    <?php endif; ?>
                </nav>
            </section>
            <?php foreach ($subPanels as $id => $panel) : ?>
                <?php $noFields = ['warehouses', 'manufacturers']; ?>
                <section class="menu-panel"
                         id="off-panel-<?php echo htmlspecialchars($panel['alias'], ENT_COMPAT, 'UTF-8'); ?>"
                         data-panel
                         data-title="<?php echo htmlspecialchars($panel['title'], ENT_COMPAT, 'UTF-8'); ?>"
                         data-parent="off-panel-filter"
                         aria-hidden="true">
                    <button class="btn btn-back" type="button" data-panel-back aria-label="<?php echo $backText; ?>"><span><?php echo $backText; ?></span></button>
                    <?php if (in_array($id, $noFields, true)) : ?>
                        <input type="hidden" name="<?php echo $id; ?>[]" value="0">
                        <?php foreach ($filter->$id as $variant) : ?>
                            <?php
                            $variantId = is_array($variant) ? (int) ($variant['id'] ?? 0) : (int) ($variant->id ?? 0);
                            $variantTitle = is_array($variant) ? (string) ($variant['title'] ?? '') : (string) ($variant->title ?? '');
                            $activeValues = array_map('intval', (array) ($filter->active[$id] ?? []));
                            $checked = '';
                            $checkedBool = in_array($variantId, $activeValues, true);
                            $enabled = $isAvailableOption($availableOptions[$id] ?? null, $variantId, $checkedBool);

                            if ($checkedBool) {
                                $checked = 'checked';
                            }
                            ?>
                            <div class="form-check<?php echo $enabled ? '' : ' filter-option-disabled'; ?>">
                                <input class="form-check-input"
                                       id="<?php echo $id, '-', $variantId; ?>"
                                       type="checkbox"
                                       name="<?php echo $id; ?>[]"
                                       value="<?php echo $variantId; ?>" <?php echo $checked; ?><?php echo $enabled ? '' : ' disabled'; ?>>
                                <label class="form-check-label<?php echo $enabled ? '' : ' disabled'; ?>"
                                       for="<?php echo $id, '-', $variantId; ?>"><?php echo htmlspecialchars($variantTitle, ENT_COMPAT, 'UTF-8'); ?></label>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <input type="hidden" name="ishop_fields[<?php echo (int) $id; ?>][]" value="0">
                        <?php foreach ($panel['values'] as $value_id => $value) : ?>
                            <?php
                            $checked = '';
                            $valueId = (int) $value_id;
                            $fieldId = (int) $id;
                            $checkedBool = isset($filter->active['fields'][$id]) && in_array($value_id, $filter->active['fields'][$id]);
                            $enabled = $isAvailableOption($getFieldAvailableValues($availableOptions, $fieldId), $valueId, $checkedBool);

                            if ($checkedBool) {
                                $checked = 'checked';
                            }
                            ?>
                            <div class="form-check<?php echo $enabled ? '' : ' filter-option-disabled'; ?>">
                                <input class="form-check-input"
                                       id="value-<?php echo $fieldId . '-' . $valueId; ?>"
                                       type="checkbox"
                                       name="ishop_fields[<?php echo $fieldId; ?>][]"
                                       value="<?php echo $valueId; ?>" <?php echo $checked; ?><?php echo $enabled ? '' : ' disabled'; ?>>
                                <label class="form-check-label<?php echo $enabled ? '' : ' disabled'; ?>"
                                       for="value-<?php echo $fieldId . '-' . $valueId; ?>">
                                    <?php echo htmlspecialchars((string) $value, ENT_COMPAT, 'UTF-8'); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </section>
            <?php endforeach; ?>
            <?php echo HTMLHelper::_('form.token'); ?>
        </form>
    </div>
    <div class="offcanvas-footer border-top">
        <div class="filter-submit-wrap me-2">
            <button class="btn btn-primary"
                    type="submit"
                    form="<?php echo $formId; ?>"
                    data-filter-submit
                    aria-describedby="ishop-filter-submit-hint-<?php echo (int) $module->id; ?>"
                    aria-disabled="<?php echo $submitUnavailable ? 'true' : 'false'; ?>"<?php echo $submitUnavailable ? ' disabled' : ''; ?>>
                <span data-filter-submit-text><?php echo $submitText; ?></span>
            </button>
            <div class="filter-submit-hint"
                 id="ishop-filter-submit-hint-<?php echo (int) $module->id; ?>"
                 data-filter-submit-hint
                 <?php echo $submitUnavailable ? '' : 'hidden'; ?>><?php echo $submitUnavailableHint; ?></div>
        </div>
        <button class="btn btn-link"
                type="button"
                form="<?php echo $formId; ?>"
                id="ishop-filter-reset-<?php echo (int) $module->id; ?>"
                data-filter-reset><?php echo Text::_('MOD_ISHOP_FILTER_MODULE_RESET'); ?></button>
    </div>
</div>
