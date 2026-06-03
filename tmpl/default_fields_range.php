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
<?php foreach ($filter->ishop_fields as $field) : ?>
    <?php if ((int) $field->type !== 0) : ?>
        <?php continue; ?>
    <?php endif; ?>
    <?php
    [$min, $max] = explode(',', $field->values);
    if ($min == $max) {
        continue;
    }

    $range = (array) ($filter->availableOptions['ishop_fields'][$field->id] ?? []);
    $min = isset($range['min']) ? (int) $range['min'] : (int) round((float) $min, 0, PHP_ROUND_HALF_DOWN);
    $max = isset($range['max']) ? (int) $range['max'] : (int) round((float) $max, 0, PHP_ROUND_HALF_UP);

    if (isset($filter->active['fields'][$field->id]['min']) && is_numeric($filter->active['fields'][$field->id]['min'])) {
        $tmpMin = (int) round((float) $filter->active['fields'][$field->id]['min']);
    } else {
        $tmpMin = '';
    }

    if (isset($filter->active['fields'][$field->id]['max']) && is_numeric($filter->active['fields'][$field->id]['max'])) {
        $tmpMax = (int) round((float) $filter->active['fields'][$field->id]['max']);
    } else {
        $tmpMax = '';
    }

    $fieldTitle = htmlspecialchars((string) $field->title, ENT_COMPAT, 'UTF-8');
    $fieldUnit = htmlspecialchars((string) $field->unit, ENT_COMPAT, 'UTF-8');
    ?>
    <span><?php echo $fieldTitle; ?><?php echo (empty($field->unit)) ? '' : ', ' . $fieldUnit; ?>:</span>
    <div class="range">
        <div class="range-inputs">
            <div class="input">
                <input type="number"
                       class="form-control range-min"
                       id="ishop_fields_<?php echo (int) $field->id; ?>_from"
                       step="1"
                       min="<?php echo $min; ?>"
                       max="<?php echo $max; ?>"
                       name="ishop_fields[<?php echo (int) $field->id; ?>][min]"
                       data-filter-label="<?php echo $fieldTitle; ?><?php echo (empty($field->unit)) ? '' : ', ' . $fieldUnit; ?>"
                       placeholder="<?php echo $min; ?>"
                       value="<?php echo $tmpMin; ?>">
                <label class="form-label input__hint" for="ishop_fields_<?php echo (int) $field->id; ?>_from"><?php echo Text::_('MOD_ISHOP_FILTER_BY_PRICE_FROM'); ?></label>
            </div>
            <div class="input">
                <input type="number"
                       class="form-control range-max"
                       id="ishop_fields_<?php echo (int) $field->id; ?>_to"
                       step="1"
                       min="<?php echo $min; ?>"
                       max="<?php echo $max; ?>"
                       name="ishop_fields[<?php echo (int) $field->id; ?>][max]"
                       data-filter-label="<?php echo $fieldTitle; ?><?php echo (empty($field->unit)) ? '' : ', ' . $fieldUnit; ?>"
                       placeholder="<?php echo $max; ?>"
                       value="<?php echo $tmpMax; ?>">
                <label class="form-label input__hint" for="ishop_fields_<?php echo (int) $field->id; ?>_to"><?php echo Text::_('MOD_ISHOP_FILTER_BY_PRICE_TO'); ?></label>
            </div>
        </div>
        <div class="range-slider">
            <div class="range-slider__line"></div>
            <div class="range-slider__point range-slider__point--upper"></div>
            <div class="range-slider__point range-slider__point--lower"></div>
        </div>
    </div>
<?php endforeach; ?>
