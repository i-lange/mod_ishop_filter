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

$dimensions = [
    'width' => [
        'enabled' => 'show_width',
        'label' => 'MOD_ISHOP_FILTER_BY_WIDTH',
        'min' => 'min_width',
        'max' => 'max_width',
    ],
    'height' => [
        'enabled' => 'show_height',
        'label' => 'MOD_ISHOP_FILTER_BY_HEIGHT',
        'min' => 'min_height',
        'max' => 'max_height',
    ],
    'depth' => [
        'enabled' => 'show_depth',
        'label' => 'MOD_ISHOP_FILTER_BY_DEPTH',
        'min' => 'min_depth',
        'max' => 'max_depth',
    ],
    'weight' => [
        'enabled' => 'show_weight',
        'label' => 'MOD_ISHOP_FILTER_BY_WEIGHT',
        'min' => 'min_weight',
        'max' => 'max_weight',
    ],
];
$main = (array) ($filter->main ?? []);
?>
<?php foreach ($dimensions as $dimensionKey => $dimension) : ?>
    <?php
    $minName = $dimension['min'];
    $maxName = $dimension['max'];
    $baseMinValue = (int) round((float) ($main[$minName] ?? 0));
    $baseMaxValue = (int) round((float) ($main[$maxName] ?? 0));
    $range = (array) ($filter->availableOptions['sizes'][$dimensionKey] ?? []);
    $minValue = isset($range['min']) ? (int) $range['min'] : $baseMinValue;
    $maxValue = isset($range['max']) ? (int) $range['max'] : $baseMaxValue;
    $activeMin = (($filter->active[$minName] ?? 0) > 0) ? (int) round((float) $filter->active[$minName]) : '';
    $activeMax = (($filter->active[$maxName] ?? 0) > 0) ? (int) round((float) $filter->active[$maxName]) : '';
    $show = $params->get($dimension['enabled'], 0) && ($baseMinValue > 0 || $baseMaxValue > 0) && $baseMinValue !== $baseMaxValue;
    ?>
    <?php if ($show) : ?>
        <span><?php echo Text::_($dimension['label']); ?></span>
        <div class="range">
            <div class="range-inputs">
                <div class="input">
                    <input class="form-control range-min"
                           id="<?php echo $minName; ?>"
                           type="number"
                           step="1"
                           min="<?php echo $minValue; ?>"
                           max="<?php echo $maxValue; ?>"
                           name="<?php echo $minName; ?>"
                           placeholder="<?php echo $minValue; ?>"
                           value="<?php echo $activeMin; ?>">
                    <label class="form-label input__hint" for="<?php echo $minName; ?>"><?php echo Text::_('MOD_ISHOP_FILTER_BY_PRICE_FROM'); ?></label>
                </div>
                <div class="input">
                    <input class="form-control range-max"
                           id="<?php echo $maxName; ?>"
                           type="number"
                           step="1"
                           min="<?php echo $minValue; ?>"
                           max="<?php echo $maxValue; ?>"
                           name="<?php echo $maxName; ?>"
                           placeholder="<?php echo $maxValue; ?>"
                           value="<?php echo $activeMax; ?>">
                    <label class="form-label input__hint" for="<?php echo $maxName; ?>"><?php echo Text::_('MOD_ISHOP_FILTER_BY_PRICE_TO'); ?></label>
                </div>
            </div>
            <div class="range-slider">
                <div class="range-slider__line"></div>
                <div class="range-slider__point range-slider__point--upper"></div>
                <div class="range-slider__point range-slider__point--lower"></div>
            </div>
        </div>
    <?php endif; ?>
<?php endforeach; ?>
