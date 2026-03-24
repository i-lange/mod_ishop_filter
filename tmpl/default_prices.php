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
     <div class="row g-3">
         <div class="col-md-6">
             <div class="mb-3">
                 <label for="min_price" class="form-label"><?php echo Text::_('MOD_ISHOP_FILTER_BY_PRICE_FROM'); ?></label>
                 <input type="number"
                        class="form-control"
                        id="min_price"
                        min="<?php echo (int) $filter->main->min_price; ?>"
                        max="<?php echo (int) $filter->main->max_price; ?>"
                        name="min_price"
                        placeholder="<?php echo (int) $filter->main->min_price; ?>"
                        value="<?php echo ($filter->active['min_price'] > 0) ? (int) $filter->active['min_price'] : ''; ?>">
             </div>
         </div>
         <div class="col-md-6">
             <div class="mb-3">
                 <label for="max_price" class="form-label"><?php echo Text::_('MOD_ISHOP_FILTER_BY_PRICE_TO'); ?></label>
                 <input type="number"
                        class="form-control"
                        id="max_price"
                        min="<?php echo (int) $filter->main->min_price; ?>"
                        max="<?php echo (int) $filter->main->max_price ?>"
                        name="max_price"
                        placeholder="<?php echo (int) $filter->main->max_price; ?>"
                        value="<?php echo ($filter->active['max_price'] > 0) ? (int) $filter->active['max_price'] : ''; ?>">
             </div>
         </div>
     </div>
 </li>