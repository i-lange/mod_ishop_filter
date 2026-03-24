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
<?php if ($params->get('show_width', 0) && ($filter->main->min_width > 0 || $filter->main->max_width > 0)) : ?>
    <li>
        <span><?php echo Text::_('MOD_ISHOP_FILTER_BY_WIDTH'); ?></span>
        <div class="range">
            <div class="range-inputs">
                <div class="input">
                    <input class="form-control range-min"
                           id="min_width"
                           type="number"
                           min="<?php echo (int) $filter->main->min_width; ?>"
                           max="<?php echo (int) $filter->main->max_width; ?>"
                           name="min_width"
                           placeholder="<?php echo (int) $filter->main->min_width; ?>"
                           value="<?php echo ($filter->active['min_width'] > 0) ? (int) $filter->active['min_width'] : ''; ?>">
                    <label class="form-label input__hint" for="min_width"><?php echo Text::_('MOD_ISHOP_FILTER_BY_PRICE_FROM'); ?></label>
                </div>
                <div class="input">
                    <input class="form-control range-max"
                           id="max_width"
                           type="number"
                           min="<?php echo (int) $filter->main->min_width; ?>"
                           max="<?php echo (int) $filter->main->max_width ?>"
                           name="max_width"
                           placeholder="<?php echo (int) $filter->main->max_width; ?>"
                           value="<?php echo ($filter->active['max_width'] > 0) ? (int) $filter->active['max_width'] : ''; ?>">
                    <label class="form-label input__hint" for="max_width"><?php echo Text::_('MOD_ISHOP_FILTER_BY_PRICE_TO'); ?></label>
                </div>
            </div>
            <div class="range-slider">
                <div class="range-slider__line"></div>
                <div class="range-slider__point range-slider__point--upper"></div>
                <div class="range-slider__point range-slider__point--lower"></div>
            </div>
        </div>
    </li>
<?php endif; ?>
<?php if ($params->get('show_height', 0) && ($filter->main->min_height > 0 || $filter->main->max_height > 0)) : ?>
    <li>
        <span><?php echo Text::_('MOD_ISHOP_FILTER_BY_HEIGHT'); ?></span>
        <div class="range">
            <div class="range-inputs">
                <div class="input">
                    <input class="form-control range-min"
                           id="min_height"
                           type="number"
                           min="<?php echo (int) $filter->main->min_height; ?>"
                           max="<?php echo (int) $filter->main->max_height; ?>"
                           name="min_height"
                           placeholder="<?php echo (int) $filter->main->min_height; ?>"
                           value="<?php echo ($filter->active['min_height'] > 0) ? (int) $filter->active['min_height'] : ''; ?>">
                    <label class="form-label input__hint" for="min_height"><?php echo Text::_('MOD_ISHOP_FILTER_BY_PRICE_FROM'); ?></label>
                </div>
                <div class="input">
                    <input class="form-control range-max"
                           id="max_height"
                           type="number"
                           min="<?php echo (int) $filter->main->min_height; ?>"
                           max="<?php echo (int) $filter->main->max_height ?>"
                           name="max_height"
                           placeholder="<?php echo (int) $filter->main->max_height; ?>"
                           value="<?php echo ($filter->active['max_height'] > 0) ? (int) $filter->active['max_height'] : ''; ?>">
                    <label class="form-label input__hint" for="max_height"><?php echo Text::_('MOD_ISHOP_FILTER_BY_PRICE_TO'); ?></label>
                </div>
            </div>
            <div class="range-slider">
                <div class="range-slider__line"></div>
                <div class="range-slider__point range-slider__point--upper"></div>
                <div class="range-slider__point range-slider__point--lower"></div>
            </div>
        </div>
    </li>
<?php endif; ?>
<?php if ($params->get('show_depth', 0) && ($filter->main->min_depth > 0 || $filter->main->max_depth > 0)) : ?>
    <li>
        <span><?php echo Text::_('MOD_ISHOP_FILTER_BY_DEPTH'); ?></span>
        <div class="range">
            <div class="range-inputs">
                <div class="input">
                    <input class="form-control range-min"
                           id="min_depth"
                           type="number"
                           min="<?php echo (int) $filter->main->min_depth; ?>"
                           max="<?php echo (int) $filter->main->max_depth; ?>"
                           name="min_depth"
                           placeholder="<?php echo (int) $filter->main->min_depth; ?>"
                           value="<?php echo ($filter->active['min_depth'] > 0) ? (int) $filter->active['min_depth'] : ''; ?>">
                    <label class="form-label input__hint" for="min_depth"><?php echo Text::_('MOD_ISHOP_FILTER_BY_PRICE_FROM'); ?></label>
                </div>
                <div class="input">
                    <input class="form-control range-max"
                           id="max_depth"
                           type="number"
                           min="<?php echo (int) $filter->main->min_depth; ?>"
                           max="<?php echo (int) $filter->main->max_depth ?>"
                           name="max_depth"
                           placeholder="<?php echo (int) $filter->main->max_depth; ?>"
                           value="<?php echo ($filter->active['max_depth'] > 0) ? (int) $filter->active['max_depth'] : ''; ?>">
                    <label class="form-label input__hint" for="max_depth"><?php echo Text::_('MOD_ISHOP_FILTER_BY_PRICE_TO'); ?></label>
                </div>
            </div>
            <div class="range-slider">
                <div class="range-slider__line"></div>
                <div class="range-slider__point range-slider__point--upper"></div>
                <div class="range-slider__point range-slider__point--lower"></div>
            </div>
        </div>
    </li>
<?php endif; ?>
<?php if ($params->get('show_weight', 0) && ($filter->main->min_weight > 0 || $filter->main->max_weight > 0)) : ?>
    <li>
        <span><?php echo Text::_('MOD_ISHOP_FILTER_BY_WEIGHT'); ?></span>
        <div class="range">
            <div class="range-inputs">
                <div class="input">
                    <input class="form-control range-min"
                           id="min_weight"
                           type="number"
                           min="<?php echo (int) $filter->main->min_weight; ?>"
                           max="<?php echo (int) $filter->main->max_weight; ?>"
                           name="min_weight"
                           placeholder="<?php echo (int) $filter->main->min_depth; ?>"
                           value="<?php echo ($filter->active['min_weight'] > 0) ? (int) $filter->active['min_weight'] : ''; ?>">
                    <label class="form-label input__hint" for="min_weight"><?php echo Text::_('MOD_ISHOP_FILTER_BY_PRICE_FROM'); ?></label>
                </div>
                <div class="input">
                    <input class="form-control range-max"
                           id="max_weight"
                           type="number"
                           min="<?php echo (int) $filter->main->min_weight; ?>"
                           max="<?php echo (int) $filter->main->max_weight ?>"
                           name="max_weight"
                           placeholder="<?php echo (int) $filter->main->max_weight; ?>"
                           value="<?php echo ($filter->active['max_weight'] > 0) ? (int) $filter->active['max_weight'] : ''; ?>">
                    <label class="form-label input__hint" for="max_weight"><?php echo Text::_('MOD_ISHOP_FILTER_BY_PRICE_TO'); ?></label>
                </div>
            </div>
            <div class="range-slider">
                <div class="range-slider__line"></div>
                <div class="range-slider__point range-slider__point--upper"></div>
                <div class="range-slider__point range-slider__point--lower"></div>
            </div>
        </div>
    </li>
<?php endif; ?>
