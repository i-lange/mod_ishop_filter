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
?>
<?php foreach ($filter->ishop_fields as $field) : ?>
    <?php $fieldType = (int) $field->type; ?>
    <?php if ($fieldType === 0) : // Числовые значение ?>
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
    <?php elseif ($fieldType === 2) : // Да или Нет ?>
        <?php
        $checked = '';
        $checkedBool = isset($filter->active['fields'][$field->id]) && (int) $filter->active['fields'][$field->id] === 1;
        $enabled = $checkedBool || isset($filter->availableOptions['ishop_fields'][$field->id]);

        if ($checkedBool) {
            $checked = 'checked';
        }
        ?>
        <div class="nav-link">
            <input type="hidden" name="ishop_fields[<?php echo (int) $field->id; ?>]" value="0">
            <div class="form-check form-switch<?php echo $enabled ? '' : ' filter-option-disabled'; ?>">
                <input class="form-check-input"
                       type="checkbox"
                       role="switch"
                       id="ishop_fields_<?php echo (int) $field->id; ?>_bool"
                       name="ishop_fields[<?php echo (int) $field->id; ?>]"
                       value="1" <?php echo $checked; ?><?php echo $enabled ? '' : ' disabled'; ?>>
                <label class="form-check-label<?php echo $enabled ? '' : ' disabled'; ?>" for="ishop_fields_<?php echo (int) $field->id; ?>_bool"><?php echo htmlspecialchars((string) $field->title, ENT_COMPAT, 'UTF-8'); ?></label>
            </div>
        </div>
    <?php else : // Строковые из списка ?>
        <?php
        $valueIds = explode('||', (string) $field->values_id);
        $valueTitles = explode('||', (string) $field->values);
        $values = count($valueIds) === count($valueTitles) ? array_combine($valueIds, $valueTitles) : [];

        // Не нужно выводить, если выбор из одного варианта
        if (count($values) <= 1) {
            continue;
        }

        // Добавляем панель с выбором значений характеристики
        $subPanels[$field->id]['title'] = (string) $field->title;
        $subPanels[$field->id]['alias'] = (string) (($field->alias ?? '') ?: 'field-' . $field->id);
        $subPanels[$field->id]['values'] = $values;
        ?>
        <span class="nav-link separator" data-panel-target="off-panel-<?php echo htmlspecialchars($subPanels[$field->id]['alias'], ENT_COMPAT, 'UTF-8'); ?>"><?php echo htmlspecialchars((string) $field->title, ENT_COMPAT, 'UTF-8'); ?></span>
    <?php endif; ?>
<?php endforeach; ?>
