<?php
/**
 * @package    mod_ishop_filter
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/mod_ishop_filter
 * @copyright  (C) 2026 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

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
<li class="parent">
     <span><?php echo Text::_('MOD_ISHOP_FILTER_BY_BRAND'); ?></span>
     <input type="hidden" name="manufacturers[]" value="0">
     <ul class="list-unstyled">
  		<?php foreach($filter->manufacturers as $brand) : ?>
        		<li>
        			<div class="form-check">
 						<?php
 						$checked = '';
 						if (in_array($brand['id'], $filter->active['manufacturers'])) {
 							$checked = 'checked';
 						}
 						?>
                         <input class="form-check-input"
                                id="brand-<?php echo $brand['id']; ?>"
                                type="checkbox"
                                name="manufacturers[]"
                                value="<?php echo $brand['id']; ?>" <?php echo $checked; ?>>
                         <label class="form-check-label"
                                for="brand-<?php echo $brand['id']; ?>"><?php echo $brand['title']; ?></label>
        			</div>
        		</li>
  		<?php endforeach; ?>
     </ul>
 </li>