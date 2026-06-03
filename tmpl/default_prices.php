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
 */

$priceRange = (array) ($filter->availableOptions['price_range'] ?? []);
$main = (array) ($filter->main ?? []);
$minPrice = isset($priceRange['min']) ? (int) $priceRange['min'] : (int) round((float) ($main['min_price'] ?? 0));
$maxPrice = isset($priceRange['max']) ? (int) $priceRange['max'] : (int) round((float) ($main['max_price'] ?? 0));
$activeMinPrice = (($filter->active['min_price'] ?? 0) > 0) ? (int) round((float) $filter->active['min_price']) : '';
$activeMaxPrice = (($filter->active['max_price'] ?? 0) > 0) ? (int) round((float) $filter->active['max_price']) : '';
?>
<span><?php echo Text::_('MOD_ISHOP_FILTER_BY_PRICE'); ?></span>
<div class="range">
    <div class="range-inputs">
        <div class="input">
            <input class="form-control range-min"
                   id="min_price"
                   type="number"
                   step="1"
                   min="<?php echo $minPrice; ?>"
                   max="<?php echo $maxPrice; ?>"
                   name="min_price"
                   data-filter-label="<?php echo htmlspecialchars(Text::_('MOD_ISHOP_FILTER_BY_PRICE'), ENT_COMPAT, 'UTF-8'); ?>"
                   placeholder="<?php echo $minPrice; ?>"
                   value="<?php echo $activeMinPrice; ?>">
            <label class="form-label input__hint" for="min_price"><?php echo Text::_('MOD_ISHOP_FILTER_BY_PRICE_FROM'); ?></label>
        </div>
        <div class="input">
            <input class="form-control range-max"
                   id="max_price"
                   type="number"
                   step="1"
                   min="<?php echo $minPrice; ?>"
                   max="<?php echo $maxPrice; ?>"
                   name="max_price"
                   data-filter-label="<?php echo htmlspecialchars(Text::_('MOD_ISHOP_FILTER_BY_PRICE'), ENT_COMPAT, 'UTF-8'); ?>"
                   placeholder="<?php echo $maxPrice; ?>"
                   value="<?php echo $activeMaxPrice; ?>">
            <label class="form-label input__hint" for="max_price"><?php echo Text::_('MOD_ISHOP_FILTER_BY_PRICE_TO'); ?></label>
        </div>
    </div>
    <div class="range-slider">
        <div class="range-slider__line"></div>
        <div class="range-slider__point range-slider__point--upper"></div>
        <div class="range-slider__point range-slider__point--lower"></div>
    </div>
</div>
