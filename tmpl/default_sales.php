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

$checked = '';
if (isset($filter->active['good_price']) && (int) $filter->active['good_price'] === 1) {
	$checked = 'checked';
}
?>
<li>
    <input type="hidden" name="good_price" value="0">
    <div class="form-switcher <?php echo $checked; ?>">
        <label class="form-switcher__label" for="ishop_good_price_bool"><?php echo Text::_('MOD_ISHOP_FILTER_BY_SALES'); ?></label>
        <div class="form-switcher__slider">
            <input id="ishop_good_price_bool"
                   type="checkbox"
                   name="good_price"
                   value="1" <?php echo $checked; ?>>
        </div>
    </div>
</li>