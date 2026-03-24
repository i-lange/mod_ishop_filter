<?php
/**
 * @package    mod_ishop_filter
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/mod_ishop_filter
 * @copyright  (C) 2026 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

use Joomla\CMS\Factory;
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

$access_levels = Factory::getApplication()->getIdentity()->getAuthorisedViewLevels();
$warehouses_access = $params->get('warehouses_access', 0);
?>
<?php if ($warehouses_access > 0 && in_array($warehouses_access, $access_levels)) : ?>
    <li class="parent">
        <span><?php echo Text::_('MOD_ISHOP_FILTER_BY_WAREHOUSES'); ?></span>
        <input type="hidden" name="warehouses[]" value="0">
        <ul class="list-unstyled">
            <?php foreach($filter->warehouses as $warehouse) : ?>
                <li>
                    <div class="form-check">
                        <?php
                        $checked = '';
                        if (in_array($warehouse->id, $filter->active['warehouses'])) {
                            $checked = 'checked';
                        }
                        ?>
                        <input class="form-check-input"
                                id="warehouse-<?php echo $warehouse->id; ?>"
                                type="checkbox"
                                name="warehouses[]"
                                value="<?php echo $warehouse->id; ?>" <?php echo $checked; ?>>
                        <label class="form-check-label"
                                for="warehouse-<?php echo $warehouse->id; ?>"><?php echo $warehouse->title; ?></label>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </li>
<?php endif; ?>