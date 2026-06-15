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
 * @var array $subPanels
 */
?>
<?php foreach ($filter->ishop_fields as $field) : ?>
    <?php if ((int) $field->type === 0 || (int) $field->type === 2) : ?>
        <?php continue; ?>
    <?php endif; ?>
    <?php
    $valueIds = explode('||', (string) $field->values_id);
    $valueTitles = explode('||', (string) $field->values);
    $values = count($valueIds) === count($valueTitles) ? array_combine($valueIds, $valueTitles) : [];

    // Не нужно выводить, если выбор из одного варианта
    if (count($values) <= 1) {
        continue;
    }

    $selectedValueIds = array_map('intval', (array) ($filter->active['fields'][$field->id] ?? []));
    $selectedCount = count($selectedValueIds);

    // Добавляем панель с выбором значений характеристики
    $subPanels[$field->id]['title'] = (string) $field->title;
    $subPanels[$field->id]['alias'] = (string) (($field->alias ?? '') ?: 'field-' . $field->id);
    $subPanels[$field->id]['values'] = $values;
    ?>
    <span class="nav-link separator"
          data-panel-target="off-panel-<?php echo htmlspecialchars($subPanels[$field->id]['alias'], ENT_COMPAT, 'UTF-8'); ?>">
        <span class="filter-panel-title"><?php echo htmlspecialchars((string) $field->title, ENT_COMPAT, 'UTF-8'); ?></span>
        <span class="badge rounded-pill text-bg-primary filter-selected-count<?php echo $selectedCount > 0 ? '' : ' is-empty'; ?>"
              data-field-id="<?php echo (int) $field->id; ?>"
              data-selected-count
              data-count="<?php echo $selectedCount; ?>"
              <?php echo $selectedCount > 0 ? '' : 'hidden'; ?>><?php echo $selectedCount; ?></span>
    </span>
<?php endforeach; ?>
