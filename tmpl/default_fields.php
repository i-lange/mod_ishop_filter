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
	<?php if ($field->type === 0) : // Числовые значение?>
		<?php
        [$min, $max] = explode(',', $field->values);
        if ($min == $max) {
            continue;
        }

        $min = round($min, 0, PHP_ROUND_HALF_DOWN);
        $max = round($max, 0, PHP_ROUND_HALF_UP);

		if (isset($filter->active['fields'][$field->id]['min']) && is_numeric($filter->active['fields'][$field->id]['min'])) {
			$tmp_min = $filter->active['fields'][$field->id]['min'];
		} else {
			$tmp_min = '';
		}

		if (isset($filter->active['fields'][$field->id]['max']) && is_numeric($filter->active['fields'][$field->id]['max'])) {
			$tmp_max = $filter->active['fields'][$field->id]['max'];
		} else {
			$tmp_max = '';
		}
		?>
        <li>
            <span><?php echo $field->title; ?><?php echo (empty($field->unit)) ? '' : ', ' . $field->unit; ?>:</span>
            <div class="range">
                <div class="range-inputs">
                    <div class="input">
                        <input class="form-control range-min"
                               id="ishop_fields_<?php echo $field->id; ?>_from"
                               type="number"
                               min="<?php echo $min ?>"
                               max="<?php echo $max; ?>"
                               name="ishop_fields[<?php echo $field->id; ?>][min]"
                               placeholder="<?php echo $min; ?>"
                               value="<?php echo $tmp_min; ?>">
                        <label class="form-label input__hint" for="ishop_fields_<?php echo $field->id; ?>_from"><?php echo Text::_('MOD_ISHOP_FILTER_BY_PRICE_FROM'); ?></label>
                    </div>
                    <div class="input">
                        <input class="form-control range-max"
                               id="ishop_fields_<?php echo $field->id; ?>_to"
                               type="number"
                               min="<?php echo $min; ?>"
                               max="<?php echo $max; ?>"
                               name="ishop_fields[<?php echo $field->id; ?>][max]"
                               placeholder="<?php echo $max; ?>"
                               value="<?php echo $tmp_max; ?>">
                        <label class="form-label input__hint" for="ishop_fields_<?php echo $field->id; ?>_to"><?php echo Text::_('MOD_ISHOP_FILTER_BY_PRICE_TO'); ?></label>
                    </div>
                </div>
                <div class="range-slider">
                    <div class="range-slider__line"></div>
                    <div class="range-slider__point range-slider__point--upper"></div>
                    <div class="range-slider__point range-slider__point--lower"></div>
                </div>
            </div>
        </li>
	<?php elseif ($field->type === 2) : // Да или Нет?>
		<?php
		$checked = '';
		if (isset($filter->active['fields'][$field->id]) && (int) $filter->active['fields'][$field->id] === 1) {
			$checked = 'checked';
		}
		?>
        <li>
            <div class="form-switcher <?php echo $checked; ?>">
                <label class="form-switcher__label" for="ishop_fields_<?php echo $field->id; ?>_bool"><?php echo $field->title; ?></label>
                <div class="form-switcher__slider">
                    <input id="ishop_fields_<?php echo $field->id; ?>_bool"
                           type="checkbox"
                           name="ishop_fields[<?php echo $field->id; ?>]"
                           value="1" <?php echo $checked; ?>>
                </div>
            </div>
        </li>
	<?php else : // Строковые из списка?>
		<?php $checked_count = 0; ?>
		<?php $values = array_combine(explode('||', $field->values_id), explode('||', $field->values)); ?>
		<?php
		if (count($values) <= 1) {
			continue;
		};
		?>
        <li class="parent">
            <span><?php echo $field->title; ?></span>
            <input type="hidden" name="ishop_fields[<?php echo $field->id; ?>][]" value="0">
            <ul class="inputs">
				<?php foreach($values as $value_id => $value) : ?>
                    <li>
                        <p class="form-check">
							<?php
							$checked = '';
							if (isset($filter->active['fields'][$field->id]) && in_array($value_id, $filter->active['fields'][$field->id])) {
								$checked = 'checked';
								$checked_count++;
							}
							?>
                            <input class="form-check-input"
                                   id="value-<?php echo $field->id . '-' . $value_id; ?>"
                                   type="checkbox"
                                   name="ishop_fields[<?php echo $field->id; ?>][]"
                                   value="<?php echo $value_id; ?>" <?php echo $checked; ?>>
                            <label class="form-check-label"
                                   for="value-<?php echo $field->id . '-' . $value_id; ?>">
								<?php echo $value, ' ', $field->unit; ?>
                            </label>
                        </p>
                    </li>
				<?php endforeach; ?>
            </ul>
        </li>
	<?php endif; ?>
<?php endforeach; ?>