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
         <div class="row g-3">
             <div class="col-md-6">
                 <div class="mb-3">
                     <label for="min_width" class="form-label"><?php echo Text::_('MOD_ISHOP_FILTER_BY_PRICE_FROM'); ?></label>
                     <input type="number"
                            class="form-control"
                            id="min_width"
                            min="<?php echo (int) $filter->main->min_width; ?>"
                            max="<?php echo (int) $filter->main->max_width; ?>"
                            name="min_width"
                            placeholder="<?php echo (int) $filter->main->min_width; ?>"
                            value="<?php echo ($filter->active['min_width'] > 0) ? (int) $filter->active['min_width'] : ''; ?>">
                 </div>
             </div>
             <div class="col-md-6">
                 <div class="mb-3">
                     <label for="max_width" class="form-label"><?php echo Text::_('MOD_ISHOP_FILTER_BY_PRICE_TO'); ?></label>
                     <input type="number"
                            class="form-control"
                            id="max_width"
                            min="<?php echo (int) $filter->main->min_width; ?>"
                            max="<?php echo (int) $filter->main->max_width ?>"
                            name="max_width"
                            placeholder="<?php echo (int) $filter->main->max_width; ?>"
                            value="<?php echo ($filter->active['max_width'] > 0) ? (int) $filter->active['max_width'] : ''; ?>">
                 </div>
             </div>
         </div>
     </li>
 <?php endif; ?>
 <?php if ($params->get('show_height', 0) && ($filter->main->min_height > 0 || $filter->main->max_height > 0)) : ?>
     <li>
         <span><?php echo Text::_('MOD_ISHOP_FILTER_BY_HEIGHT'); ?></span>
         <div class="row g-3">
             <div class="col-md-6">
                 <div class="mb-3">
                     <label for="min_height" class="form-label"><?php echo Text::_('MOD_ISHOP_FILTER_BY_PRICE_FROM'); ?></label>
                     <input type="number"
                            class="form-control"
                            id="min_height"
                            min="<?php echo (int) $filter->main->min_height; ?>"
                            max="<?php echo (int) $filter->main->max_height; ?>"
                            name="min_height"
                            placeholder="<?php echo (int) $filter->main->min_height; ?>"
                            value="<?php echo ($filter->active['min_height'] > 0) ? (int) $filter->active['min_height'] : ''; ?>">
                 </div>
             </div>
             <div class="col-md-6">
                 <div class="mb-3">
                     <label for="max_height" class="form-label"><?php echo Text::_('MOD_ISHOP_FILTER_BY_PRICE_TO'); ?></label>
                     <input type="number"
                            class="form-control"
                            id="max_height"
                            min="<?php echo (int) $filter->main->min_height; ?>"
                            max="<?php echo (int) $filter->main->max_height ?>"
                            name="max_height"
                            placeholder="<?php echo (int) $filter->main->max_height; ?>"
                            value="<?php echo ($filter->active['max_height'] > 0) ? (int) $filter->active['max_height'] : ''; ?>">
                 </div>
             </div>
         </div>
     </li>
 <?php endif; ?>
 <?php if ($params->get('show_depth', 0) && ($filter->main->min_depth > 0 || $filter->main->max_depth > 0)) : ?>
     <li>
         <span><?php echo Text::_('MOD_ISHOP_FILTER_BY_DEPTH'); ?></span>
         <div class="row g-3">
             <div class="col-md-6">
                 <div class="mb-3">
                     <label for="min_depth" class="form-label"><?php echo Text::_('MOD_ISHOP_FILTER_BY_PRICE_FROM'); ?></label>
                     <input type="number"
                            class="form-control"
                            id="min_depth"
                            min="<?php echo (int) $filter->main->min_depth; ?>"
                            max="<?php echo (int) $filter->main->max_depth; ?>"
                            name="min_depth"
                            placeholder="<?php echo (int) $filter->main->min_depth; ?>"
                            value="<?php echo ($filter->active['min_depth'] > 0) ? (int) $filter->active['min_depth'] : ''; ?>">
                 </div>
             </div>
             <div class="col-md-6">
                 <div class="mb-3">
                     <label for="max_depth" class="form-label"><?php echo Text::_('MOD_ISHOP_FILTER_BY_PRICE_TO'); ?></label>
                     <input type="number"
                            class="form-control"
                            id="max_depth"
                            min="<?php echo (int) $filter->main->min_depth; ?>"
                            max="<?php echo (int) $filter->main->max_depth ?>"
                            name="max_depth"
                            placeholder="<?php echo (int) $filter->main->max_depth; ?>"
                            value="<?php echo ($filter->active['max_depth'] > 0) ? (int) $filter->active['max_depth'] : ''; ?>">
                 </div>
             </div>
         </div>
     </li>
 <?php endif; ?>
 <?php if ($params->get('show_weight', 0) && ($filter->main->min_weight > 0 || $filter->main->max_weight > 0)) : ?>
     <li>
         <span><?php echo Text::_('MOD_ISHOP_FILTER_BY_WEIGHT'); ?></span>
         <div class="row g-3">
             <div class="col-md-6">
                 <div class="mb-3">
                     <label for="min_weight" class="form-label"><?php echo Text::_('MOD_ISHOP_FILTER_BY_PRICE_FROM'); ?></label>
                     <input type="number"
                            class="form-control"
                            id="min_weight"
                            min="<?php echo (int) $filter->main->min_weight; ?>"
                            max="<?php echo (int) $filter->main->max_weight; ?>"
                            name="min_weight"
                            placeholder="<?php echo (int) $filter->main->min_depth; ?>"
                            value="<?php echo ($filter->active['min_weight'] > 0) ? (int) $filter->active['min_weight'] : ''; ?>">
                 </div>
             </div>
             <div class="col-md-6">
                 <div class="mb-3">
                     <label for="max_weight" class="form-label"><?php echo Text::_('MOD_ISHOP_FILTER_BY_PRICE_TO'); ?></label>
                     <input type="number"
                            class="form-control"
                            id="max_weight"
                            min="<?php echo (int) $filter->main->min_weight; ?>"
                            max="<?php echo (int) $filter->main->max_weight ?>"
                            name="max_weight"
                            placeholder="<?php echo (int) $filter->main->max_weight; ?>"
                            value="<?php echo ($filter->active['max_weight'] > 0) ? (int) $filter->active['max_weight'] : ''; ?>">
                 </div>
             </div>
         </div>
     </li>
 <?php endif; ?>
