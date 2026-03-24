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
?>
<li>
    <span><?php echo Text::_('MOD_ISHOP_FILTER_BY_PRICE'); ?></span>
    <div class="range">
        <div class="range-inputs">
            <div class="input">
                <input class="form-control range-min"
                       id="min_price"
                       type="number"
                       min="<?php echo (int) $filter->main->min_price; ?>"
                       max="<?php echo (int) $filter->main->max_price; ?>"
                       name="min_price"
                       placeholder="<?php echo (int) $filter->main->min_price; ?>"
                       value="<?php echo ($filter->active['min_price'] > 0) ? (int) $filter->active['min_price'] : ''; ?>">
                <label class="form-label input__hint" for="min_price"><?php echo Text::_('MOD_ISHOP_FILTER_BY_PRICE_FROM'); ?></label>
            </div>
            <div class="input">
                <input class="form-control range-max"
                       id="max_price"
                       type="number"
                       min="<?php echo (int) $filter->main->min_price; ?>"
                       max="<?php echo (int) $filter->main->max_price ?>"
                       name="max_price"
                       placeholder="<?php echo (int) $filter->main->max_price; ?>"
                       value="<?php echo ($filter->active['max_price'] > 0) ? (int) $filter->active['max_price'] : ''; ?>">
                <label class="form-label input__hint" for="max_price"><?php echo Text::_('MOD_ISHOP_FILTER_BY_PRICE_TO'); ?></label>
            </div>
        </div>
        <div class="range-slider">
            <div class="range-slider__line"></div>
            <div class="range-slider__point range-slider__point--upper"></div>
            <div class="range-slider__point range-slider__point--lower"></div>
        </div>
    </div>
</li>