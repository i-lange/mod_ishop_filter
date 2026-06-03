<?php
/**
 * @package    mod_ishop_filter
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/mod_ishop_filter
 * @copyright  (C) 2026 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

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
    <?php if ((int) $field->type !== 2) : ?>
        <?php continue; ?>
    <?php endif; ?>
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
<?php endforeach; ?>
